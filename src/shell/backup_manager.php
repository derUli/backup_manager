#!/usr/bin/php -q
<?php

function usage()
{
    echo "backup_manager - Backup Manager\n";
    echo "Version " . getModuleMeta("backup_manager", "version") . "\n";
    echo "Copyright (C) 2018 by Ulrich Schmidt";
    echo "\n\n";
    echo "Usage php -f backup_manager.php [backup|restore] [Backup To Restore] --maintenance-mode=on\n\n";
    exit();
}

if (php_sapi_name() != "cli") {
    die("This script can be run from command line only.");
}

$parent_path = dirname(__file__) . "/../";
include $parent_path . "init.php";

array_shift($argv);

// No time limit
@set_time_limit(0);

if (count($argv) < 1) {
    usage();
} else {
    $command = $argv[0];
    if ($command == "backup") {
        $maintenanceMode = in_array("--maintenance-mode=on", $argv);
        $backupHooksController = ModuleHelper::getMainController("backup_manager");
        
        $targetDir = $backupHooksController->getBackupDir() . "/" . time() . ".backup";
        $backup = new Backup($targetDir);
        
        echo "Starting backup...\n";
        echo "Current Time is " . date("c") . "\n";
        echo "Maintenance Mode is ";
        echo $maintenanceMode ? "on" : "off";
        echo "\n";
        $backup->backup($maintenanceMode);
        echo "Backup finished\n";
        echo "Finished at " . date("c");
    } else if ($command == "restore" and count($argv) >= 2) {
        $sourceDir = $argv[1];
        $maintenanceMode = in_array("--maintenance-mode=on", $argv);
        $backup = new Backup($sourceDir);
        echo "Starting restore...\n";
        echo "Current Time is " . date("c") . "\n";
        echo "Maintenance Mode is ";
        echo $maintenanceMode ? "on" : "off";
        echo "\n";
        $backup->restore($maintenanceMode);
        echo "Backup finished\n";
        echo "Finished at " . date("c");
    } else {
        usage();
    }
    echo "\n";
}