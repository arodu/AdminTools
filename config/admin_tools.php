<?php

use Cake\Console\ConsoleIo;

return [
    'AdminTools' => [
        'backup' => [
            /**
             * Enable or disable the backup
             */
            'enabled' => env('AT_BACKUP_ENABLED') ?? true,

            /**
             * The name of the backup
             */
            'name' => env('AT_BACKUP_NAME') ?? 'backup',

            /**
             * A list of datasources to backup, you can use an array or a string separated by comma
             */
            'datasources' => env('AT_BACKUP_DATASOURCES') ?? 'default',

            /**
             * Extra options to use for each datasource
             * 
             * Example:
             * 'datasourceOptions' => [
             *     'default' => [
             *         'name' => 'otherNameBackup',
             *         'path' => 'other/path/to/save/backup',
             *         'compress' => 'zip',
             *         'removeFile' => false,
             *         'email' => [
             *             'enabled' => false,
             *         ],
             *     ],
             * ],
            */
            'datasourceOptions' => [],

            /**
             * The compress method to use
             * gzip, zip, rar, none
             */
            'compress' => env('AT_BACKUP_COMPRESS') ?? 'gzip',

            /**
             * The path where the backup file will be saved
             */
            'path' => env('AT_BACKUP_PATH') ?? TMP,

            /**
             * Remove the file after execution of the command
             */
            'removeFile' => env('AT_BACKUP_REMOVE_FILE') ?? true,

            /**
             * You can use a callable to execute a script before dump the file
             * This script runs by each datasource, or you can change it by datasource in datasourceOptions
             * function (array $options, ConsoleIo $io): ?string {
             *      // do something before dump file
             *      //return 'path/to/script.sh';
             *      return 'mysqldump -u root mydb > /tmp/mydb.sql';
             * },
             */
            'dumpScript' => null,

            /**
             * This anonymous function is a callback that is executed after a script is executed.
             * This script runs by each datasource, or you can change it by datasource in datasourceOptions
             * It takes an array of options and a ConsoleIo object as parameters.
             * It should return an array of options.
             * 
             * Example:
             * function (array $options, ConsoleIo $io): array {
             *      // do something after execute the script
             *      copy('/tmp/mydb.sql', 'other/path/mydb.sql');
             * 
             *      return $options;
             * },
             */
            'afterExecScript' => null,

            'email' => [
                /**
                 * Enable or disable the email backup
                 */
                'enabled' => env('AT_BACKUP_EMAIL_ENABLE') ?? true,

                /**
                 * The email configuration to use
                 */
                'config' => env('AT_BACKUP_EMAIL_CONFIG') ?? 'default',

                /**
                 * The email format to use
                 * html, text, both
                 */
                'format' => env('AT_BACKUP_EMAIL_FORMAT') ?? 'both',

                /**
                 * The email recipients
                 */
                'emailList' => env('AT_BACKUP_EMAIL_LIST') ?? '',

                /**
                 * The email subject
                 */
                'subject' => env('AT_BACKUP_EMAIL_SUBJECT') ?? null,

                /**
                 * The email template
                 */
                'template' => env('AT_BACKUP_EMAIL_TEMPLATE') ?? 'AdminTools.default',

                /**
                 * The email layout
                 */
                'layout' => env('AT_BACKUP_EMAIL_LAYOUT') ?? 'default',
            ],
        ],
    ],
];
