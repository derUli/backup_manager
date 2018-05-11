<?php

class BackupHooksController extends MainClass
{

    protected function doBackup()
    {
        $cfg = new CMSConfig();
        $dir = $this->getBackupDir() . "/" . time() . ".backup";
        $backup = new Backup($dir);
        $backup->backup(is_true($cfg->backup_maintenance_mode));
    }

    public function cron()
    {
        $jobs = $this->getBackupJobs();
        foreach ($jobs as $job) {
            $cronjobName = "backup/" . implode("", $job);
            switch ($job[1]) {
                case "hour":
                case "hours":
                    BetterCron::hours($cronjobName, $job[0], function () {
                        $this->doBackup();
                    });
                    break;
                    break;
                case "day":
                case "days":
                    BetterCron::days($cronjobName, $job[0], function () {
                        $this->doBackup();
                    });
                    break;
                    break;
                case "week":
                case "weeks":
                    BetterCron::days($cronjobName, $job[0] * 7, function () {
                        $this->doBackup();
                    });
                    break;
                    break;
                case "month":
                case "months":
                    BetterCron::months($cronjobName, $job[0], function () {
                        $this->doBackup();
                    });
                    break;
                    break;
                case "year":
                case "years":
                    BetterCron::years($cronjobName, $job[0], function () {
                        $this->doBackup();
                    });
                    break;
                    break;
            }
        }
    }

    protected function getBackupJobs()
    {
        $cfg = new CMSConfig();
        $jobsResult = array();
        if (isset($cfg->backup_jobs) and is_array($cfg->backup_jobs)) {
            $jobs = $cfg->backup_jobs;
            foreach ($jobs as $job) {
                $jobArr = explode(" ", $job);
                $jobArr[0] = intval($jobArr[0]);
                $jobsResult[] = $jobArr;
            }
        }
        return $jobsResult;
    }

    public function getBackupDir()
    {
        $cfg = new CMSConfig();
        $dir = Path::resolve("ULICMS_DATA_STORAGE_ROOT/content/backups");
        ;
        if (isset($cfg->backup_dir) and is_string($cfg->backup_dir)) {
            $dir = Path::resolve($cfg->backup_dir);
        }
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        if (! file_exists($dir . "/.htaccess")) {
            copy(Path::resolve("ULICMS_ROOT/lib/htaccess-deny-all.txt"), $dir . "/.htaccess");
        }
        return $dir;
    }
    
    public function getSettingsHeadline(){
        return get_translation("backups");
    }
    public function getSettingsLinkText(){
        return get_translation("open");
    }

    public function settings()
    {
        Response::javascriptRedirect(ModuleHelper::buildActionURL("backup_list"));
    }

    public function uninstall()
    {
        $removeFiles = array(
            "ULICMS_DATA_STORAGE_ROOT/shell/backup_manager.php"
        );
        foreach ($removeFiles as $file) {
            $path = Path::resolve($file);
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }
}