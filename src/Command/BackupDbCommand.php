<?php

declare(strict_types=1);

namespace AdminTools\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\I18n\Number;
use Cake\Utility\Text;

/**
 * BackupDb command.
 */
class BackupDbCommand extends Command
{
    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/4/en/console-commands/commands.html#defining-arguments-and-options
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $parser->addOption('datasource', [
            'help' => 'Datasource to backup',
            'required' => true,
            'default' => 'default',
            'short' => 'd',
        ]);

        $parser->addOption('gzip', [
            'help' => 'Compress the backup',
            'required' => false,
            'boolean' => true,
            'default' => true,
            'short' => 'g',
        ]);

        $parser->addOption('filename', [
            'help' => 'Filename to save the backup',
            'required' => false,
            'default' => null,
            'short' => 'f',
        ]);

        $parser->addOption('sendEmail', [
            'help' => 'Send email with the backup',
            'required' => false,
            'boolean' => true,
            'default' => true,
            'short' => 's',
        ]);

        $parser->addOption('emailBackupList', [
            'help' => 'List of emails to send the backup, separated by comma',
            'required' => false,
            'default' => null,
            'short' => 'e',
        ]);

        $parser->addOption('removeFileAfterSend', [
            'help' => 'Remove the file after send the email',
            'required' => false,
            'boolean' => true,
            'default' => true,
            'short' => 'r',
        ]);

        $parser->addOption('path', [
            'help' => 'Path to save the backup',
            'required' => false,
            'default' => TMP,
            'short' => 'p',
        ]);

        $parser->setDescription([
            'Backup a database',
            'This command will backup a database and send an email with the backup file',
        ]);

        return $parser;
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return null|void|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $io->out('BackupEmail command');

        $emailBackupList = Configure::read('emailBackupList');

        if (empty($emailBackupList)) {
            $io->error('Email backup list not configured');

            return self::CODE_ERROR;
        }

        try {
            $datasource = $args->getOption('datasource');
            $gzip = true; //$args->getOption('gzip') ?? true;
            $config = \Cake\Datasource\ConnectionManager::get($datasource)->config();
            $now = FrozenTime::now();
            $name = $args->getOption('filename') ?? $datasource;
            $filePath = TMP . $this->getFile([
                'filename' => $name,
                'gzip' => $gzip,
                'date' => $now->format('YmdHis'),
            ]);

            $io->out('Datasource: ' . $datasource);

            $bash = $this->getScript($config, $filePath, $gzip);
            shell_exec($bash);
            $fileInfo = $this->fileInfo($filePath);
            $io->success('Backup done: ' . $fileInfo['path'] . ' (' . $fileInfo['size'] . ')');

            $this->sendEmail([
                'file' => $fileInfo,
                'date' => $now,
                'emailBackupList' => $emailBackupList,
                'datasource' => $datasource,
                'name' => $name,
            ]);
            $io->success('Email sent: [' . implode(', ', $emailBackupList) . ']');

            return self::CODE_SUCCESS;
        } catch (\Exception $e) {
            $io->error($e->getMessage());

            return self::CODE_ERROR;
        } finally {
            if (file_exists($filePath ?? '')) {
                unlink($filePath);
                $io->success('File removed: ' . $filePath);
            }
        }
    }

    /**
     * @param array $config
     * @param string $filename
     * @param boolean $gzip
     * @return string
     */
    protected function getScript(array $config, string $file, bool $gzip = true): string
    {
        $script = $this->getDumpScript($config);

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
    protected function getFile(array $options = []): string
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
    protected function getDumpScript(array $config): string
    {
        if ($config['scheme'] === 'mysql') {
            $config = array_merge([
                'port' => 3306,
                'host' => 'localhost',
            ], $config);

            return Text::insert(
                'mysqldump --user=:username --password=:password --port=:port --host=:host :database',
                $config
            );
        }

        if ($config['scheme'] === 'pgsql') {
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
            return Text::insert(
                'sqlite3 :database .dump',
                $config
            );
        }

        throw new \RuntimeException('Scheme ' . $config['scheme'] . ' not supported');
    }

    protected function sendEmail(array $options = []): void
    {
        $mailer = new \Cake\Mailer\Mailer('default');
        $mailer
            ->setTo($options['emailBackupList'])
            ->setSubject('Backup ' . $options['name'] . ' ' . $options['date']->format('Y-m-d H:i:s'))
            ->setEmailFormat('html')
            ->setAttachments([$options['file']['path']])
            ->setViewVars([
                'file' => $options['file'],
                'date' => $options['date'],
                'emailBackupList' => $options['emailBackupList'],
                'datasource' => $options['datasource'],
            ])
            ->viewBuilder()
                ->setTemplate('AdminTools.backup')
                ->setLayout('AdminTools.default');
        $mailer->deliver();
    }

    protected function fileInfo(string $file): array
    {
        $info = pathinfo($file);
        $info['size'] = Number::toReadableSize(filesize($file));
        $info['path'] = $info['dirname'] . DS . $info['basename'];
        
        return $info;
    }
}
