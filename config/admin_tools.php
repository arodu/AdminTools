<?php

use Cake\Console\ConsoleIo;

return [
    'AdminTools' => [
        'backupDb' => [
            /**
             * @var bool
             * 
             * Enable or disable the backup
             */
            'enabled' => filter_var(env('AT_BACKUP_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

            /**
             * @var string
             * 
             * The name of the backup
             */
            'name' => env('AT_BACKUP_NAME') ?? 'backup-name',

            /**
             * @var string
             * 
             * A list of datasources to backup, you can use an array or a string separated by comma
             */
            'datasources' => env('AT_BACKUP_DATASOURCES') ?? 'default',

            /**
             * @var string|null|false
             * 
             * The compress method to use: [gzip, zip, rar]
             * for none use [none, false or null]
             */
            'compress' => env('AT_BACKUP_COMPRESS') ?? 'gzip',

            /**
             * @var string
             * 
             * The path where the backup file will be saved
             */
            'path' => env('AT_BACKUP_PATH') ?? TMP,

            /**
             * @var string
             * 
             * The file template to use
             * - :name = backup name
             * - :datasource = datasource name
             * - :database = database name
             * - :date = current date
             * - :datetime = current datetime
             * - :host = database host
             * - :port = database port
             * - :scheme = database scheme
             */
            'fileTemplate' => env('AT_BACKUP_FILE_TEMPLATE') ?? ':name_:datasource_:datetime',

            /**
             * @var string|null|false
             * 
             * The file extension to use, default is sql
             */
            'fileExtension' => env('AT_BACKUP_FILE_EXTENSION') ?? 'sql',

            /**
             * @var bool
             * 
             * Remove the file after execution of the command
             */
            'removeFile' => env('AT_BACKUP_REMOVE_FILE') ?? true,

            /**
             * @var callable|null|false
             * 
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
             * @var callable|null|false
             * 
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

            /**
             * @var array|null
             * 
             * Extra options to use for each datasource
             * this options will override the default options
             * 
             * Example:
             * 'datasourceOptions' => [
             *     'default' => [
             *         'name' => 'otherNameBackup',
             *         'path' => 'other/path/to/save/backup',
             *         'compress' => 'zip',
             *         'removeFile' => false,
             *     ],
             * ],
             */
            'datasourceOptions' => [],
        ],
        'backupEmail' => [
            /**
             * @var bool
             * 
             * Enable or disable the email backup
             */
            'enabled' => env('AT_BACKUP_EMAIL_ENABLE') ?? true,

            /**
             * @var string
             * 
             * The email configuration to use
             */
            'config' => env('AT_BACKUP_EMAIL_CONFIG') ?? 'default',

            /**
             * @var string
             * 
             * The email format to use
             * html, text, both
             */
            'format' => env('AT_BACKUP_EMAIL_FORMAT') ?? 'html',

            /**
             * @var array|string|null
             * 
             * The email recipients
             */
            'emailList' => env('AT_BACKUP_EMAIL_LIST') ?? null,

            /**
             * @var string|null
             * 
             * The email subject
             */
            'subject' => env('AT_BACKUP_EMAIL_SUBJECT') ?? null,

            /**
             * @var string
             * 
             * The email template
             */
            'template' => env('AT_BACKUP_EMAIL_TEMPLATE') ?? 'AdminTools.backup',

            /**
             * @var string
             * 
             * The email layout
             */
            'layout' => env('AT_BACKUP_EMAIL_LAYOUT') ?? 'default',
        ],
    ],
];
