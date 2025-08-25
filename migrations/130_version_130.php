
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_130 extends App_module_migration
{
    public function up()
    {
        // Atualização para versão 1.3.0 - Sistema de Build Automatizado
        
        // Atualizar versão do módulo
        if (!get_option('timesheet_module_version')) {
            add_option('timesheet_module_version', '1.3.0', 1);
        } else {
            update_option('timesheet_module_version', '1.3.0');
        }
        
        // Log da atualização
        log_activity('Timesheet module updated to version 1.3.0 - Automated Build System implemented');
    }

    public function down()
    {
        // Rollback para versão anterior se necessário
        update_option('timesheet_module_version', '1.2.0');
        
        log_activity('Timesheet module rollback from version 1.3.0 to 1.2.0');
    }
}
