<?php

return [
    'AdminTools' => [
        'backup' => [
            'datasource' => env('AT_BACKUP_DATASOURCE', 'default'),
            'compress' => env('AT_BACKUP_COMPRESS', 'gzip'),
            'path' => env('AT_BACKUP_PATH', TMP),
            'email' => [
                'enable' => env('AT_BACKUP_EMAIL_ENABLE', true),
                'config' => env('AT_BACKUP_EMAIL_CONFIG', 'default'), // email config to use
                'format' => env('AT_BACKUP_EMAIL_FORMAT', 'both'), // html, text, both
                'emailList' => env('AT_BACKUP_EMAIL_LIST', []),
                'subject' => env('AT_BACKUP_EMAIL_SUBJECT', null),
                'template' => env('AT_BACKUP_EMAIL_TEMPLATE', 'AdminTools.default'),
                'layout' => env('AT_BACKUP_EMAIL_LAYOUT', 'default'),
                'removeFileAfterSend' => env('AT_BACKUP_EMAIL_REMOVE_FILE', true),
            ],
        ],
    ],
];