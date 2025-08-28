<?php
/**
 * Ensures that the module init file can't be accessed directly, only within the application.
 */
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Timesheet
Description: Sistema de apontamento de horas com aprovação para profissionais e gerentes de projeto - Versão Simplificada
Version: 1.4.6
Requires at least: 2.3.*
Author: Perfex CRM Module Developer
*/

define('TIMESHEET_MODULE_NAME', 'timesheet');
define('TIMESHEET_MODULE_VERSION', '1.4.5');

/**
 * Register activation hook
 */
register_activation_hook(TIMESHEET_MODULE_NAME, 'timesheet_activation_hook');

function timesheet_activation_hook()
{
    try {
        if (function_exists('log_activity')) {
            log_activity('[Timesheet Activation] Iniciando hook de ativação do módulo v1.4.4');
        }
        
        $CI = &get_instance();
        if (!$CI) {
            if (function_exists('log_activity')) {
                log_activity('[Timesheet Activation ERROR] Falha ao obter instância do CodeIgniter');
            }
            throw new Exception('CodeIgniter instance not available');
        }
        
        if (function_exists('log_activity')) {
            log_activity('[Timesheet Activation] CodeIgniter instance obtida com sucesso');
            log_activity('[Timesheet Activation] Executando install.php...');
        }
        
        // Verificar se o arquivo existe antes de incluir
        $install_file = __DIR__ . '/install.php';
        if (!file_exists($install_file)) {
            throw new Exception('Install file not found: ' . $install_file);
        }
        
        // Include com verificação de retorno
        $result = include_once($install_file);
        
        if (function_exists('log_activity')) {
            if ($result === false) {
                log_activity('[Timesheet Activation WARNING] Install.php retornou false');
            } else {
                log_activity('[Timesheet Activation] Hook de ativação concluído com sucesso');
            }
        }
        
        // Sempre retornar true para não quebrar a ativação
        return true;
        
    } catch (Exception $e) {
        if (function_exists('log_activity')) {
            log_activity('[Timesheet Activation ERROR] ' . $e->getMessage());
            log_activity('[Timesheet Activation ERROR] File: ' . $e->getFile() . ' Line: ' . $e->getLine());
        }
        
        // Log no error_log do PHP como backup
        error_log('[Timesheet Activation] ERROR: ' . $e->getMessage());
        
        // Não re-throw para evitar quebrar completamente a ativação
        return false;
    }
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
 * Hook para inicializar menus e permissões.
 * VERSÃO SIMPLIFICADA - SEM HOOKS BIDIRECIONAIS
 */
hooks()->add_action('admin_init', 'timesheet_init_menu_and_permissions');

function timesheet_init_menu_and_permissions()
{
    $CI =& get_instance();

    // Verificar permissões simplificadas
    $can_view = has_permission('timesheet', '', 'view') || is_admin();
    $can_approve = has_permission('timesheet', '', 'approve') || is_admin() || timesheet_can_manage_any_project(get_staff_user_id());
    
    // Mostrar menu se tiver pelo menos uma das permissões
    $has_any_permission = $can_view || $can_approve;

    // 1) CRIA O GRUPO (sem href) – aparece para quem tiver permissão de view ou approve
    if ($has_any_permission) {
        $CI->app_menu->add_sidebar_menu_item('timesheet_group', [
            'name'     => _l('timesheet'),
            'icon'     => 'fa fa-calendar',
            'position' => 30,
        ]);
    }

    // 2) FILHOS DO MENU

    // Meu Timesheet – apenas para quem tem permissão "view"
    if ($can_view) {
        $CI->app_menu->add_sidebar_children_item('timesheet_group', [
            'slug'     => 'timesheet_my_timesheet',
            'name'     => _l('timesheet_my_timesheet'),
            'href'     => admin_url('timesheet'),
            'position' => 1,
        ]);
    }

    // Aprovação Semanal – apenas para quem tem permissão "approve"
    if ($can_approve) {
        $CI->app_menu->add_sidebar_children_item('timesheet_group', [
            'slug'     => 'timesheet_weekly_manage',
            'name'     => _l('timesheet_weekly_approvals'),
            'href'     => admin_url('timesheet/manage_weekly'),
            'position' => 3,
        ]);

        // Aprovação Rápida – apenas para quem tem permissão "approve"
        $CI->app_menu->add_sidebar_children_item('timesheet_group', [
            'slug'     => 'timesheet_manage',
            'name'     => _l('timesheet_quick_approvals'),
            'href'     => admin_url('timesheet/manage'),
            'position' => 4,
        ]);
    }

    // 3) PERMISSÕES - Registrar apenas as duas permissões necessárias
    $capabilities = [];
    $capabilities['capabilities'] = [
        'view'    => _l('timesheet_permission_view'),
        'approve' => _l('timesheet_permission_approve'),
    ];
    register_staff_capabilities('timesheet', $capabilities, _l('timesheet'));
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

// NOVO HOOK: Adiciona CSS customizado para esconder elementos
hooks()->add_action('app_admin_head', 'timesheet_hide_native_log_time_elements');

/**
 * NOVA FUNÇÃO: Injeta CSS para esconder o botão e o formulário de apontamento manual de horas do projeto.
 * Esta é a abordagem mais segura, pois não modifica os arquivos do núcleo do Perfex.
 */
function timesheet_hide_native_log_time_elements()
{
    // A verificação esconde os elementos apenas se o usuário NÃO for admin.
    if (!is_admin()) {
        $CI = &get_instance();
        // Verifica se estamos na página de um projeto específico para aplicar o CSS
        if ($CI->uri->segment(1) == 'admin' && $CI->uri->segment(2) == 'projects' && $CI->uri->segment(3) == 'view') {
            echo '<style>
                /* * ALTERAÇÃO: O seletor foi atualizado para ser mais específico,
                 * usando o atributo onclick, conforme confirmado na inspeção.
                 */
                a[onclick="new_timesheet();return false;"] {
                    display: none !important;
                }

                /* Esconde a seção inteira de entrada manual de tempo para garantir */
                #timesheet-entry {
                    display: none !important;
                }
            </style>';
        }
    }
}

/**
 * Carrega o helper do módulo.
 */
$CI = &get_instance();
$CI->load->helper(TIMESHEET_MODULE_NAME . '/timesheet');