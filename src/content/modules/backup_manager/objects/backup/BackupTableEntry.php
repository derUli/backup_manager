<?php
use UliCMS\Exceptions\FileNotFoundException;

class BackupTableEntry
{

    public $date;

    public $size;

    public $type;

    public $name;

    public function __construct($folder = null)
    {
        if ($folder) {
            $this->load($folder);
        }
    }

    public function load($folder)
    {
        $metadataFile = $folder . "/backup.json";
        if (file_exists($metadataFile)) {
            $json = file_get_contents($metadataFile);
            $data = json_decode($json, true);
            $this->type = $data["type"];
            $this->date = $data["time"];
            $databaseBackup = $folder . "/database.sql.gz";
            $this->size = file_exists($databaseBackup) ? filesize($databaseBackup) : 0;
        } else {
            throw new FileNotFoundException("File $metadataFile not found");
        }
        $this->name = explode(".", basename($folder));
        $this->name = $this->name[0];
    }

    public static function getAll($baseFolder)
    {
        $result = array();
        $folders = glob("$baseFolder/*.backup", GLOB_ONLYDIR);
        if ($folders) {
            foreach ($folders as $folder) {
                if (file_exists($folder . "/backup.json")) {
                    $result[] = new BackupTableEntry($folder);
                }
            }
        }
        return $result;
    }
}