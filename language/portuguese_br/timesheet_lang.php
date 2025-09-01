<?php

# Version 1.0.7

$lang['timesheet'] = 'Timesheet';
$lang['timesheet_my_timesheet'] = 'Meu Timesheet';
$lang['timesheet_manage'] = 'Gerenciar Timesheets';
$lang['timesheet_approvals'] = 'Aprovações de Timesheet';
$lang['timesheet_reports'] = 'Relatórios de Timesheet';

// Days of week
$lang['timesheet_monday'] = 'Segunda';
$lang['timesheet_tuesday'] = 'Terça';
$lang['timesheet_wednesday'] = 'Quarta';
$lang['timesheet_thursday'] = 'Quinta';
$lang['timesheet_friday'] = 'Sexta';
$lang['timesheet_saturday'] = 'Sábado';
$lang['timesheet_sunday'] = 'Domingo';

// Actions
$lang['timesheet_save'] = 'Salvar';
$lang['timesheet_submit'] = 'Enviar para Aprovação';
$lang['timesheet_approve'] = 'Aprovar';
$lang['timesheet_reject'] = 'Rejeitar';
$lang['timesheet_edit'] = 'Editar';
$lang['timesheet_delete'] = 'Deletar';

// Status
$lang['timesheet_status_draft'] = 'Rascunho';
$lang['timesheet_status_submitted'] = 'Enviado';
$lang['timesheet_status_pending'] = 'Aguardando Aprovação';
$lang['timesheet_status_approved'] = 'Aprovado';
$lang['timesheet_status_rejected'] = 'Rejeitado';

// Messages
$lang['timesheet_saved_successfully'] = 'Timesheet salvo com sucesso!';
$lang['timesheet_submitted_successfully'] = 'Timesheet enviado para aprovação com sucesso!';
$lang['timesheet_approved_successfully'] = 'Timesheet aprovado com sucesso!';
$lang['timesheet_rejected_successfully'] = 'Timesheet rejeitado com sucesso!';
$lang['timesheet_no_projects_assigned'] = 'Nenhum projeto atribuído a você';
$lang['timesheet_select_project'] = 'Adicionar Projeto';
$lang['timesheet_select_task'] = 'Selecione uma Tarefa';
$lang['timesheet_hours'] = 'Horas';
$lang['timesheet_total'] = 'Total';
$lang['timesheet_week_of'] = 'Semana de';
$lang['timesheet_previous_week'] = 'Semana Anterior';
$lang['timesheet_next_week'] = 'Próxima Semana';
$lang['timesheet_this_week'] = 'Esta Semana';
$lang['timesheet_current_week'] = 'Semana Atual';

// Validation
$lang['timesheet_invalid_hours'] = 'Formato de horas inválido';
$lang['timesheet_project_required'] = 'Projeto é obrigatório';
$lang['timesheet_task_required'] = 'Tarefa é obrigatória. Por favor, selecione uma tarefa.';
$lang['timesheet_already_submitted'] = 'Esta semana já foi enviada';
$lang['timesheet_cannot_edit_approved'] = 'Não é possível editar timesheets aprovados. Cancele a aprovação primeiro.';

// Interface messages
$lang['timesheet_project_and_task_required'] = 'Por favor, selecione um projeto E uma tarefa.';
$lang['timesheet_selection_required'] = 'Seleção Obrigatória';
$lang['timesheet_project_already_added'] = 'Este projeto/tarefa já foi adicionado à sua planilha.';
$lang['timesheet_duplicate_project'] = 'Projeto Duplicado';
$lang['timesheet_add_activity_required'] = 'Você deve adicionar pelo menos um projeto/tarefa antes de enviar o timesheet.';
$lang['timesheet_no_activity_selected'] = 'Nenhuma Atividade Selecionada';
$lang['timesheet_remove_row'] = 'Remover Linha';
$lang['timesheet_confirm_remove_row'] = 'Tem certeza que deseja remover esta linha? Todas as horas lançadas nela serão perdidas.';
$lang['timesheet_submit_for_approval'] = 'Enviar para Aprovação';
$lang['timesheet_server_communication_error'] = 'Erro de comunicação com o servidor';
$lang['timesheet_connection_error_submit'] = 'Erro de conexão ao enviar para aprovação';
$lang['timesheet_save_before_submit_error'] = 'Falha ao salvar as horas antes do envio. Tente novamente.';
$lang['timesheet_cancel_submission'] = 'Cancelar Submissão';
$lang['timesheet_keep_as_is'] = 'Manter Como Está';
$lang['timesheet_unsaved_changes_warning'] = 'Algumas alterações podem não ter sido salvas. Deseja continuar mesmo assim?';
$lang['timesheet_pending_changes'] = 'Alterações Pendentes';
$lang['timesheet_unsaved_changes_exit_warning'] = 'Você tem alterações não salvas. Tem certeza que deseja sair?';

