<?php

declare(strict_types=1);

namespace AdminTools\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\FrozenTime;
use Cake\Mailer\Mailer;
use Cake\Utility\Hash;
use Exception;

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

        $settings = Configure::read('AdminTools.backup');

        $parser->addOptions([
            'datasource' => [
                'help' => 'Datasource to backup',
                'default' => Hash::get($settings, 'datasource', 'default'),
                'choices' => ConnectionManager::configured(),
                'short' => 'd',
            ],
            'compress' => [
                'help' => 'Compress the backup',
                'default' => Hash::get($settings, 'compress', 'gzip'),
                'choices' => ['gzip', 'zip', 'rar', 'none'],
                'short' => 'c',
            ],
            'name' => [
                'help' => 'backup name identifier',
                'default' => Hash::get($settings, 'name', 'default'),
                'short' => 'n',
            ],
            'sendEmail' => [
                'help' => 'Send email with the backup',
                'boolean' => true,
                'default' => Hash::get($settings, 'email.enable', true),
                'short' => 'e',
            ],
            'removeFile' => [
                'help' => 'Remove the file after execution of the command',
                'boolean' => true,
                'default' => Hash::get($settings, 'removeFile', true),
                'short' => 'r',
            ],
            'path' => [
                'help' => 'Path to save the backup',
                'default' => Hash::get($settings, 'path', TMP),
            ],
            'emailList' => [
                'help' => 'List of emails to send the backup, separated by comma',
            ],
            'emailConfig' => [
                'help' => 'Email configuration to send the backup',
                'default' => Hash::get($settings, 'email.config', 'default'),
            ],
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
        try {
            // get options
            // prepare options
            // separe options by datasource
            // execute script by datasource
            // send email
            // remove files


            $io->out('BackupEmail command');

            $settings = Configure::read('AdminTools.backup');
            $options = $this->prepareOptions($args->getOptions(), $settings);

            //debug($settings);
            debug($options);
            exit();

            $io->success('output: QUIET', 1, ConsoleIo::QUIET);
            $io->success('output: NORMAL', 1, ConsoleIo::NORMAL);
            $io->success('output: VERBOSE', 1, ConsoleIo::VERBOSE);
            $io->hr();

            $io->out('out');
            $io->info('info');
            $io->comment('comment');
            $io->warning('warning');
            $io->error('error');
            $io->success('success');
            //$io->abort('abort');
            $io->hr();

            $io->out('start progress', 1, ConsoleIo::QUIET);
            for ($i = 0; $i < 10; $i++) {
                $io->overwrite('overwrite ' . ($i * 10) . '%', 0);
                sleep(1);
            }
            $io->overwrite('overwrite 100%', 1);
            $io->success('end progress', 1, ConsoleIo::QUIET);

            //debug($settings);
            //debug($options);
            exit();

            if ($settings['enabled'] ?? false) {
                $io->warning(__('Backup DB is disabled, enable it in config/admin_tools.php'));

                return self::CODE_SUCCESS;
            }

            $datasource = $args->getOption('datasource');
            $gzip = true; //$args->getOption('gzip') ?? true;
            $dbConfig = \Cake\Datasource\ConnectionManager::get($datasource)->config();
            $date = FrozenTime::now();
            $name = $args->getOption('name') ?? $datasource;
            $filePath = TMP . $this->getFile([
                'filename' => $name,
                'gzip' => $gzip,
                'date' => $date->format('YmdHis'),
            ]);

            $io->out('Datasource: ' . $datasource);

            $bash = $this->dumpScript($options, $settings, $io);
            if (empty($bash)) {
                throw new Exception(__('No script to execute'));
            }

            shell_exec($bash);
            $file = $this->fileInfo($filePath);
            $io->success('Backup done: ' . $file['path'] . ' (' . $file['size'] . ')', 1, ConsoleIo::VERBOSE);


            //$backupData = compact('emailList', 'name', 'date', 'file', 'datasource');

            if ($settings['afterExecScript'] && is_callable($settings['afterExecScript'])) {
                $options = $settings['afterExecScript']($options, $io);
            }

            if (Hash::get($options, 'sendEmail', false) && !empty($options['emailList'])) {
                $this->sendEmail($options, $io);
            }

            return self::CODE_SUCCESS;
        } catch (Exception $e) {
            $io->error($e->getMessage());

            return self::CODE_ERROR;
        } finally {
            if (($options['removeFile'] ?? true) && file_exists($filePath ?? '')) {
                unlink($filePath);
                $io->success('File removed: ' . $filePath);
            }
        }
    }


    /**
     * Sends an email with backup details.
     *
     * @param array $options The options for sending the email.
     * - emailBackupList: array List of emails to send the backup.
     * - name: string Name of the backup.
     * - date: \Cake\I18n\FrozenTime Date of the backup.
     * - file: array File information.
     * - datasource: string Datasource of the backup.
     * - subject: string|null Subject of the email.
     * @param ConsoleIo $io The ConsoleIo object for displaying output.
     * @return void
     */
    protected function sendEmail(array $options, ConsoleIo $io): void
    {
        $emailSettings = Configure::read('AdminTools.backup.email');

        debug($options);
        debug($emailSettings);
        exit();

        $mailer = new Mailer($options['emailConfig'] ?? 'default');
        $mailer
            ->setTo(Hash::get($options, 'emailList'))
            ->setSubject(Hash::get($emailSettings, 'email.subject') ?? __('Backup {0} {1}', $options['name'], $options['date']->format('Y-m-d H:i:s')))
            ->setEmailFormat(Hash::get($emailSettings, 'format') ?? 'both')
            ->setAttachments([Hash::get($emailSettings, 'file.path')])
            ->setViewVars($options)
            ->viewBuilder()
            ->setTemplate($emailSettings['template'] ?? 'AdminTools.default')
            ->setLayout($emailSettings['layout'] ?? 'default');
        $mailer->deliver();

        $io->success('Email sent to: [' . implode(', ', Hash::get($options, 'emailList')) . ']');
    }

    /**
     * @param array $options
     * @param array $settings
     * @param \Cake\Console\ConsoleIo $io
     * @return string
     */
    protected function dumpScript(array $options, array $settings, ConsoleIo $io): string
    {
        if ($settings['dumpScript'] && is_callable($settings['dumpScript'])) {
            return $settings['dumpScript']($options, $io);
        }

        //$io->out('Dump script');
        //return 'mysqldump -u root mydb > /tmp/mydb.sql';

        return '';
    }

    /**
     * @param array $options
     * @param array $settings
     * @return array
     */
    protected function prepareOptions(array $options, array $settings): array
    {
        if (is_string($options['emailList'])) {
            $options['emailList'] = explode(',', $options['emailList']);
        }
        $options['datetime'] = FrozenTime::now();
        $options['name'] = $options['name'] ?? $options['datasource'] ?? 'default';
        $options['path'] = $options['path'] ?? TMP;

        return $options;
    }

    protected function removeFiles(array $options, ConsoleIo $io): void
    {
        if (file_exists($options['file'] ?? '')) {
            unlink($options['file']);
            $io->success('File removed: ' . $options['file']);
        }
    }
}
