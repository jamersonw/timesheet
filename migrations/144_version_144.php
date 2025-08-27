
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_144 extends App_module_migration
{
    public function up()
    {
        // Atualização para versão 1.4.4
        // Nova funcionalidade: Permissão específica para aprovação de timesheet
        
        // Adicionar novas permissões do módulo timesheet
        $permissions = [];

        // Basic timesheet permissions
        $permissions[] = [
            'id' => 1,
            'name' => 'Visualizar',
            'short_name' => 'view',
        ];
        $permissions[] = [
            'id' => 2,
            'name' => 'Criar',
            'short_name' => 'create',
        ];
        $permissions[] = [
            'id' => 3,
            'name' => 'Editar',
            'short_name' => 'edit',
        ];
        $permissions[] = [
            'id' => 4,
            'name' => 'Deletar',
            'short_name' => 'delete',
        ];

        // NEW: Approval permission
        $permissions[] = [
            'id' => 5,
            'name' => 'Aprovar Timesheet',
            'short_name' => 'approve',
        ];

        add_module_permissions('timesheet', $permissions);
        
        // Atualizar versão do módulo
        if (!get_option('timesheet_module_version')) {
            add_option('timesheet_module_version', '1.4.4', 1);
        } else {
            update_option('timesheet_module_version', '1.4.4');
        }

        // Log da atualização
        log_activity('[Timesheet v1.4.4] Nova permissão: Usuários com função específica podem aprovar timesheets');
    }

    public function down()
    {
        // Rollback para versão anterior se necessário
        update_option('timesheet_module_version', '1.4.3');
    }
}
