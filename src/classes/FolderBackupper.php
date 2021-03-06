<?php

namespace ifmx\WebBackupper\classes;

class FolderBackupper {

    // -------------------------------------------------------------------
    // Public Functions
    // -------------------------------------------------------------------


    /**
     * create folder backups foreach folder
     *
     * @param array $instances
     * @param array $ftpConfig
     * @return array
     * @throws \Exception
     */
    public static function createBackup(array $instances = [], array $ftpConfig = []): array {
        $files = [];

        // loop folders
        foreach ($instances as $instanceName => $folderConfig) {

            $subfolders = $folderConfig;
            if (!is_array($subfolders)) {
                $subfolders = [$subfolders];
            }

            // define backup and temp folder name for instance
            $backupDir = General::getBackupDir($instanceName);
            $tempDir = General::getTempDir($instanceName);

            // create zip from folder
            $fileName = self::createFileBackup($instanceName, $tempDir, $backupDir, $subfolders);

            // on success
            if ($fileName) {
                $files[$instanceName] = $fileName;

                // set log msg
                Logger::info('folder "' . $instanceName . '" backuped successfully');

                // upload file to ftp server
                if ($ftpConfig) {
                    $uploaded = FTP::upload($instanceName, $backupDir, $fileName, $ftpConfig);

                    if ($uploaded) {
                        Logger::info('folder Backup "' . $instanceName . '" uploaded to FTP server successfully');
                    } else {
                        Logger::warning('folder Backup "' . $instanceName . '" uploaded to FTP server failed');
                    }
                }
            } else {

                // set log msg
                Logger::error('folder "' . $instanceName . '" backup failed');
            }
        }

        return $files;
    }


    /**
     * creates a backup from a folder
     *
     * @param string $instanceName
     * @param string $tempDir
     * @param string $backupDir
     * @param        $folders
     * @return string
     * @throws \Exception
     */
    public static function createFileBackup(string $instanceName, string $tempDir, string $backupDir, $folders): string {

        // make array
        if (!is_array($folders)) {
            $folders = [$folders];
        }

        // loop folders
        foreach ($folders as $folder) {
            $fromFolder = realpath($folder);

            // copy folder to temp folder
            if (is_dir($fromFolder)) {
                $folderName = basename($fromFolder);
                $toFolder = $tempDir . DIRECTORY_SEPARATOR . $folderName;

                Logger::debug('start to copy folder "' . $fromFolder . '"');
                self::copyFolder($fromFolder, $toFolder);
                Logger::debug('finished copying folder "' . $fromFolder . '"');
            } else {
                Logger::error('folder "' . $folder . '" does not exist');
            }
        }

        // create zip from copied folder
        $fileName = self::zipFolder($tempDir, $backupDir, $instanceName);

        // delete temp folder
        Logger::debug('start to delete temp folder "' . $tempDir . '"');
        self::deleteFolder($tempDir);
        Logger::debug('finished deleting temp folder "' . $tempDir . '"');

        // return name from zip file
        return $fileName;
    }

    // -------------------------------------------------------------------
    // Protected Functions
    // -------------------------------------------------------------------

    /**
     * create a copy from a folder recursively
     *
     * @param string $fromFolder
     * @param string $toFolder
     * @throws \Exception
     */
    protected static function copyFolder(string $fromFolder, string $toFolder) {

        // check if folder exists
        if (!is_dir($fromFolder)) {
            Logger::error('Folder "' . $fromFolder . '" does not exist');
        }

        // create folder if not exists
        if (!is_dir($toFolder)) {
            if (!mkdir($toFolder, 0777, true)) {
                Logger::error('Folder "' . $toFolder . '" could not be created');
            }
        }

        // open from folder
        $dir = opendir($fromFolder);

        // loop trough files in source folder
        while (false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                $fromFile = $fromFolder . DIRECTORY_SEPARATOR . $file;
                $toFile = $toFolder . DIRECTORY_SEPARATOR . $file;

                // if file is a folder, call this function recursive
                if (is_dir($fromFile)) {
                    self::copyFolder($fromFile, $toFile);
                } else {

                    // copy file to new folder
                    copy($fromFile, $toFile);
                }
            }
        }

        // close folder
        closedir($dir);
    }


    /**
     * deletes a folder recursively
     *
     * @param string $path
     * @return bool
     */
    protected static function deleteFolder(string $path): bool {
        $return = true;

        // if path is a dir, delete everything inside
        if (is_dir($path)) {

            // open folder
            $dh = opendir($path);

            // loop through all files an folders
            while (($fileName = readdir($dh)) !== false) {
                if ($fileName !== '.' && $fileName !== '..') {
                    self::deleteFolder($path . DIRECTORY_SEPARATOR . $fileName);
                }
            }

            // close folder
            closedir($dh);

            // delete folder
            if (!rmdir($path)) {
                $return = false;
            }

            // delete file
        } else {
            if (!unlink($path)) {
                $return = false;
            }
        }

        return $return;
    }


    /**
     * create zip from folder
     *
     * @param string $sourceDir
     * @param string $destinationDir
     * @param string $instanceName
     * @return string
     */
    protected static function zipFolder(string $sourceDir, string $destinationDir, string $instanceName): string {
        $sourceDir = realpath($sourceDir);
        $destinationDir = realpath($destinationDir);
        $fileName = date('Y-m-d-H-i-s_') . $instanceName . '.zip';

        Logger::debug('start to zip folder "' . $sourceDir . '"');

        // initialize archive object
        $zip = new \ZipArchive();
        $zip->open($destinationDir . DIRECTORY_SEPARATOR . $fileName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        // create recursive directory iterator
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            // skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($sourceDir) + 1);

                // add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }

        // zip archive will be created only after closing object
        $zip->close();

        // get file size
        $fileSize = General::getFileSize($destinationDir . DIRECTORY_SEPARATOR . $fileName);

        Logger::debug('finished zipping folder "' . $sourceDir . '". file size: ' . $fileSize);

        return $fileName;
    }
}
