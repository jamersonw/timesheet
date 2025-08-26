
<?php
/**
 * Ensures that the module init file can't be accessed directly, only within the application.
 */
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Timesheet
Description: Sistema de apontamento de horas com aprovação para profissionais e gerentes de projeto
Version: 1.3.17
Requires at least: 2.3.*
Author: Perfex CRM Module Developer
*/

define('TIMESHEET_MODULE_NAME', 'timesheet');
define('TIMESHEET_MODULE_VERSION', '1.3.2');

/**
 * Register activation hook
 */
register_activation_hook(TIMESHEET_MODULE_NAME, 'timesheet_activation_hook');

function timesheet_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
 * Register deactivation hook
 */
register_deactivation_hook(TIMESHEET_MODULE_NAME, 'timesheet_deactivation_hook');

function timesheet_deactivation_hook()
{
    // Ações de limpeza na desativação (geralmente não usado para apagar dados)
}

/**
 * Register uninstall hook
 */
register_uninstall_hook(TIMESHEET_MODULE_NAME, 'timesheet_uninstall_hook');

function timesheet_uninstall_hook()
{
    $CI = &get_instance();

    // Apaga as tabelas do módulo
    $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'timesheet_entries`');
    $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'timesheet_approvals`');

    // Remove as opções do módulo
    $CI->db->where('name LIKE', 'timesheet_%');
    $CI->db->delete(db_prefix() . 'options');
}

/**
 * Register language files
 */
register_language_files(TIMESHEET_MODULE_NAME, [TIMESHEET_MODULE_NAME]);

/**
 * Hook para inicializar menus, permissões e os hooks de sincronização.
 * Usamos 'admin_init' para garantir que tudo seja carregado no ambiente administrativo.
 */
hooks()->add_action('admin_init', 'timesheet_init_all');

