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

        $parser->addOption('compress', [
            'help' => 'Compress the backup',
            'required' => false,
            'default' => 'gzip',
            'short' => 'c',
        ]);

        $parser->addOption('name', [
            'help' => 'Name to save the backup',
            'required' => false,
            'default' => null,
            'short' => 'n',
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

        debug($args->getOptions());

        $emailBackupList = array_filter(Configure::read('emailBackupList'));

        if (empty($emailBackupList)) {
            $io->warning('Email backup list not configured');

            return self::CODE_SUCCESS;
        }

        try {
            $datasource = $args->getOption('datasource');
            $gzip = true; //$args->getOption('gzip') ?? true;
            $config = \Cake\Datasource\ConnectionManager::get($datasource)->config();
            $now = FrozenTime::now();
            $name = $args->getOption('name') ?? $datasource;
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
     * Sends an email with the backup file attached.
     *
     * @param array $options An array of options for sending the email.
     *   - emailBackupList: The list of email addresses to send the backup to.
     *   - name: The name of the backup.
     *   - date: The date of the backup.
     *   - file: The backup file information.
     *   - datasource: The datasource used for the backup.
     * @return void
     */
    protected function sendEmail(array $options = []): void
    {
        $emailConfig = Configure::read('AdminTools.backup.email');

        $mailer = new \Cake\Mailer\Mailer($emailConfig['config'] ?? 'default');
        $mailer
            ->setTo($options['emailBackupList'])
            ->setSubject($emailConfig['subject'] ?? __('Backup {0} {1}', $options['name'], $options['date']->format('Y-m-d H:i:s')))
            ->setEmailFormat($emailConfig['format'] ?? 'both')
            ->setAttachments([$options['file']['path']])
            ->setViewVars([
                'name' => $options['name'],
                'file' => $options['file'],
                'date' => $options['date'],
                'emailBackupList' => $options['emailBackupList'],
                'datasource' => $options['datasource'],
            ])
            ->viewBuilder()
                ->setTemplate($emailConfig['template'] ?? 'AdminTools.default')
                ->setLayout($emailConfig['layout'] ?? 'default');
        $mailer->deliver();
    }
}
