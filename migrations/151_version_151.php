
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_151 extends App_module_migration
{
    public function up()
    {
        // Atualização para versão 1.5.1 - Correções de Interface e Tradução
        
        // Atualizar versão do módulo
        if (!get_option('timesheet_module_version')) {
            add_option('timesheet_module_version', '1.5.1', 1);
        } else {
            update_option('timesheet_module_version', '1.5.1');
        }
        
        // Log da atualização
        log_activity('Timesheet module updated to version 1.5.1 - Interface fixes and Portuguese translations');
    }

    public function down()
    {
        // Rollback para versão anterior se necessário
        update_option('timesheet_module_version', '1.5.0');
        
        log_activity('Timesheet module rollback from version 1.5.1 to 1.5.0');
    }
}
