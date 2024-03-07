<?php

use Cake\Core\Configure;
use Cake\Log\Log;

try {
    Configure::load('AdminTools.admin_tools');
    Configure::load('admin_tools');
} catch (\Exception $e) {
    Log::debug('AdminTools: ' . $e->getMessage());
}