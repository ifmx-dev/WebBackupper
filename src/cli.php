<?php

// set working directory for cli
use ifmx\WebBackupper;
use ifmx\WebBackupper\classes;

chdir(__DIR__);

$config = [];

require 'Backupper.php';
require 'config/config.php';

// initialize backupper
$backupper = new WebBackupper\Backupper($config);

// declare instances from config
$instances = [];
$instances['wordpress'] = classes\General::getConfig('wordpress');
$instances['webapps'] = classes\General::getConfig('webapps');
$instances['databases'] = classes\General::getConfig('databases');
$instances['directories'] = classes\General::getConfig('directories');

$ftpConfig = [];
if (classes\General::getConfig('ftp, enabled')) {
    $ftpConfig['isSftp'] = classes\General::getConfig('ftp, isSftp');
    $ftpConfig['host'] = classes\General::getConfig('ftp, host');
    $ftpConfig['username'] = classes\General::getConfig('ftp, username');
    $ftpConfig['password'] = classes\General::getConfig('ftp, password');
    $ftpConfig['port'] = classes\General::getConfig('ftp, port');
    $ftpConfig['path'] = classes\General::getConfig('ftp, path');
}

// create backups
$backupper->createBackup($instances, $ftpConfig);

// send email to webmaster
if (classes\General::getConfig('system, sendLogEmail')) {

    // read email address of webmaster
    $toEmailAddress = classes\General::getConfig('system, webmasterEmailAddress');

    // read log
    $log = classes\Logger::getLogAsString();

    $backupper->sendLogMail($toEmailAddress, $log);
}
