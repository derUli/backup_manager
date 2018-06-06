<?php

class BackupAdminController extends Controller
{

    public function create()
    {
        header("Content-type: text/html; charset=utf-8");
        translate("create_backup");
        echo "...";
        echo "<br/>";
        translate("please_wait_dot_dot_dot");
        echo "<br/>";
        fcflush();
        flush();
        
        $backupHooksController = ControllerRegistry::get("BackupHooksController");
        
        $targetDir = $backupHooksController->getBackupDir() . "/" . time() . ".backup";
        
        $backup = new Backup($targetDir);
        $backup->backup(boolval(Request::getVar("maintenance_mode", 0, "bool")));
        translate("Done.");
        echo "<br/>";
        fcflush();
        flush();
        Response::javascriptRedirect(ModuleHelper::buildActionURL("backup_list"));
        return "";
    }

    // Action to check the password with ajax before submitting the form
    public function checkPassword()
    {
        $password = Request::getVar("password", null, "str");
        $user = new User();
        $user->loadById($_SESSION["login_id"]);
        if (Encryption::hashPassword($password) != $user->getPassword()) {
            HTTPStatusCodeResult(403, get_translation("wrong_password"));
        }
        HTTPStatusCodeResult(200, get_translation("ok"));
    }

    public function restore()
    {
        $name = Request::getVar("name", null, "int");
        if (! $name) {
            TextResult("Not found", 404);
        }
        $backupHooksController = ControllerRegistry::get("BackupHooksController");
        
        $targetDir = $backupHooksController->getBackupDir() . "/" . $name . ".backup";
        
        if (! is_dir($targetDir)) {
            TextResult("Not found", 404);
        }
        
        $password = Request::getVar("password", null, "str");
        $user = new User();
        $user->loadById($_SESSION["login_id"]);
        if (Encryption::hashPassword($password) != $user->getPassword()) {
            $backLink = \UliCMS\HTML\Link::Link("#", "[" . get_translation("try_again") . "]", array(
                "onclick" => "history.back()",
                "class" => "btn btn-default btn-back"
            ));
            ExceptionResult(get_translation("wrong_password") . " $backLink", 403);
        }
        
        header("Content-type: text/html; charset=utf-8");
        translate("restore_backup_x", array(
            "%dir%" => basename($targetDir)
        ));
        echo "...";
        echo "<br/>";
        translate("please_wait_dot_dot_dot");
        echo "<br/>";
        fcflush();
        flush();
        
        $backup = new Backup($targetDir);
        $backup->restore(boolval(Request::getVar("maintenance_mode", 0, "bool")));
        translate("Done.");
        echo "<br/>";
        fcflush();
        flush();
        Response::javascriptRedirect(ModuleHelper::buildActionURL("backup_list"));
        return "";
    }

    public function download()
    {
        $name = Request::getVar("name", 0, "int");
        $backupHooksController = ModuleHelper::getMainController("backup_manager");
        $baseFolder = $backupHooksController->getBackupDir();
        $folder = $baseFolder . "/{$name}.backup";
        if ($name and is_dir($folder)) {
            $tmpFile = Path::resolve("ULICMS_TMP/" . uniqid() . ".zip");
            $outputFile = get_http_host() . "-{$name}.backup.zip";
            $zip = new ZipArchive();
            $zip->open($tmpFile, ZipArchive::CREATE);
            $files = glob($folder . "/*");
            foreach ($files as $file) {
                if (is_file($file)) {
                    $localName = substr($file, strlen($folder) + 1);
                    $zip->addFile($file, $localName);
                }
            }
            
            $zip->close();
            
            $size = filesize($tmpFile); // The way to avoid corrupted ZIP
            
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename=' . $outputFile);
            header('Content-Length: ' . $size);
            readfile($tmpFile);
            @unlink($tmpFile);
            exit();
        }
        
        HtmlResult("Not Found", HttpStatusCode::NOT_FOUND);
    }

    public function delete()
    {
        $name = Request::getVar("name", 0, "int");
        $backupHooksController = ModuleHelper::getMainController("backup_manager");
        $baseFolder = $backupHooksController->getBackupDir();
        $folder = $baseFolder . "/{$name}.backup";
        if ($name and is_dir($folder)) {
            SureRemoveDir($folder, true);
        }
        Response::redirect(ModuleHelper::buildActionURL("backup_list"));
    }
}