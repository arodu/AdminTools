<?php
/**
 * @var \Cake\I18n\FrozenTime $date
 * @var array<string> $emailBackupList
 * @var array<string> $file
 * @var string $datasource
 */
?>

<h1><?= __('Backup: {0}', $name) ?></h1>

<?php foreach ($backups as $backup) : ?>
    <p>
        <b><?= __('Datasource: ') ?></b><?= $backup['datasource']['name'] ?><br>
        <b><?= __('Database: ') ?></b><?= $backup['datasource']['database'] ?><br>
        <b><?= __('Host: ') ?></b><?= $backup['datasource']['host'] ?><br>
        <b><?= __('Port: ') ?></b><?= $backup['datasource']['port'] ?><br>
        <b><?= __('Scheme: ') ?></b><?= $backup['datasource']['scheme'] ?><br>
        <b><?= __('Compress: ') ?></b><?= $backup['compress'] ?><br>
        <b><?= __('File: ') ?></b><?= $backup['fileinfo']['basename'] ?><br>
        <b><?= __('Size: ') ?></b><?= $backup['fileinfo']['size'] ?><br>
        <b><?= __('Created at: ') ?></b><?= $backup['datetime']->format('Y-m-d H:i:s') ?><br>
    </p>
<?php endforeach; ?>