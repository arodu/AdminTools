<?php

declare(strict_types=1);

use Cake\I18n\Number;
use Cake\Utility\Text;

class BackupDb
{

    /**
     * @param string $file
     * @return array
     */
    public static function fileInfo(string $file): array
    {
        $info = pathinfo($file);
        $info['size'] = Number::toReadableSize(filesize($file));
        $info['path'] = $info['dirname'] . DS . $info['basename'];

        return $info;
    }

    /**
     * @param string|null $compress
     * @param string $filename
     * @return string
     */
    public static function compressFormat(?string $compress, string $filename): string
    {
        if (empty($compress) || $compress === 'none') {
            return ' > ' . $filename;
        }

        if ($compress === 'gzip') {
            self::checkCommand('gzip');
            return ' | gzip -c > ' . $filename . '.gz';
        }

        if ($compress === 'zip') {
            self::checkCommand('zip');
            return ' | zip > ' . $filename . '.zip';
        }

        if ($compress === 'rar') {
            self::checkCommand('rar');
            return ' | rar > ' . $filename . '.rar';
        }

        throw new \RuntimeException('Compress format ' . $compress . ' not supported');
    }

    /**
     * @param array $config
     * @param string $filename
     * @param boolean $gzip
     * @return string
     */
    public static function getScript(array $config, string $file, bool $gzip = true): string
    {
        $script = self::getDumpScript($config);

        if ($gzip) {
            $script .= ' | gzip -c > ' . $file;
        } else {
            $script .= ' > ' . $file;
        }

        return $script;
    }

    /**
     * @param array $config
     * @param string|null $filename
     * @param boolean $gzip
     * @return string
     */
    public static function getFile(array $options = []): string
    {
        $options = array_merge([
            'filename' => 'default',
            'gzip' => true,
            'date' => null,
        ], $options);

        return Text::insert(
            ':filename:date.sql:gz',
            [
                'filename' => $options['filename'],
                'date' => $options['date'] ? '_' . $options['date'] : '',
                'gz' => $options['gzip'] ? '.gz' : '',
            ]
        );
    }

    /**
     * @param array $config
     * @return string
     */
    public static function getDumpScript(array $config): string
    {
        if ($config['scheme'] === 'mysql') {
            self::checkCommand('mysqldump');

            $config['host'] = $config['host'] != 'localhost' ? '--host=' . $config['host'] : '';
            $config['port'] = $config['port'] != '3306' ? '--port=' . $config['port'] : '';

            return Text::insert(
                'mysqldump --user=":username" --password=":password" :port :host :database',
                $config
            );
        }

        if ($config['scheme'] === 'pgsql') {
            self::checkCommand('pg_dump');
            $config = array_merge([
                'port' => 5432,
                'host' => 'localhost',
            ], $config);

            return Text::insert(
                'pg_dump --username=:username --port=:port --host=:host :database',
                $config
            );
        }

        if ($config['scheme'] === 'sqlite') {
            self::checkCommand('sqlite3');
            return Text::insert(
                'sqlite3 :database .dump',
                $config
            );
        }

        throw new \RuntimeException('Scheme ' . $config['scheme'] . ' not supported');
    }


    public static function checkCommand(string $command): bool
    {
        $check = Text::insert('command -v :command >/dev/null 2>&1 || { echo >&2 "I require :command but it\'s not installed.  Aborting."; exit 1;}', [
            'command' => $command,
        ]);

        return (bool) shell_exec($check);
    }
}
