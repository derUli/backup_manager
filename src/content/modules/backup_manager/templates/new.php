
<p>
	<a href="<?php echo ModuleHelper::buildActionURL("backup_list");?>"
		class="btn btn-default btn-back"><?php translate("back")?></a>
</p>
<h1><?php translate("create_backup");?></h1>
<?php echo ModuleHelper::buildMethodCallForm("BackupAdminController", "create");?>
<strong><?php translate("type");?></strong>
<div class="radio">
	<label><input type="radio"
		id="backup_type_<?php echo BackupType::Database;?>" name="type"
		value="<?php echo BackupType::Database;?>" checked>
		<?php echo get_translation("backup_type_database");?></label>
</div>
<div class="checkbox">
	<label><input type="checkbox" name="maintenance_mode" value="1">
<?php translate("put_site_into_maintenance_mode");?></label>
</div>
<button type="submit" class="btn btn-warning"><?php translate("create_backup");?></button>
<?php echo ModuleHelper::endForm();?>