<?php

// Debug mode
$debug_mode = true;

$config = [

    // ------------------------------------------
    // Wordpress Instances for backup
    // 'Name for Backupfile' => 'Path to Directory'
    // ------------------------------------------
    'wpDirectories' => [
        // WP-Directory 1
        'TestWordpress' => '/home/var/www/site1',
        // WP-Directory 2
        'TestWordpress' => '/home/var/www/site2'
    ],

    // ------------------------------------------
    // Folder and DB Backup for backup
    // 'Name for Backupfile' => 'Infos'
    // ------------------------------------------
    'webapps' => [
        // Webapp 1
        'TestDb' => [
            'directories' => [
                // Folder
                '/home/var/www/folder',
                '/home/var/www/folder1'
            ],
            'db' => [
                'name' => 'db_name',
                'host' => 'https://db.host.com',
                'port' => '3306', // optional
                'username' => 'username',
                'password' => 'password'
            ]
        ]
    ],

    // ------------------------------------------
    // Database for backup
    // 'Name for Backupfile' => [Login Infos]
    // ------------------------------------------
    'databases' => [
        // DB 1
        'TestDb' => [
            'name' => 'db_name',
            'host' => 'https://db.host.com',
            'port' => '3306', // optional
            'username' => 'username',
            'password' => 'password'
        ]
    ],

    // ------------------------------------------
    // Folder for backup
    // 'Name for Backupfile' => 'Path to Directory'
    // ------------------------------------------
    'directories' => [
        // Folder 1
        'TestFolder' => '/home/var/www/folder'
    ],

    // ------------------------------------------
    // System
    // -----------------------------------------
    'system' => [
        // Local Backup Folder
        'backupDirectory' => 'backup',
        'sendSuccessMessage' => true    // echo a message when finished
    ],

    // ------------------------------------------
    // Paths
    // ------------------------------------------
    'paths' => [
        // Path to mysqldump
        'mysqldump' => '/usr/local/bin'
    ],

    // ------------------------------------------
    // FTP
    // -----------------------------------------
    'ftp' => [
        'isSftp' => true,
        'host' => 'sftp.mydomain.com',
        'port' => '22',
        'username' => 'backup',
        'password' => '5sZI^etC&',
        'folder' => 'backup/web/'
    ]
];