// Controller messages
$lang['timesheet_no_task_selected_save'] = 'Nenhuma tarefa selecionada para salvar';
$lang['timesheet_cannot_edit_pending_approved'] = 'Não é possível editar uma tarefa que está pendente ou já foi aprovada.';
$lang['timesheet_save_entry_failed'] = 'Falha ao salvar a entrada.';
$lang['timesheet_submit_error'] = 'Erro ao enviar timesheet';
$lang['timesheet_invalid_parameters'] = 'Parâmetros inválidos';
$lang['timesheet_rejection_reason_required'] = 'Motivo da rejeição é obrigatório';
$lang['timesheet_approval_processing_error'] = 'Erro ao processar aprovação';
$lang['timesheet_approval_cancelled_success'] = 'Aprovação cancelada com sucesso. Timesheet voltou para rascunho.';
$lang['timesheet_cancel_approval_error'] = 'Erro ao cancelar aprovação';

// Permissions
$lang['timesheet_permission_view'] = 'Visualizar Timesheet (criar próprios lançamentos)';
$lang['timesheet_permission_approve'] = 'Aprovar Timesheet (acessar telas de aprovação)';

// Weekly Timesheet Specific Translations
$lang['timesheet_weekly_total'] = 'Total da Semana';
$lang['timesheet_week_total_hours'] = 'Total de Horas da Semana';
$lang['timesheet_approval_request'] = 'Solicitação de Aprovação';
$lang['timesheet_approval_pending'] = 'Aprovação Pendente';
$lang['timesheet_submission_cancelled'] = 'Submissão cancelada com sucesso';
$lang['timesheet_cannot_cancel_submission'] = 'Não é possível cancelar a submissão';
$lang['timesheet_weekly_approvals'] = 'Aprovações Semanais';
$lang['timesheet_quick_approvals'] = 'Aprovações Rápidas';

// Batch Selection
$lang['timesheet_batch_selection'] = 'Seleção em Lote';
$lang['timesheet_select_all_tasks'] = 'Selecionar Todas as Tarefas Pendentes';
$lang['timesheet_tasks_selected'] = 'tarefas selecionadas';
$lang['timesheet_approve_selected'] = 'Aprovar Selecionadas';
$lang['timesheet_reject_selected'] = 'Rejeitar Selecionadas';
$lang['timesheet_batch_approval'] = 'Aprovação em Lote';
$lang['timesheet_batch_rejection'] = 'Rejeição em Lote';
$lang['timesheet_confirm_approve_selected'] = 'Tem certeza que deseja aprovar %d tarefas selecionadas?';
$lang['timesheet_confirm_reject_selected'] = 'Informe o motivo para rejeitar %d tarefas selecionadas:';

// User specific actions
$lang['timesheet_approve_user_selected'] = 'Aprovar Selecionadas do Usuário';
$lang['timesheet_reject_user_selected'] = 'Rejeitar Selecionadas do Usuário';
$lang['timesheet_confirm_approve_user_tasks'] = 'Tem certeza que deseja aprovar %d tarefas selecionadas deste usuário?';
$lang['timesheet_confirm_reject_user_tasks'] = 'Informe o motivo para rejeitar %d tarefas selecionadas deste usuário:';

// Preview and entries
$lang['timesheet_no_entries_found'] = 'Nenhuma entrada encontrada para os projetos que você gerencia.';
$lang['timesheet_loading_preview'] = 'Carregando preview do timesheet...';
$lang['timesheet_cancel_task_approval'] = 'Cancelar Aprovação da Tarefa';
$lang['timesheet_confirm_cancel_task'] = 'Tem certeza que deseja cancelar a aprovação desta tarefa específica? As horas serão removidas do quadro de horas e a tarefa voltará para o status pendente.';
$lang['timesheet_yes_cancel'] = 'Sim, Cancelar';
$lang['timesheet_task_approval_cancelled'] = 'Aprovação da tarefa cancelada com sucesso.';
$lang['timesheet_error_cancel_task'] = 'Erro ao cancelar a aprovação da tarefa.';

// General
$lang['timesheet_project_task'] = 'Projeto/Tarefa';
$lang['timesheet_actions'] = 'Ações';
$lang['timesheet_no_pending_approvals'] = 'Nenhuma aprovação pendente para a semana selecionada.';
$lang['timesheet_select_user_tasks'] = 'Selecionar todas as tarefas deste usuário';

