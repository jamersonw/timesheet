$(document).ready(function () {
    var selectedTasks = []; // Array para armazenar IDs das tarefas selecionadas

    // Debug inicial
    console.log("[Weekly JS Debug] Manage Weekly carregado e pronto.");
    if (typeof manage_weekly_data === "undefined" || !manage_weekly_data.weekly_approvals) {
        console.error("[Weekly JS Debug] ERRO: Dados da página (manage_weekly_data) não foram carregados corretamente.");
        alert("ERRO: Dados da página não foram carregados. Verifique os logs do servidor.");
        return;
    }

    // Carrega os dados iniciais para cada aprovação visível
    manage_weekly_data.weekly_approvals.forEach(function (approval) {
        loadTotalHours(approval.id, approval.staff_id, approval.week_start_date);
        loadTimesheetPreview(approval.id, approval.staff_id, approval.week_start_date);
    });

    // Função para carregar o total de horas
    function loadTotalHours(approvalId, staffId, weekStartDate) {
        var $totalDisplay = $('.total-hours-display[data-approval-id="' + approvalId + '"]');
        $.getJSON(manage_weekly_data.admin_url + "timesheet/get_week_total", {
            staff_id: staffId,
            week_start_date: weekStartDate
        }).done(function (response) {
            if (response && response.success) {
                $totalDisplay.html("<strong>" + parseFloat(response.total_hours).toFixed(2) + "h</strong>");
            } else {
                $totalDisplay.html('<span class="text-danger">Erro</span>');
            }
        }).fail(function () {
            $totalDisplay.html('<span class="text-danger">Erro</span>');
        });
    }

    // Função para carregar o preview do timesheet
    function loadTimesheetPreview(approvalId, staffId, weekStartDate) {
        var $previewContainer = $("#preview-" + approvalId);
        $.getJSON(manage_weekly_data.admin_url + "timesheet/get_timesheet_preview", {
            staff_id: staffId,
            week_start_date: weekStartDate
        }).done(function (response) {
            if (response && response.success && response.html) {
                $previewContainer.html(response.html);
                setTimeout(updateAllCheckboxesAndControls, 100);
            } else {
                $previewContainer.html('<div class="text-center text-danger">Erro ao carregar preview</div>');
            }
        }).fail(function () {
            $previewContainer.html('<div class="text-center text-danger">Erro ao carregar dados</div>');
        });
    }

    // ===================================================================
    // LÓGICA DE SELEÇÃO EM LOTE (VERSÃO FINAL)
    // ===================================================================

    // 1. Handler para o checkbox GLOBAL "Selecionar Todas as Tarefas"
    $(document).on("change", "#select-all-tasks", function () {
        var isChecked = $(this).is(":checked");
        $(".task-checkbox:enabled, .select-user-tasks-header").prop("checked", isChecked);
        $(".select-user-tasks-header").prop("indeterminate", false);
        updateAllCheckboxesAndControls();
    });

    // 2. Handler para o checkbox POR USUÁRIO
    $(document).on("change", ".select-user-tasks-header", function () {
        var isChecked = $(this).is(":checked");
        var $previewContainer = $(this).closest('[id^=preview-]');
        $previewContainer.find('.task-checkbox:enabled').prop("checked", isChecked);
        updateAllCheckboxesAndControls();
    });

    // 3. Handler para checkboxes INDIVIDUAIS de tarefas
    $(document).on("change", ".task-checkbox", function () {
        setTimeout(updateAllCheckboxesAndControls, 0);
    });

    /**
     * Função Centralizadora: Atualiza todos os checkboxes e botões da página.
     */
    function updateAllCheckboxesAndControls() {
        selectedTasks = [];
        $(".task-checkbox:checked").each(function () {
            selectedTasks.push($(this).val());
        });
        console.log("[Batch Selection] Tarefas selecionadas:", selectedTasks);

        var totalSelectedCount = selectedTasks.length;
        var totalCheckboxesAvailable = $('.task-checkbox:enabled').length;

        // --- LÓGICA GLOBAL ---
        $(".selection-counter").html("<strong>" + totalSelectedCount + "</strong> " + (manage_weekly_data.lang_tasks_selected || 'tarefas selecionadas'));
        $(".batch-approve-btn, .batch-reject-btn").prop("disabled", totalSelectedCount === 0);

        var globalCheckbox = $("#select-all-tasks");
        if (totalCheckboxesAvailable > 0 && totalSelectedCount === totalCheckboxesAvailable) {
            globalCheckbox.prop("checked", true);
            globalCheckbox.prop("indeterminate", false);
        } else if (totalSelectedCount > 0) {
            globalCheckbox.prop("checked", false);
            globalCheckbox.prop("indeterminate", true);
        } else {
            globalCheckbox.prop("checked", false);
            globalCheckbox.prop("indeterminate", false);
        }

        // --- LÓGICA POR USUÁRIO (CORRIGIDA) ---
        $(".approval-panel").each(function () {
            var $panel = $(this);
            var $userHeaderCheckbox = $panel.find('.select-user-tasks-header');

            var userSelectedCount = $panel.find('.task-checkbox:checked').length;
            var userTotalAvailable = $panel.find('.task-checkbox:enabled').length;

            // ** A CORREÇÃO PRINCIPAL ESTÁ AQUI **
            // Encontra os botões dentro do painel do usuário e os habilita/desabilita.
            $panel.find('.user-batch-approve-btn, .user-batch-reject-btn')
                .prop("disabled", userSelectedCount === 0);

            // Atualiza o estado do checkbox do cabeçalho da tabela
            if ($userHeaderCheckbox.length > 0) {
                if (userTotalAvailable > 0 && userSelectedCount === userTotalAvailable) {
                    $userHeaderCheckbox.prop("checked", true);
                    $userHeaderCheckbox.prop("indeterminate", false);
                } else if (userSelectedCount > 0) {
                    $userHeaderCheckbox.prop("checked", false);
                    $userHeaderCheckbox.prop("indeterminate", true);
                } else {
                    $userHeaderCheckbox.prop("checked", false);
                    $userHeaderCheckbox.prop("indeterminate", false);
                }
            }
        });
    }

    // Restante do código (Ações e Modais) permanece inalterado...
    // ===================================================================
    // AÇÕES E MODAIS (APROVAR/REJEITAR/CANCELAR)
    // ===================================================================
    // NOVO: Handler para cancelar aprovação de TAREFA INDIVIDUAL
    $(document).on("click", ".cancel-task-btn", function () {
        var approvalId = $(this).data("approval-id");
        
        TimesheetModals.confirm({
            title: manage_weekly_data.lang_cancel_task_approval || "Cancelar Aprovação da Tarefa",
            message: manage_weekly_data.lang_confirm_cancel_task || "Tem certeza que deseja cancelar a aprovação desta tarefa específica? As horas serão removidas do quadro de horas e a tarefa voltará para o status pendente.",
            confirmText: manage_weekly_data.lang_yes_cancel || "Sim, Cancelar",
            confirmClass: "timesheet-modal-btn-danger"
        }).then(function (confirmed) {
            if (confirmed) {
                $.post(manage_weekly_data.admin_url + "timesheet/cancel_task_approval", { approval_id: approvalId }, function(response) {
                    if (response.success) {
                        TimesheetModals.alert({ title: "Sucesso", message: response.message, type: "success" })
                            .then(function() { location.reload(); });
                    } else {
                        TimesheetModals.alert({ title: "Erro", message: response.message, type: "error" });
                    }
                }, 'json').fail(function() {
                    TimesheetModals.alert({ title: "Erro", message: "Erro de comunicação com o servidor.", type: "error" });
                });
            }
        });
    });
    
    $(document).on("click", "button.approve-btn, a.approve-btn", function(e) { e.preventDefault(); var t = $(this).data("approval-id"); TimesheetModals.confirm({ title: "Aprovar Timesheet", message: "Tem certeza que deseja aprovar este timesheet? Esta ação não pode ser desfeita.", confirmText: "Aprovar" }).then(function(e) { e && approveRejectTimesheet(t, "approved") }) }), $(document).on("click", "button.reject-btn, a.reject-btn", function(e) { e.preventDefault(); var t = $(this).data("approval-id"); TimesheetModals.prompt({ title: "Rejeitar Timesheet", message: "Por favor, informe o motivo da rejeição:", required: !0 }).then(function(e) { e && approveRejectTimesheet(t, "rejected", e) }) }), $(document).on("click", ".cancel-approval-btn", function(e) { e.preventDefault(); var t = $(this).data("approval-id"); TimesheetModals.confirm({ title: "Cancelar Aprovação", message: "Tem certeza que deseja cancelar esta aprovação? O timesheet voltará ao status de rascunho.", confirmText: "Sim, Cancelar" }).then(function(e) { e && cancelApproval(t) }) }), $(document).on("click", ".batch-approve-btn", function() { 0 !== selectedTasks.length && TimesheetModals.confirm({ title: "Aprovação em Lote", message: "Tem certeza que deseja aprovar " + selectedTasks.length + " tarefas selecionadas?", confirmText: "Aprovar Todas" }).then(function(e) { e && processBatchAction("approved", null, selectedTasks) }) }), $(document).on("click", ".batch-reject-btn", function() { 0 !== selectedTasks.length && TimesheetModals.prompt({ title: "Rejeição em Lote", message: "Informe o motivo para rejeitar " + selectedTasks.length + " tarefas selecionadas:", required: !0 }).then(function(e) { e && processBatchAction("rejected", e, selectedTasks) }) }), $(document).on("click", ".user-batch-approve-btn", function() { var e = $(this).data("user-id"), t = [];
        $("#preview-" + e + " .task-checkbox:checked").each(function() { t.push($(this).val()) }), 0 !== t.length && TimesheetModals.confirm({ title: "Aprovação em Lote - Usuário", message: "Tem certeza que deseja aprovar " + t.length + " tarefas selecionadas deste usuário?", confirmText: "Aprovar Selecionadas" }).then(function(e) { e && processBatchAction("approved", null, t) }) }), $(document).on("click", ".user-batch-reject-btn", function() { var e = $(this).data("user-id"), t = [];
        $("#preview-" + e + " .task-checkbox:checked").each(function() { t.push($(this).val()) }), 0 !== t.length && TimesheetModals.prompt({ title: "Rejeição em Lote - Usuário", message: "Informe o motivo para rejeitar " + t.length + " tarefas selecionadas deste usuário:", required: !0 }).then(function(e) { e && processBatchAction("rejected", e, t) }) });

    function approveRejectTimesheet(e, t, a) { var o = { approval_id: e, action: t, reason: a || null };
        $.post(manage_weekly_data.admin_url + "timesheet/approve_reject", o, function(e) { e.success ? TimesheetModals.alert({ title: "Sucesso", message: e.message, type: "success" }).then(function() { location.reload() }) : TimesheetModals.alert({ title: "Erro", message: e.message, type: "error" }) }, "json").fail(function() { TimesheetModals.alert({ title: "Erro", message: "Erro de comunicação com o servidor.", type: "error" }) }) }

    function cancelApproval(e) { $.post(manage_weekly_data.admin_url + "timesheet/cancel_approval", { approval_id: e }, function(e) { e.success ? TimesheetModals.alert({ title: "Sucesso", message: e.message, type: "success" }).then(function() { location.reload() }) : TimesheetModals.alert({ title: "Erro", message: e.message, type: "error" }) }, "json").fail(function() { TimesheetModals.alert({ title: "Erro", message: "Erro de comunicação com o servidor.", type: "error" }) }) }

    function processBatchAction(e, t, a) { var o = { task_ids: a, action: e, reason: t || null };
        $.post(manage_weekly_data.admin_url + "timesheet/batch_approve_reject", o, function(e) { e.success ? TimesheetModals.alert({ title: "Sucesso", message: e.message, type: "success" }).then(function() { location.reload() }) : TimesheetModals.alert({ title: "Erro", message: e.message, type: "error" }) }, "json").fail(function() { TimesheetModals.alert({ title: "Erro", message: "Erro de comunicação com o servidor.", type: "error" }) }) }

    updateAllCheckboxesAndControls();
});