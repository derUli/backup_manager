# backup_manager

Backup Manager for UliCMS is a backup solution for UliCMS and the successor to mysql_backup.

Backup Manager provides backup features within a GUI, a command line interface and as a pseudo cronjob 

# Requirements

* UliCMS 2018.3.1 or later
* better_cron Module installed (https://extend.ulicms.de/better_cron.html)
* The function shell_exec() must not be disabled
* mysqldump and gzip must be included in $PATH

# Installation

...