// Confirmation messages
$lang['timesheet_confirm_submit'] = 'Tem certeza que deseja enviar este timesheet para aprovação? Esta ação não pode ser desfeita.';
$lang['timesheet_confirm_cancel_submission'] = 'Tem certeza que deseja cancelar o envio? O timesheet voltará ao status de rascunho.';

// Status messages
$lang['timesheet_pending_message'] = 'Suas horas foram enviadas e estão aguardando aprovação do gerente.';
$lang['timesheet_approved_message'] = 'Suas horas foram aprovadas pelo gerente.';
$lang['timesheet_rejected_message'] = 'Suas horas foram rejeitadas. Entre em contato com seu gerente para mais detalhes.';

// Common UI elements
$lang['submitted_at'] = 'Enviado em';
$lang['staff'] = 'Funcionário';
$lang['options'] = 'Opções';
$lang['view'] = 'Ver';
$lang['back'] = 'Voltar';
$lang['close'] = 'Fechar';
$lang['add'] = 'Adicionar';
$lang['project'] = 'Projeto';
$lang['task'] = 'Tarefa';

// Status Messages
$lang['timesheet_status_pending_message'] = 'Este timesheet foi enviado e está aguardando aprovação.';
$lang['timesheet_status_approved_message'] = 'Este timesheet foi aprovado.';
$lang['timesheet_status_rejected_message'] = 'Este timesheet foi rejeitado e pode ser editado novamente.';

// Rejection
$lang['timesheet_rejection_reason'] = 'Motivo da rejeição';

// Additional missing translations
$lang['timesheet_week_navigation'] = 'Navegação Semanal';
$lang['timesheet_previous_week'] = 'Semana Anterior';
$lang['timesheet_next_week'] = 'Próxima Semana';
$lang['timesheet_current_week'] = 'Semana Atual';
$lang['timesheet_weekly_approvals'] = 'Aprovações Semanais';
$lang['timesheet_quick_approvals'] = 'Aprovações Rápidas';
$lang['timesheet_select_all'] = 'Selecionar Todas';
$lang['timesheet_deselect_all'] = 'Desmarcar Todas';
$lang['timesheet_selected_tasks'] = 'tarefas selecionadas';
$lang['timesheet_approve_selected'] = 'Aprovar Selecionadas';
$lang['timesheet_reject_selected'] = 'Rejeitar Selecionadas';
$lang['timesheet_no_tasks_selected'] = 'Nenhuma tarefa selecionada';
$lang['timesheet_confirm_bulk_approval'] = 'Confirmar aprovação em lote';
$lang['timesheet_confirm_bulk_rejection'] = 'Confirmar rejeição em lote';
$lang['timesheet_bulk_approval_success'] = 'Tarefas aprovadas com sucesso';
$lang['timesheet_bulk_rejection_success'] = 'Tarefas rejeitadas com sucesso';
$lang['timesheet_processing'] = 'Processando...';
$lang['timesheet_saving'] = 'Salvando...';
$lang['timesheet_saved'] = 'Salvo';
$lang['timesheet_save_error'] = 'Erro ao salvar';
$lang['timesheet_auto_save'] = 'Salvamento automático';
$lang['timesheet_force_save'] = 'Salvamento forçado';
$lang['timesheet_backup_save'] = 'Salvamento de backup';
$lang['timesheet_unsaved_changes'] = 'Há alterações não salvas. Deseja continuar?';
$lang['timesheet_pending_operations'] = 'Há operações pendentes. Aguarde...';
$lang['timesheet_debug_mode'] = 'Modo Debug';
$lang['timesheet_test_connection'] = 'Teste de Conexão';
$lang['timesheet_database_ok'] = 'Banco de dados OK';
$lang['timesheet_permissions_ok'] = 'Permissões OK';
$lang['timesheet_module_status'] = 'Status do Módulo';
$lang['timesheet_version_check'] = 'Verificação de Versão';
$lang['timesheet_table_exists'] = 'Tabela existe';
$lang['timesheet_table_missing'] = 'Tabela não encontrada';
$lang['timesheet_installation_log'] = 'Log de Instalação';
$lang['timesheet_activation_debug'] = 'Debug de Ativação';
$lang['timesheet_sync_bidirectional'] = 'Sincronização Bidirecional';
$lang['timesheet_timer_reference'] = 'Referência de Timer';
$lang['timesheet_unidirectional_mode'] = 'Modo Unidirecional';
$lang['timesheet_readonly_board'] = 'Quadro Somente Leitura';