
<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Adicionar coluna perfex_timer_id se não existir
if (!$CI->db->field_exists('perfex_timer_id', db_prefix() . 'timesheet_entries')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'timesheet_entries` ADD COLUMN `perfex_timer_id` INT(11) DEFAULT NULL AFTER `status`');
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'timesheet_entries` ADD KEY `perfex_timer_id` (`perfex_timer_id`)');
}

// Atualizar versão do módulo
update_option('timesheet_module_version', '1.3.2');
