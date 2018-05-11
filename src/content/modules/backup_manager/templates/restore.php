<p>
	<a href="<?php echo ModuleHelper::buildActionURL("backup_list");?>"
		class="btn btn-default btn-back"><?php translate("back")?></a>
</p>
<?php
$name = Request::getVar("name", null, "int");
if ($name) {
    $backupHooksController = ModuleHelper::getMainController("backup_manager");
    $baseFolder = $backupHooksController->getBackupDir() . "/{$name}.backup";
    
    $backup = new BackupTableEntry($baseFolder);
    ?>
<p>
	<strong><?php translate("date");?></strong><br />
<?php echo date("c",$backup->date);?>
</p>
<p>
	<strong><?php translate("type");?></strong><br />
<?php echo get_translation("backup_type_{$backup->type}");?>
</p>
<p>
	<strong><?php translate("size");?></strong><br />
<?php echo round($backup->size / 1000 / 1000, 2) ." MB";?>
</p>
<?php echo ModuleHelper::buildMethodCallForm("BackupAdminController", "restore");?>
<input type="hidden" name="name" value="<?php esc($name);?>">
<div class="checkbox">
	<label><input type="checkbox" name="maintenance_mode" value="1">
<?php translate("put_site_into_maintenance_mode");?></label>
</div>
<button type="submit" class="btn btn-danger"><?php translate("restore");?></button>

<?php echo ModuleHelper::endForm();?>



<?php
} else {
    Response::javascriptRedirect(ModuleHelper::buildActionURL("backup_list"));
}
?>