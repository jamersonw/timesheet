
<?php
/**
 * Ensures that the module init file can't be accessed directly, only within the application.
 */
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Timesheet
Description: Sistema de apontamento de horas com aprovação para profissionais e gerentes de projeto - Versão Simplificada
Version: 1.5.2
Requires at least: 2.3.*
Author: Perfex CRM Module Developer
*/

define('TIMESHEET_MODULE_NAME', 'timesheet');
define('TIMESHEET_MODULE_VERSION', '1.5.2');

/**
 * Register activation hook
 */
register_activation_hook(TIMESHEET_MODULE_NAME, 'timesheet_activation_hook');

function timesheet_activation_hook()
{
    try {
        if (function_exists('log_activity')) {
            log_activity('[Timesheet Activation] Iniciando hook de ativação do módulo v1.5.2');
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

/**
 * Hook para esconder elementos nativos do timesheet e desabilitar timer para não-admins
 */
hooks()->add_action('app_admin_head', 'timesheet_hide_native_log_time_elements');
hooks()->add_action('app_admin_footer', 'timesheet_disable_timer_globally');

/**
 * Injeta CSS para esconder o botão e o formulário de apontamento manual de horas do projeto.
 * Também esconde botões de cronômetro e quadro de tempo para não-administradores.
 */
function timesheet_hide_native_log_time_elements()
{
    $CI = &get_instance();
    $current_staff_id = get_staff_user_id();
    
    // Se for administrador, não aplicar restrições
    if (is_admin($current_staff_id)) {
        return;
    }
    
    // Verificar se está em uma página de tarefa/projeto
    $is_task_page = (strpos($CI->uri->uri_string(), 'tasks/view/') !== false || 
                     strpos($CI->uri->uri_string(), 'projects/view/') !== false);
    
    // CSS para esconder elementos do timesheet nativo + cronômetro
    echo '<style type="text/css">
        /* Esconder formulário de log manual de horas */
        #log_time_wrapper,
        .project-overview-task-timer,
        .task-timer-wrapper,
        .task-single-log-time-form,
        #timesheet-entry,
        a[onclick="new_timesheet();return false;"],
        
        /* Esconder botões de cronômetro */
        a[onclick*="timer_action"],
        .btn[onclick*="timer_action"],
        
        /* Esconder botão de quadro de tempo */
        .btn[onclick*="slideToggle(\'#task_single_timesheets\')"],
        a[onclick*="slideToggle(\'#task_single_timesheets\')"],
        
        /* Esconder seção de timesheets da tarefa */
        #task_single_timesheets,
        
        /* Esconder outros elementos relacionados a timer */
        .task-timer-buttons,
        .timer-controls {
            display: none !important;
        }
        
        /* Esconder texto do cronômetro se existir */
        .fa-clock:not(.timesheet-icon),
        .fa-regular.fa-clock {
            display: none !important;
        }
    </style>';
    
    // JavaScript para desabilitar funcionalidades de timer
    if ($is_task_page) {
        echo '<script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function() {
                // Remover eventos de timer
                var timerButtons = document.querySelectorAll("[onclick*=\"timer_action\"]");
                timerButtons.forEach(function(btn) {
                    btn.style.display = "none";
                    btn.onclick = function(e) { 
                        e.preventDefault(); 
                        alert("Função de cronômetro desabilitada. Use o módulo Timesheet para apontamento de horas.");
                        return false; 
                    };
                });
                
                // Remover botões de quadro de tempo
                var timesheetButtons = document.querySelectorAll("[onclick*=\"slideToggle\"]");
                timesheetButtons.forEach(function(btn) {
                    if (btn.onclick && btn.onclick.toString().includes("task_single_timesheets")) {
                        btn.style.display = "none";
                        btn.onclick = function(e) { 
                            e.preventDefault(); 
                            alert("Quadro de tempo desabilitado. Use o módulo Timesheet para gerenciar horas.");
                            return false; 
                        };
                    }
                });
                
                // Esconder seção de timesheets se existir
                var timesheetSection = document.getElementById("task_single_timesheets");
                if (timesheetSection) {
                    timesheetSection.style.display = "none";
                }
                
                console.log("Timesheet Module: Cronômetro e quadro de tempo desabilitados para usuário não-admin");
            });
        </script>';
    }
}

/**
 * Função adicional para desabilitar timer globalmente em todas as páginas
 */
function timesheet_disable_timer_globally()
{
    $current_staff_id = get_staff_user_id();
    
    // Se for administrador, não aplicar restrições
    if (is_admin($current_staff_id)) {
        return;
    }
    
    echo '<script type="text/javascript">
        // Função global para interceptar todas as chamadas de timer_action
        if (typeof window.original_timer_action === "undefined") {
            // Salvar função original se existir
            if (typeof timer_action !== "undefined") {
                window.original_timer_action = timer_action;
            }
            
            // Sobrescrever função timer_action
            window.timer_action = function(element, taskId) {
                alert("⚠️ Função de cronômetro desabilitada.\\n\\nPara apontamento de horas, acesse:\\nTimesheet → Meu Timesheet");
                return false;
            };
            
            // Interceptar cliques em botões de timer via event delegation
            document.addEventListener("click", function(e) {
                var target = e.target;
                var button = target.closest("a[onclick*=\"timer_action\"], button[onclick*=\"timer_action\"]");
                
                if (button) {
                    e.preventDefault();
                    e.stopPropagation();
                    alert("⚠️ Cronômetro desabilitado para sua função.\\n\\nUse: Timesheet → Meu Timesheet");
                    return false;
                }
                
                // Também interceptar botões de quadro de tempo
                var timesheetBtn = target.closest("a[onclick*=\"task_single_timesheets\"], button[onclick*=\"task_single_timesheets\"]");
                if (timesheetBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    alert("⚠️ Quadro de tempo desabilitado.\\n\\nUse: Timesheet → Aprovações Semanais");
                    return false;
                }
            }, true);
            
            console.log("Timesheet Module: Timer globalmente desabilitado para usuário não-admin ID " + ' . $current_staff_id . ');
        }
    </script>';
}

/**
 * Carrega o helper do módulo.
 */
$CI = &get_instance();
$CI->load->helper(TIMESHEET_MODULE_NAME . '/timesheet');
