<?php
/**
 * @var \Cake\I18n\FrozenTime $date
 * @var array<string> $emailBackupList
 * @var array<string> $file
 * @var string $datasource
 */
?>

<h1><?= __('DB backup email') ?></h1>

<p>
    <?= __('The database backup has been sent to the following email addresses:') ?>
<ul>
    <?php foreach ($emailBackupList as $email) : ?>
        <li><?= $email ?></li>
    <?php endforeach; ?>
</ul>
</p>

<p>
    <strong><?= __('Backup file: ') ?></strong>
    <?= sprintf('%s (%s)', $file['basename'], $file['size']) ?>
</p>

<p>
    <strong><?= __('Created at: ') ?></strong>
    <?= $date->format('Y-m-d H:i:s') ?>
</p>