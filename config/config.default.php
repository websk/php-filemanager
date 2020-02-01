<?php
return [
    'settings' => [
        'log_path' => '/var/www/log',
        'tmp_path' => '/var/www/tmp',
        'site_full_path' => '/var/www/php-filemanager',
        'storages' => [
            'files' => [
                'adapter' => 'local',
                'root_path' => '/var/www/php-filemanager/public/files',
                'url_path' => '/files',
                'allowed_extensions' => ['gif', 'jpeg', 'jpg', 'png', 'pdf', 'csv'],
                'allowed_types' => ['image/gif', 'image/jpeg', 'image/jpg', 'image/pjpeg', 'image/x-png', 'image/png', 'application/pdf', 'application/x-pdf', 'text/csv'],
            ]
        ]
    ],
];
