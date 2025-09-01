
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_150 extends App_module_migration
{
    public function up()
    {
        // Atualização para versão 1.5.0 - Internacionalização completa
        
        // Atualizar versão do módulo
        if (!get_option('timesheet_module_version')) {
            add_option('timesheet_module_version', '1.5.0', 1);
        } else {
            update_option('timesheet_module_version', '1.5.0');
        }
        
        // Log da atualização
        log_activity('[Timesheet Migration] Módulo atualizado para versão 1.5.0 - Internacionalização completa implementada');
    }

    public function down()
    {
        // Rollback para versão anterior se necessário
        update_option('timesheet_module_version', '1.4.9');
    }
}
