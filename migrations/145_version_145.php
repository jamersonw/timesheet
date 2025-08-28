
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_145 extends App_module_migration
{
    public function up()
    {
        // Atualização para versão 1.4.5
        // Correção crítica: Erro de sintaxe JavaScript no manage_weekly.js
        
        // Atualizar versão do módulo
        if (!get_option('timesheet_module_version')) {
            add_option('timesheet_module_version', '1.4.5', 1);
        } else {
            update_option('timesheet_module_version', '1.4.5');
        }

        // Log da atualização
        log_activity('[Timesheet v1.4.5] Correção crítica: Erro de sintaxe JavaScript na tela semanal corrigido - Aprovações funcionando perfeitamente');
    }

    public function down()
    {
        // Rollback para versão anterior se necessário
        update_option('timesheet_module_version', '1.4.4');
    }
}
