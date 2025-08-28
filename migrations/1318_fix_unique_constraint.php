
<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Fix_unique_constraint extends App_module_migration
{
    public function up()
    {
        try {
            // Remover a constraint antiga se existir
            $this->db->query('ALTER TABLE `' . db_prefix() . 'timesheet_approvals` DROP INDEX IF EXISTS `unique_staff_week`');
            
            // Adicionar nova constraint que inclui task_id
            $this->db->query('ALTER TABLE `' . db_prefix() . 'timesheet_approvals` ADD UNIQUE KEY `unique_staff_week_task` (`staff_id`, `week_start_date`, `task_id`)');
            
            log_activity('[Timesheet Migration 1318] Constraint única atualizada para incluir task_id');
            
        } catch (Exception $e) {
            log_activity('[Timesheet Migration 1318 ERROR] Erro ao atualizar constraint: ' . $e->getMessage());
            throw $e;
        }
    }

    public function down()
    {
        try {
            // Remover a nova constraint
            $this->db->query('ALTER TABLE `' . db_prefix() . 'timesheet_approvals` DROP INDEX IF EXISTS `unique_staff_week_task`');
            
            // Restaurar constraint antiga (apenas para desenvolvimento - em produção não recomendado)
            $this->db->query('ALTER TABLE `' . db_prefix() . 'timesheet_approvals` ADD UNIQUE KEY `unique_staff_week` (`staff_id`, `week_start_date`)');
            
        } catch (Exception $e) {
            log_activity('[Timesheet Migration 1318 Down ERROR] Erro ao reverter constraint: ' . $e->getMessage());
            throw $e;
        }
    }
}