function timesheet_init_all()
{
    $CI = &get_instance();

    // 1. REGISTRO DOS ITENS DE MENU
    if (has_permission('timesheet', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('timesheet', [
            'name'     => _l('timesheet_my_timesheet'),
            'href'     => admin_url('timesheet'),
            'icon'     => 'fa fa-clock-o',
            'position' => 30,
        ]);
    }

    if (is_admin() || timesheet_can_manage_any_project(get_staff_user_id())) {
        $CI->app_menu->add_sidebar_menu_item('timesheet_manage', [
            'name'     => _l('timesheet_approvals'),
            'href'     => admin_url('timesheet/manage'),
            'icon'     => 'fa fa-check-circle',
            'position' => 31,
        ]);
    }

    // 2. REGISTRO DAS PERMISSÕES
    $capabilities = [];
    $capabilities['capabilities'] = [
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];
    register_staff_capabilities('timesheet', $capabilities, _l('timesheet'));

    // Log inicial para confirmar que hooks estão sendo registrados
    log_activity('[Timesheet Init] Registrando hooks de sincronização...');
    
    // Hooks de sincronização com o sistema de timers do Perfex
    hooks()->add_action('task_timer_started', 'timesheet_sync_from_core_timer_started');
    hooks()->add_action('task_timer_deleted', 'timesheet_sync_from_core_timer_deleted');
    hooks()->add_action('task_timer_stopped', 'timesheet_sync_from_core_timer_stopped');
    hooks()->add_action('task_timer_updated', 'timesheet_sync_from_core_timer_updated');
    
    // Testar hooks alternativos que podem existir no Perfex
    hooks()->add_action('after_task_timer_added', 'timesheet_test_hook_1');
    hooks()->add_action('after_task_timer_updated', 'timesheet_test_hook_2');
    hooks()->add_action('after_task_timer_deleted', 'timesheet_test_hook_3');
    hooks()->add_action('task_timer_finished', 'timesheet_test_hook_4');
    hooks()->add_action('timer_stopped', 'timesheet_test_hook_5');
    hooks()->add_action('timer_updated', 'timesheet_test_hook_6');
    hooks()->add_action('timer_deleted', 'timesheet_test_hook_7');
    
    log_activity('[Timesheet Init] Hooks registrados com sucesso');
}

/**
 * Função de callback para o hook de INÍCIO de timer.
 */
function timesheet_sync_from_core_timer_started($data)
{
    $CI = &get_instance();
    
    // Log detalhado para debug
    log_activity('[Timesheet Hook DEBUG] task_timer_started CHAMADO! Dados recebidos: ' . json_encode($data));
    
    $task_id = $data['task_id'] ?? null;

    if ($task_id) {
        $CI->load->model('timesheet/timesheet_model');
        log_activity('[Timesheet Hook] Timer iniciado para tarefa: ' . $task_id);
        // A sincronização é feita quando o timer é finalizado
    } else {
        log_activity('[Timesheet Hook ERROR] task_timer_started chamado mas task_id não encontrado');
    }
}

/**
 * Função de callback para o hook de EXCLUSÃO de timer.
 * É chamada pelo Perfex quando um timer é deletado.
 */
function timesheet_sync_from_core_timer_deleted($data)
{
    $CI = &get_instance();
    
    // Log detalhado para debug
    log_activity('[Timesheet Hook DEBUG] task_timer_deleted CHAMADO! Dados recebidos: ' . json_encode($data));
    
    $timer_id = $data['id'] ?? null;
    $task_id = $data['task_id'] ?? null;

    if ($timer_id && $task_id) {
        $CI->load->model('timesheet/timesheet_model');
        log_activity('[Timesheet Hook] Timer deletado - ID: ' . $timer_id . ', Tarefa: ' . $task_id);

        // Buscar staff_id do timer antes de deletar referência
        $CI->db->where('perfex_timer_id', $timer_id);
        $entries = $CI->db->get(db_prefix() . 'timesheet_entries')->result();
        
        if ($entries) {
            log_activity('[Timesheet Hook] Encontradas ' . count($entries) . ' entradas vinculadas ao timer deletado');
            foreach ($entries as $entry) {
                // Limpar referência do timer deletado
                $CI->db->where('id', $entry->id);
                $CI->db->update(db_prefix() . 'timesheet_entries', ['perfex_timer_id' => null]);
                
                // Recalcular horas da tarefa
                $result = $CI->timesheet_model->recalculate_task_hours($task_id, $entry->staff_id);
                log_activity('[Timesheet Hook] Recálculo após exclusão: ' . ($result ? 'SUCESSO' : 'FALHA'));
            }
        } else {
            log_activity('[Timesheet Hook] Nenhuma entrada do timesheet encontrada para timer deletado ID: ' . $timer_id);
        }
    } else {
        log_activity('[Timesheet Hook ERROR] task_timer_deleted chamado mas dados insuficientes - Timer ID: ' . $timer_id . ', Task ID: ' . $task_id);
    }
}

/**
 * Hook para quando timer é parado/finalizado
 */
function timesheet_sync_from_core_timer_stopped($data)
{
    $CI = &get_instance();
    
    // Log detalhado para debug
    log_activity('[Timesheet Hook DEBUG] task_timer_stopped CHAMADO! Dados recebidos: ' . json_encode($data));
    
    $timer_id = $data['id'] ?? null;
    $task_id = $data['task_id'] ?? null;
    $staff_id = $data['staff_id'] ?? null;

    if ($timer_id && $task_id && $staff_id) {
        $CI->load->model('timesheet/timesheet_model');
        log_activity('[Timesheet Hook] Timer finalizado - ID: ' . $timer_id . ', Tarefa: ' . $task_id . ', Staff: ' . $staff_id);

        // Recalcular horas da tarefa após timer finalizado
        $result = $CI->timesheet_model->recalculate_task_hours($task_id, $staff_id);
        log_activity('[Timesheet Hook] Resultado do recálculo: ' . ($result ? 'SUCESSO' : 'FALHA'));
    } else {
        log_activity('[Timesheet Hook ERROR] task_timer_stopped chamado mas dados insuficientes - Timer ID: ' . $timer_id . ', Task ID: ' . $task_id . ', Staff ID: ' . $staff_id);
    }
}

/**
 * Hook para quando timer é editado
 */
function timesheet_sync_from_core_timer_updated($data)
{
    $CI = &get_instance();
    
    // Log detalhado para debug
    log_activity('[Timesheet Hook DEBUG] task_timer_updated CHAMADO! Dados recebidos: ' . json_encode($data));
    
    $timer_id = $data['id'] ?? null;
    $task_id = $data['task_id'] ?? null;
    $staff_id = $data['staff_id'] ?? null;

    if ($timer_id && $task_id && $staff_id) {
        $CI->load->model('timesheet/timesheet_model');
        log_activity('[Timesheet Hook] Timer atualizado - ID: ' . $timer_id . ', Tarefa: ' . $task_id . ', Staff: ' . $staff_id);

        // Recalcular horas da tarefa após edição
        $result = $CI->timesheet_model->recalculate_task_hours($task_id, $staff_id);
        log_activity('[Timesheet Hook] Resultado do recálculo após edição: ' . ($result ? 'SUCESSO' : 'FALHA'));
    } else {
        log_activity('[Timesheet Hook ERROR] task_timer_updated chamado mas dados insuficientes - Timer ID: ' . $timer_id . ', Task ID: ' . $task_id . ', Staff ID: ' . $staff_id);
    }
}

/**
 * Funções de teste para identificar hooks que realmente funcionam
 */
function timesheet_test_hook_1($data) {
    log_activity('[Timesheet Hook TEST] after_task_timer_added FUNCIONA! Dados: ' . json_encode($data));
    timesheet_process_timer_change($data, 'after_task_timer_added');
}

function timesheet_test_hook_2($data) {
    log_activity('[Timesheet Hook TEST] after_task_timer_updated FUNCIONA! Dados: ' . json_encode($data));
    timesheet_process_timer_change($data, 'after_task_timer_updated');
}

function timesheet_test_hook_3($data) {
    log_activity('[Timesheet Hook TEST] after_task_timer_deleted FUNCIONA! Dados: ' . json_encode($data));
    timesheet_process_timer_change($data, 'after_task_timer_deleted');
}

function timesheet_test_hook_4($data) {
    log_activity('[Timesheet Hook TEST] task_timer_finished FUNCIONA! Dados: ' . json_encode($data));
    timesheet_process_timer_change($data, 'task_timer_finished');
}

function timesheet_test_hook_5($data) {
    log_activity('[Timesheet Hook TEST] timer_stopped FUNCIONA! Dados: ' . json_encode($data));
    timesheet_process_timer_change($data, 'timer_stopped');
}

function timesheet_test_hook_6($data) {
    log_activity('[Timesheet Hook TEST] timer_updated FUNCIONA! Dados: ' . json_encode($data));
    timesheet_process_timer_change($data, 'timer_updated');
}

function timesheet_test_hook_7($data) {
    log_activity('[Timesheet Hook TEST] timer_deleted FUNCIONA! Dados: ' . json_encode($data));
    timesheet_process_timer_change($data, 'timer_deleted');
}

/**
 * Função centralizada para processar alterações de timer
 */
function timesheet_process_timer_change($data, $hook_name) {
    $CI = &get_instance();
    $CI->load->model('timesheet/timesheet_model');
    
    // Tentar extrair informações dos dados
    $timer_id = $data['id'] ?? $data['timer_id'] ?? null;
    $task_id = $data['task_id'] ?? null;
    $staff_id = $data['staff_id'] ?? null;
    
    log_activity('[Timesheet Hook] Processando ' . $hook_name . ' - Timer: ' . $timer_id . ', Task: ' . $task_id . ', Staff: ' . $staff_id);
    
    if ($task_id && $staff_id) {
        $result = $CI->timesheet_model->recalculate_task_hours($task_id, $staff_id);
        log_activity('[Timesheet Hook] Sincronização via ' . $hook_name . ': ' . ($result ? 'SUCESSO' : 'FALHA'));
    }
}

/**
 * Função auxiliar para verificar se o usuário pode gerenciar projetos.
 */
function timesheet_can_manage_any_project($staff_id)
{
    if (is_admin($staff_id)) {
        return true;
    }

    $CI = &get_instance();
    $CI->db->select('COUNT(*) as count');
    $CI->db->from(db_prefix() . 'projects');
    $CI->db->where('addedfrom', $staff_id);
    $result = $CI->db->get()->row();

    return $result && $result->count > 0;
}

/**
 * Carrega os assets (CSS/JS) do módulo.
 */
hooks()->add_action('app_admin_head', 'timesheet_load_admin_assets');

function timesheet_load_admin_assets()
{
    $CI = &get_instance();
    if (strpos($CI->uri->uri_string(), 'timesheet') !== false) {
        echo '<link rel="stylesheet" href="' . module_dir_url('timesheet', 'assets/css/timesheet.css') . '">';
    }
}

/**
 * Carrega o helper do módulo.
 */
$CI = &get_instance();
$CI->load->helper(TIMESHEET_MODULE_NAME . '/timesheet');
