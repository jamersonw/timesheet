
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_142 extends App_module_migration
{
    public function up()
    {
        // Atualização para versão 1.4.2
        // Correções: Botão cancelar aprovação e menu aprovação semanal
        
        // Atualizar versão do módulo
        if (!get_option('timesheet_module_version')) {
            add_option('timesheet_module_version', '1.4.2', 1);
        } else {
            update_option('timesheet_module_version', '1.4.2');
        }

        // Log da atualização
        log_activity('[Timesheet v1.4.2] Correções críticas: Botão cancelar aprovação e menu semanal funcionando');
    }

    public function down()
    {
        // Rollback para versão anterior se necessário
        update_option('timesheet_module_version', '1.4.1');
    }
}
