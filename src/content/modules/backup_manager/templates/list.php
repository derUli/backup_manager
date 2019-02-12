<?php
$backupHooksController = ModuleHelper::getMainController("backup_manager");
$baseFolder = $backupHooksController->getBackupDir();
$data = BackupTableEntry::getAll($baseFolder);

$acl = new ACL();
$allowed = func_enabled("shell_exec");
?>

<h1><?php translate("backups");?></h1>
<?php if($allowed["s"] == 0){
?>
<div class="alert alert-warning">
	<?php echo $allowed ["m"];?>
</div>
<?php }?>
<?php if($acl->hasPermission("backup_create")){?>
<p>
	<a href="<?php echo ModuleHelper::buildActionURL("backup_new");?>"
		class="btn btn-warning"><i class="fa fa-plus"></i> <?php translate("create");?></a>
</p>
<?php }?>
<div class="scroll">
<table class="tablesorter">
	<thead>
		<tr>
			<th><?php translate("date");?></th>
			<th><?php translate("type");?></th>
			<th><?php translate("size");?></th>
			<th><?php translate("actions");?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($data as $backup){?>
	<tr>
			<td><?php echo date("c",$backup->date);?></td>		
			<td><?php echo get_translation("backup_type_{$backup->type}");?></td>			
			<td class="text-right"><?php echo round($backup->size / 1000 / 1000, 2) ." MB";?></td>
			<td class="text-left">
				<div class="btn-group" >
				<?php if($acl->hasPermission("backup_download")){?>
						<a href="<?php echo ModuleHelper::buildMethodCallUrl("BackupAdminController", "download", "name={$backup->name}")?>" 
						class="btn btn-danger btn-margin"><i class="fa fa-download" aria-hidden="true"></i> <?php translate("download")?></a>
					<?php }?><?php if($acl->hasPermission("backup_restore")){?>
			
						<a
							href="<?php echo ModuleHelper::buildActionURL("backup_restore", "name={$backup->name}");?>"
							class="btn btn-danger btn-margin"><i class="fas fa-undo"></i> <?php translate("restore")?></a>
					
				<?php }?>
<?php
    if ($acl->hasPermission("backup_delete")) {
        ?>
        <?php
        echo ModuleHelper::buildMethodCallForm("BackupAdminController", "delete", array(
            "name" => $backup->name
        ), "post", array(
            "class" => "delete-form pull-left btn-margin"
        ));
        ?>
        <button type="submit" class="btn btn-danger"><i class="fa fa-trash" aria-hidden="true"></i> <?php translate("delete");?></button>
        <?php
        
        echo ModuleHelper::endForm();
    }
    ?>
				</div>
			</td>
		</tr>
	<?php }?>
	</tbody>
</table>
</div>
<?php
$translation = new JSTranslation();
$translation->addKey("ask_for_delete");
$translation->render();
?>
