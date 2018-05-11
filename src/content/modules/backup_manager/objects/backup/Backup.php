<?php

class Backup
{

    private $targetDir;

    public function __construct($targetDir)
    {
        $this->targetDir = $targetDir;
    }

    protected function saveMetadataJSON($type = BackupType::Database)
    {
        $data = array();
        $data["type"] = $type;
        $data["time"] = time();
        $json = json_encode($data);
        file_put_contents($this->targetDir . "/" . "backup.json", $json);
    }

    public function backup($maintenanceMode = false)
    {
        // set last backup time to current
        @ignore_user_abort(1); // run script in background
        @set_time_limit(0); // run script forever
        
        if ($maintenanceMode) {
            if (isCli()) {
                echo "Put system into maintenance mode\n";
            }
            Settings::set("maintenance_mode", "on");
        }
        if (! is_dir($this->targetDir)) {
            // TODO: Fehlerbehandlung
            if (isCli()) {
                echo "Create folder {$this->targetDir}\n";
            }
            mkdir($this->targetDir, 0777, true);
        }
        
        if (isCli()) {
            echo "Create backup.json meta data file\n";
        }
        $this->saveMetadataJSON();
        $this->backupDatabase();
        if ($maintenanceMode) {
            if (isCli()) {
                echo "Disable maintenance mode\n";
            }
            Settings::delete("maintenance_mode");
        }
    }

    protected function backupDatabase()
    {
        $config = new CMSConfig();
        
        $mysql_user = $config->db_user;
        $mysql_password = $config->db_password;
        $mysql_database = $config->db_database;
        $mysql_host = $config->db_server;
        $backup_file = $this->targetDir . "/database.sql";
        $zipFile = $this->targetDir . "/database.sql.gz";
        
        if (file_exists($backup_file)) {
            unlink($backup_file);
        }
        if (file_exists($zipFile)) {
            unlink($zipFile);
        }
        
        $allowed = func_enabled("shell_exec");
        $allowed = $allowed["s"] === 1 && ! ini_get('safe_mode');
        
        $tmpfile = $this->targetDir . "/" . uniqid();
        $writable = @file_put_contents($tmpfile, "test") !== false;
        
        if ($writable) {
            @unlink($tmpfile);
        }
        
        if ($allowed and $writable) {
            // Save Dump
            
            if (isCli()) {
                echo "Create mysqldump\n";
            }
            shell_exec("mysqldump -h $mysql_host -u $mysql_user -p$mysql_password --skip-lock-tables --add-drop-table --complete-insert --hex-blob $mysql_database > $backup_file");
            
            if (isCli()) {
                echo "Zipping mysqldump\n";
            }
            shell_exec("gzip " . $backup_file);
        } else {
            if (isCli()) {
                echo "Fatal Error: folder {$this->targetDir} is not writable\n";
            }
        }
        // TODO: Fehlerbehandlung
    }

    public function restore($maintenanceMode)
    {
        // set last backup time to current
        @ignore_user_abort(1); // run script in background
        @set_time_limit(0); // run script forever
        
        if ($maintenanceMode) {
            if (isCli()) {
                echo "Put system into maintenance mode\n";
            }
            Settings::set("maintenance_mode", "on");
        }
        
        $zipFile = $this->targetDir . "/database.sql.gz";
        $backupFile = $this->targetDir . "/database.sql";
        
        if (isCli()) {
            echo "decompress sql dump\n";
        }
        
        $extractCommand = "gzip -d \"$zipFile\"";
        shell_exec($extractCommand);
        
        @ignore_user_abort(1); // run script in background
        @set_time_limit(0); // run script forever
        
        $cfg = new CMSConfig();
        
        if (isCli()) {
            echo "import mysqldump\n";
        }
        $importCommand = "mysql -u " . $cfg->db_user . " -p" . $cfg->db_password . " -h " . $cfg->db_server . " " . $cfg->db_database . " < " . '"' . $backupFile . '"';
        
        shell_exec($importCommand);
        
        if (isCli()) {
            echo "recompressing\n";
        }
        $compressCommand = "gzip \"" . $backupFile . "\"";
        shell_exec($compressCommand);
        if ($maintenanceMode) {
            if (isCli()) {
                echo "Disable maintenance mode\n";
            }
            Settings::delete("maintenance_mode");
        }
    }
}
