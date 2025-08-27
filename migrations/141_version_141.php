
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_141 extends App_module_migration
{
    public function up()
    {
        // Atualização para versão 1.4.1
        // Nova funcionalidade: Tela de Aprovação Semanal
        
        // Atualizar versão do módulo
        if (!get_option('timesheet_module_version')) {
            add_option('timesheet_module_version', '1.4.1', 1);
        } else {
            update_option('timesheet_module_version', '1.4.1');
        }

        // Log da atualização
        log_activity('[Timesheet v1.4.1] Nova funcionalidade: Tela de Aprovação Semanal implementada');
    }

    public function down()
    {
        // Rollback para versão anterior se necessário
        update_option('timesheet_module_version', '1.4.0');
    }
}
