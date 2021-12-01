<?php

namespace ifmx\WebBackupper\classes;

class WebappBackupper {

    // -------------------------------------------------------------------
    // Public Functions
    // -------------------------------------------------------------------

    /**
     * create databases and folder backups foreach webapp in config
     *
     * @param array $webapps
     * @param array $ftpConfig
     * @return bool
     * @throws \Exception
     */
    public static function createBackup(array $instances = [], array $ftpConfig = []): bool {

        // loop webapps in config
        foreach ($instances as $instanceName => $webapp) {

            // define backup and temp folder name for instance
            $backupDir = General::getBackupDir($instanceName);
            $tempDir = General::getTempDir($instanceName);

            $dbHost = $webapp['db']['host'];
            $dbPort = $webapp['db']['port'];
            $dbName = $webapp['db']['name'];
            $dbUsername = $webapp['db']['username'];
            $dbPassword = $webapp['db']['password'];

            // create database dump
            DbBackupper::createDbBackup($instanceName, $tempDir, $dbHost, $dbPort, $dbName, $dbUsername, $dbPassword);

            // backup folders
            if ($webapp['subDirectories']) {
                $backupFolders = [];
                foreach ($webapp['subDirectories'] as $subDirectory) {
                    $backupFolders[] = $webapp['directory'] . DIRECTORY_SEPARATOR . $subDirectory;
                }
            } else {
                $backupFolders = $webapp['directories'];
            }

            // create folder backup
            $fileName = FolderBackupper::createFileBackup($instanceName, $tempDir, $backupDir, $backupFolders);

            // on success
            if ($fileName) {

                // set log msg
                Logger::info('webapp "' . $instanceName . '" backuped successfully');

                // upload file to ftp server
                if ($ftpConfig) {
                    $uploaded = FTP::upload($instanceName, $backupDir, $fileName, $ftpConfig['isSftp'], $ftpConfig['host'], $ftpConfig['username'], $ftpConfig['password'], $ftpConfig['path'], $ftpConfig['port']);

                    if ($uploaded) {
                        Logger::info('webapp backup "' . $instanceName . '" uploaded to FTP successfully');
                    } else {
                        Logger::warning('webapp backup "' . $instanceName . '" uploaded to FTP failed');
                    }
                }
            } else {

                // set log msg
                Logger::error('webapp "' . $instanceName . '" backup failed');
            }
        }

        return true;
    }
}
