$(document).ready(function() {

    var currentApprovalId = null;
    var selectedTasks = []; // Array para armazenar IDs das tarefas selecionadas

    // Debug detalhado: verificar se os dados estão carregados
    console.log('[Weekly JS Debug] Manage Weekly carregado');
    console.log('[Weekly JS Debug] Dados recebidos:', manage_weekly_data);

    if (typeof manage_weekly_data === 'undefined') {
        console.error('[Weekly JS Debug] ERRO: manage_weekly_data não está definido!');
        alert('ERRO: Dados da página não foram carregados corretamente. Verifique os logs do servidor.');
        return;
    }

    if (!manage_weekly_data.weekly_approvals) {
        console.error('[Weekly JS Debug] ERRO: weekly_approvals não existe nos dados!');
        console.log('[Weekly JS Debug] Propriedades disponíveis:', Object.keys(manage_weekly_data));
        return;
    }

    console.log('[Weekly JS Debug] Aprovações semanais encontradas:', manage_weekly_data.weekly_approvals.length);

    // Debug de cada aprovação
    if (manage_weekly_data.weekly_approvals.length > 0) {
        manage_weekly_data.weekly_approvals.forEach(function(approval, index) {
            console.log('[Weekly JS Debug] Aprovação ' + (index + 1) + ':', approval);
            console.log('[Weekly JS Debug] - Staff ID:', approval.staff_id);
            console.log('[Weekly JS Debug] - Nome:', approval.firstname + ' ' + approval.lastname);
            console.log('[Weekly JS Debug] - Status:', approval.status);
            console.log('[Weekly JS Debug] - Total tarefas:', approval.total_tasks);

            loadTotalHours(approval.id, approval.staff_id, approval.week_start_date);
            loadTimesheetPreview(approval.id, approval.staff_id, approval.week_start_date);
        });
    } else {
        console.log('[Weekly JS Debug] Nenhuma aprovação encontrada para esta semana');
    }

    // Function to load total hours for a specific approval
    function loadTotalHours(approvalId, staffId, weekStartDate) {
        console.log('[Weekly JS Debug] Carregando total de horas para:', {
            approvalId: approvalId,
            staffId: staffId,
            weekStartDate: weekStartDate
        });

        var $totalDisplay = $('.total-hours-display[data-approval-id="' + approvalId + '"]');

        if ($totalDisplay.length === 0) {
            console.warn('[Weekly JS Debug] Elemento total-hours-display não encontrado para approval:', approvalId);
            return;
        }

        $.get(manage_weekly_data.admin_url + 'timesheet/get_week_total', {
            staff_id: staffId,
            week_start_date: weekStartDate
        })
        .done(function(response) {
            console.log('[Weekly JS Debug] Resposta total de horas:', response);
            if (response.success) {
                $totalDisplay.html('<strong>' + parseFloat(response.total_hours).toFixed(1) + 'h</strong>');
            } else {
                $totalDisplay.html('<span class="text-danger">Erro</span>');
            }
        })
        .fail(function(xhr, status, error) {
            console.error('[Weekly JS Debug] Erro ao carregar total de horas:', error);
            $totalDisplay.html('<span class="text-danger">Erro</span>');
        });
    }

    // Function to load timesheet preview
    function loadTimesheetPreview(approvalId, staffId, weekStartDate) {
        console.log('[Weekly JS Debug] Carregando preview para:', {
            approvalId: approvalId,
            staffId: staffId,
            weekStartDate: weekStartDate
        });

        var $previewContainer = $('#preview-' + approvalId);

        if ($previewContainer.length === 0) {
            console.warn('[Weekly JS Debug] Container de preview não encontrado para approval:', approvalId);
            return;
        }

        $.get(manage_weekly_data.admin_url + 'timesheet/get_timesheet_preview', {
            staff_id: staffId,
            week_start_date: weekStartDate
        })
        .done(function(response) {
            console.log('[Weekly JS Debug] Resposta preview:', response);
            if (response.success) {
                $previewContainer.html(response.html);
                // Carregar detalhes das tarefas automaticamente após carregar o preview
                loadTaskDetails(approvalId, staffId, weekStartDate);
            } else {
                $previewContainer.html('<div class="text-center text-danger">Erro ao carregar preview</div>');
            }
        })
        .fail(function(xhr, status, error) {
            console.error('[Weekly JS Debug] Erro ao carregar preview:', error);
            $previewContainer.html('<div class="text-center text-danger">Erro ao carregar dados</div>');
        });
    }

    // Approve button click
    $(document).on('click', 'button.approve-btn, a.approve-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var approvalId = $(this).data('approval-id');
        console.log('[Weekly JS Debug] Clicou em aprovar semanal, ID:', approvalId);

        TimesheetModals.confirm({
            title: 'Aprovar Timesheet',
            message: 'Tem certeza que deseja aprovar este timesheet? Esta ação não pode ser desfeita.',
            icon: 'fa-check-circle',
            confirmText: 'Aprovar',
            cancelText: 'Cancelar',
            confirmClass: 'timesheet-modal-btn-success'
        }).then(function(confirmed) {
            console.log('[Weekly JS Debug] Resultado da confirmação semanal:', confirmed);
            if (confirmed) {
                approveRejectTimesheet(approvalId, 'approved');
            }
        });
    });

    // Reject button click
    $(document).on('click', 'button.reject-btn, a.reject-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var approvalId = $(this).data('approval-id');
        console.log('Clicou em rejeitar semanal, ID:', approvalId);

        TimesheetModals.prompt({
            title: 'Rejeitar Timesheet',
            message: 'Por favor, informe o motivo da rejeição:',
            placeholder: 'Digite o motivo da rejeição...',
            icon: 'fa-times-circle',
            confirmText: 'Rejeitar',
            cancelText: 'Cancelar',
            confirmClass: 'timesheet-modal-btn-danger',
            required: true
        }).then(function(reason) {
            console.log('Motivo da rejeição semanal:', reason);
            if (reason) {
                approveRejectTimesheet(approvalId, 'rejected', reason);
            }
        });
    });

    // Cancel approval button click
    $(document).on('click', '.cancel-approval-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var approvalId = $(this).data('approval-id');
        console.log('Clicou em cancelar aprovação, ID:', approvalId);

        if (!approvalId) {
            console.error('Approval ID não encontrado no botão');
            return;
        }

        // Usar confirm simples se TimesheetModals não estiver disponível
        if (typeof TimesheetModals !== 'undefined') {
            TimesheetModals.confirm({
                title: 'Cancelar Aprovação',
                message: 'Tem certeza que deseja cancelar esta aprovação? O timesheet voltará ao status de rascunho e os timers do quadro de horas serão removidos.',
                icon: 'fa-exclamation-triangle',
                confirmText: 'Sim, Cancelar',
                cancelText: 'Não',
                confirmClass: 'timesheet-modal-btn-warning'
            }).then(function(confirmed) {
                console.log('Resultado da confirmação de cancelamento:', confirmed);
                if (confirmed) {
                    cancelApproval(approvalId);
                }
            });
        } else {
            // Fallback para confirm nativo
            if (confirm('Tem certeza que deseja cancelar esta aprovação? O timesheet voltará ao status de rascunho e os timers do quadro de horas serão removidos.')) {
                cancelApproval(approvalId);
            }
        }
    });

    function loadTotalHours(approvalId, staffId, weekStartDate) {
        $.ajax({
            url: manage_weekly_data.admin_url + 'timesheet/get_week_total',
            type: 'GET',
            data: {
                staff_id: staffId,
                week_start_date: weekStartDate
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var hours = parseFloat(response.total_hours) || 0;
                    var formatted = hours > 0 ? hours.toFixed(2) + 'h' : '0h';
                    $('.total-hours-display[data-approval-id="' + approvalId + '"]').html(formatted);
                } else {
                    $('.total-hours-display[data-approval-id="' + approvalId + '"]').html('Erro');
                }
            },
            error: function() {
                $('.total-hours-display[data-approval-id="' + approvalId + '"]').html('Erro');
            }
        });
    }

    function loadTimesheetPreview(approvalId, staffId, weekStartDate) {
        $.ajax({
            url: manage_weekly_data.admin_url + 'timesheet/get_timesheet_preview',
            type: 'GET',
            data: {
                staff_id: staffId,
                week_start_date: weekStartDate
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.html) {
                    $('#preview-' + approvalId).html(response.html);
                } else {
                    $('#preview-' + approvalId).html('<div class="text-center text-muted">Nenhuma entrada encontrada</div>');
                }
            },
            error: function() {
                $('#preview-' + approvalId).html('<div class="text-center text-muted text-danger">Erro ao carregar preview</div>');
            }
        });
    }

    function approveRejectTimesheet(approvalId, action, reason) {
        var $button = $('button[data-approval-id="' + approvalId + '"]');
        var originalText = $button.html();

        // Mostrar loading
        $button.html('<i class="fa fa-spinner fa-spin"></i> Processando...').prop('disabled', true);

        var postData = {
            approval_id: approvalId,
            action: action
        };

        if (reason) {
            postData.reason = reason;
        }

        console.log('Dados enviados via AJAX (semanal):', postData);

        $.ajax({
            url: manage_weekly_data.admin_url + 'timesheet/approve_reject',
            type: 'POST',
            data: postData,
            dataType: 'json',
            success: function(response) {
                console.log('Resposta recebida (semanal):', response);

                if (response.success) {
                    TimesheetModals.alert({
                        title: 'Sucesso',
                        message: response.message,
                        icon: 'fa-check-circle',
                        type: 'success'
                    }).then(function() {
                        // Remover o painel da aprovação
                        $('.approval-panel[data-approval-id="' + approvalId + '"]').fadeOut(500, function() {
                            $(this).remove();

                            // Verificar se ainda há aprovações pendentes
                            if ($('.approval-panel').length === 0) {
                                location.reload();
                            }
                        });
                    });
                } else {
                    TimesheetModals.alert({
                        title: 'Erro',
                        message: response.message || 'Erro ao processar aprovação',
                        icon: 'fa-exclamation-triangle',
                        type: 'error'
                    });

                    // Restaurar botão
                    $button.html(originalText).prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro AJAX (semanal):', xhr.responseText);

                TimesheetModals.alert({
                    title: 'Erro de Comunicação',
                    message: 'Erro ao comunicar com o servidor. Tente novamente.',
                    icon: 'fa-exclamation-triangle',
                    type: 'error'
                });

                // Restaurar botão
                $button.html(originalText).prop('disabled', false);
            }
        });
    }

    function cancelApproval(approvalId) {
        var $button = $('button[data-approval-id="' + approvalId + '"]');
        var originalText = $button.html();

        // Mostrar loading
        $button.html('<i class="fa fa-spinner fa-spin"></i> Cancelando...').prop('disabled', true);

        var postData = {
            approval_id: approvalId
        };

        console.log('Cancelando aprovação via AJAX:', postData);

        $.ajax({
            url: manage_weekly_data.admin_url + 'timesheet/cancel_approval',
            type: 'POST',
            data: postData,
            dataType: 'json',
            success: function(response) {
                console.log('Resposta do cancelamento:', response);

                if (response.success) {
                    TimesheetModals.alert({
                        title: 'Sucesso',
                        message: response.message,
                        icon: 'fa-check-circle',
                        type: 'success'
                    }).then(function() {
                        // Recarregar a página para atualizar o status
                        location.reload();
                    });
                } else {
                    TimesheetModals.alert({
                        title: 'Erro',
                        message: response.message || 'Erro ao cancelar aprovação',
                        icon: 'fa-exclamation-triangle',
                        type: 'error'
                    });

                    // Restaurar botão
                    $button.html(originalText).prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro AJAX no cancelamento:', xhr.responseText);

                TimesheetModals.alert({
                    title: 'Erro de Comunicação',
                    message: 'Erro ao comunicar com o servidor. Tente novamente.',
                    icon: 'fa-exclamation-triangle',
                    type: 'error'
                });

                // Restaurar botão
                $button.html(originalText).prop('disabled', false);
            }
        });
    }

    // ===== FUNCIONALIDADES DE SELEÇÃO EM LOTE =====

    // Handler para checkbox "Selecionar Todos" (global)
    $(document).on('change', '#select-all-tasks', function() {
        var isChecked = $(this).is(':checked');
        $('.task-checkbox:enabled').prop('checked', isChecked).trigger('change');
    });

    // Handler para checkbox "Selecionar Todas do Usuário" (cabeçalho da tabela)
    $(document).on('change', '.select-user-tasks-header', function() {
        var isChecked = $(this).is(':checked');
        var userId = $(this).data('user-id');
        $('.task-checkbox[data-user-id="' + userId + '"]:enabled').prop('checked', isChecked).trigger('change');
    });

    // Handler para checkbox "Selecionar Todas do Usuário" (controle acima da tabela)
    $(document).on('change', '.select-user-tasks', function() {
        var isChecked = $(this).is(':checked');
        var userId = $(this).data('user-id');
        $('.task-checkbox[data-user-id="' + userId + '"]:enabled').prop('checked', isChecked).trigger('change');
        $('.select-user-tasks-header[data-user-id="' + userId + '"]').prop('checked', isChecked);
    });

    // Handler para checkboxes individuais de tarefas
    $(document).on('change', '.task-checkbox', function() {
        updateSelectedTasks();
        updateBatchControls();
        updateUserBatchControls();
    });

    // Atualizar array de tarefas selecionadas
    function updateSelectedTasks() {
        selectedTasks = [];
        $('.task-checkbox:checked').each(function() {
            selectedTasks.push($(this).val());
        });
        console.log('[Batch Selection] Tarefas selecionadas:', selectedTasks);
    }

    // Atualizar controles de ação em lote (global)
    function updateBatchControls() {
        var selectedCount = selectedTasks.length;
        var totalPending = $('.task-checkbox[data-status="pending"]:enabled').length;

        // Atualizar contador
        $('.selection-counter').html('<strong>' + selectedCount + '</strong> tarefas selecionadas');

        // Habilitar/desabilitar botões de ação
        $('.batch-approve-btn, .batch-reject-btn').prop('disabled', selectedCount === 0);

        // Atualizar checkbox "Selecionar Todos"
        var allChecked = (selectedCount > 0 && selectedCount === totalPending);
        var someChecked = (selectedCount > 0 && selectedCount < totalPending);

        $('#select-all-tasks').prop('checked', allChecked);
        $('#select-all-tasks').prop('indeterminate', someChecked);
    }

    // Atualizar controles de ação por usuário
    function updateUserBatchControls() {
        $('.select-user-tasks').each(function() {
            var userId = $(this).data('user-id');
            var userTasks = $('.task-checkbox[data-user-id="' + userId + '"]:enabled');
            var userSelectedTasks = $('.task-checkbox[data-user-id="' + userId + '"]:checked');

            var selectedCount = userSelectedTasks.length;
            var totalCount = userTasks.length;

            // Atualizar contador por usuário
            $('.user-selection-counter[data-user-id="' + userId + '"]').html('<strong>' + selectedCount + '</strong> tarefas selecionadas');

            // Habilitar/desabilitar botões por usuário
            $('.user-batch-approve-btn[data-user-id="' + userId + '"], .user-batch-reject-btn[data-user-id="' + userId + '"]').prop('disabled', selectedCount === 0);

            // Atualizar estado dos checkboxes "Selecionar Todas do Usuário"
            var allChecked = (selectedCount > 0 && selectedCount === totalCount);
            var someChecked = (selectedCount > 0 && selectedCount < totalCount);

            // Sincronizar ambos os checkboxes (controle e cabeçalho da tabela)
            $(this).prop('checked', allChecked);
            $(this).prop('indeterminate', someChecked);
            $('.select-user-tasks-header[data-user-id="' + userId + '"]').prop('checked', allChecked);
            $('.select-user-tasks-header[data-user-id="' + userId + '"]').prop('indeterminate', someChecked);
        });
    }

    // Handler para aprovação em lote (global)
    $(document).on('click', '.batch-approve-btn', function() {
        if (selectedTasks.length === 0) {
            alert('Nenhuma tarefa selecionada');
            return;
        }

        TimesheetModals.confirm({
            title: 'Aprovação em Lote',
            message: 'Tem certeza que deseja aprovar ' + selectedTasks.length + ' tarefas selecionadas?',
            icon: 'fa-check-circle',
            confirmText: 'Aprovar Todas',
            cancelText: 'Cancelar',
            confirmClass: 'timesheet-modal-btn-success'
        }).then(function(confirmed) {
            if (confirmed) {
                processBatchAction('approved');
            }
        });
    });

    // Handler para rejeição em lote (global)
    $(document).on('click', '.batch-reject-btn', function() {
        if (selectedTasks.length === 0) {
            alert('Nenhuma tarefa selecionada');
            return;
        }

        TimesheetModals.prompt({
            title: 'Rejeição em Lote',
            message: 'Informe o motivo para rejeitar ' + selectedTasks.length + ' tarefas selecionadas:',
            placeholder: 'Digite o motivo da rejeição...',
            icon: 'fa-times-circle',
            confirmText: 'Rejeitar Todas',
            cancelText: 'Cancelar',
            confirmClass: 'timesheet-modal-btn-danger',
            required: true
        }).then(function(reason) {
            if (reason) {
                processBatchAction('rejected', reason);
            }
        });
    });

    // Handler para aprovação em lote por usuário
    $(document).on('click', '.user-batch-approve-btn', function() {
        var userId = $(this).data('user-id');
        var userSelectedTasks = [];

        $('.task-checkbox[data-user-id="' + userId + '"]:checked').each(function() {
            userSelectedTasks.push($(this).val());
        });

        if (userSelectedTasks.length === 0) {
            alert('Nenhuma tarefa selecionada para este usuário');
            return;
        }

        TimesheetModals.confirm({
            title: 'Aprovação em Lote - Usuário',
            message: 'Tem certeza que deseja aprovar ' + userSelectedTasks.length + ' tarefas selecionadas deste usuário?',
            icon: 'fa-check-circle',
            confirmText: 'Aprovar Selecionadas',
            cancelText: 'Cancelar',
            confirmClass: 'timesheet-modal-btn-success'
        }).then(function(confirmed) {
            if (confirmed) {
                processBatchAction('approved', null, userSelectedTasks);
            }
        });
    });

    // Handler para rejeição em lote por usuário
    $(document).on('click', '.user-batch-reject-btn', function() {
        var userId = $(this).data('user-id');
        var userSelectedTasks = [];

        $('.task-checkbox[data-user-id="' + userId + '"]:checked').each(function() {
            userSelectedTasks.push($(this).val());
        });

        if (userSelectedTasks.length === 0) {
            alert('Nenhuma tarefa selecionada para este usuário');
            return;
        }

        TimesheetModals.prompt({
            title: 'Rejeição em Lote - Usuário',
            message: 'Informe o motivo para rejeitar ' + userSelectedTasks.length + ' tarefas selecionadas deste usuário:',
            placeholder: 'Digite o motivo da rejeição...',
            icon: 'fa-times-circle',
            confirmText: 'Rejeitar Selecionadas',
            cancelText: 'Cancelar',
            confirmClass: 'timesheet-modal-btn-danger',
            required: true
        }).then(function(reason) {
            if (reason) {
                processBatchAction('rejected', reason, userSelectedTasks);
            }
        });
    });

    // Processar ação em lote
    function processBatchAction(action, reason, specificTasks) {
        var tasksToProcess = specificTasks || selectedTasks;
        var buttonSelector = specificTasks ? '.user-batch-' + action.replace('ed', '') + '-btn' : '.batch-' + action.replace('ed', '') + '-btn';
        var $button = $(buttonSelector);

        // Se há tarefas específicas, encontrar o botão correto
        if (specificTasks && specificTasks.length > 0) {
            // Encontrar o botão do usuário correto baseado na primeira tarefa
            var firstTaskCheckbox = $('.task-checkbox[value="' + specificTasks[0] + '"]');
            var userId = firstTaskCheckbox.data('user-id');
            $button = $('.user-batch-' + action.replace('ed', '') + '-btn[data-user-id="' + userId + '"]');
        }

        var originalText = $button.html();

        // Mostrar loading
        $button.html('<i class="fa fa-spinner fa-spin"></i> Processando...').prop('disabled', true);

        var postData = {
            task_ids: tasksToProcess,
            action: action
        };

        if (reason) {
            postData.reason = reason;
        }

        console.log('[Batch Action] Enviando dados:', postData);

        $.ajax({
            url: manage_weekly_data.admin_url + 'timesheet/batch_approve_reject',
            type: 'POST',
            data: postData,
            dataType: 'json',
            success: function(response) {
                console.log('[Batch Action] Resposta recebida:', response);

                if (response.success) {
                    TimesheetModals.alert({
                        title: 'Sucesso',
                        message: response.message,
                        icon: 'fa-check-circle',
                        type: 'success'
                    }).then(function() {
                        // Recarregar a página para atualizar o status
                        location.reload();
                    });
                } else {
                    TimesheetModals.alert({
                        title: 'Erro',
                        message: response.message || 'Erro ao processar ação em lote',
                        icon: 'fa-exclamation-triangle',
                        type: 'error'
                    });

                    // Restaurar botão
                    $button.html(originalText).prop('disabled', tasksToProcess.length === 0);
                }
            },
            error: function(xhr, status, error) {
                console.error('[Batch Action] Erro AJAX:', xhr.responseText);

                TimesheetModals.alert({
                    title: 'Erro de Comunicação',
                    message: 'Erro ao comunicar com o servidor. Tente novamente.',
                    icon: 'fa-exclamation-triangle',
                    type: 'error'
                });

                // Restaurar botão
                $button.html(originalText).prop('disabled', tasksToProcess.length === 0);
            }
        });
    }

    // Carregar tarefas detalhadas quando expandir aprovação
    function loadTaskDetails(approvalId, staffId, weekStartDate) {
        console.log('[Task Details] Carregando detalhes das tarefas para approval:', approvalId);

        // Aguardar um pouco para garantir que a tabela foi renderizada
        setTimeout(function() {
            $.get(manage_weekly_data.admin_url + 'timesheet/get_week_task_approvals', {
                staff_id: staffId,
                week_start_date: weekStartDate
            })
            .done(function(response) {
                console.log('[Task Details] Resposta recebida:', response);
                if (response.success && response.tasks) {
                    renderTaskCheckboxes(approvalId, response.tasks);
                } else {
                    console.warn('[Task Details] Nenhuma tarefa encontrada ou erro na resposta');
                }
            })
            .fail(function(xhr, status, error) {
                console.error('[Task Details] Erro ao carregar:', error);
            });
        }, 500); // Aguardar 500ms para garantir que a tabela foi renderizada
    }

    // Renderizar checkboxes das tarefas diretamente na tabela
    function renderTaskCheckboxes(approvalId, tasks) {
        console.log('[Task Checkboxes] Iniciando renderização para approval:', approvalId, 'com', tasks.length, 'tarefas');

        var $previewContainer = $('#preview-' + approvalId);
        var $table = $previewContainer.find('table');

        if ($table.length === 0) {
            console.warn('[Task Checkboxes] Tabela não encontrada para approval:', approvalId);
            return;
        }

        // Verificar se já existem checkboxes para evitar duplicação
        if ($table.find('.task-checkbox').length > 0) {
            console.log('[Task Checkboxes] Checkboxes já existem, pulando renderização');
            return;
        }

        // Adicionar coluna de checkbox no cabeçalho (substituir a primeira célula)
        var $headerRow = $table.find('thead tr');
        if ($headerRow.length > 0) {
            var $firstHeaderCell = $headerRow.find('th:first');
            if ($firstHeaderCell.length > 0) {
                $firstHeaderCell.replaceWith('<th width="40" class="text-center"><input type="checkbox" class="select-user-tasks-header" data-user-id="' + approvalId + '" title="Selecionar todas as tarefas deste usuário"></th>');
            }
        }

        // Obter linhas do tbody (excluir linha de total se existir)
        var $bodyRows = $table.find('tbody tr');
        var $dataRows = $bodyRows.filter(function() {
            return $(this).find('td:contains("Total:")').length === 0;
        });

        console.log('[Task Checkboxes] Encontradas', $dataRows.length, 'linhas de dados');

        // Substituir primeira célula das linhas de dados com checkboxes
        $dataRows.each(function(index) {
            var $row = $(this);
            var $firstCell = $row.find('td:first');

            if (index < tasks.length) {
                var task = tasks[index];
                var isDisabled = task.status !== 'pending' ? 'disabled' : '';
                var checkboxHtml = '<td class="text-center"><input type="checkbox" class="task-checkbox" value="' + task.id + '" data-status="' + task.status + '" data-user-id="' + approvalId + '" ' + isDisabled + '></td>';
                $firstCell.replaceWith(checkboxHtml);
                console.log('[Task Checkboxes] Substituído checkbox para tarefa:', task.id, 'status:', task.status);
            } else {
                $firstCell.replaceWith('<td class="text-center">-</td>');
            }
        });

        // Substituir primeira célula da linha de total (se existir)
        var $totalRow = $bodyRows.filter(function() {
            return $(this).find('td:contains("Total:")').length > 0;
        });

        if ($totalRow.length > 0) {
            var $totalFirstCell = $totalRow.find('td:first');
            $totalFirstCell.replaceWith('<td class="text-center"><strong>-</strong></td>');
        }

        console.log('[Task Checkboxes] Renderização concluída');

        // Atualizar controles após inserir checkboxes
        setTimeout(function() {
            updateUserBatchControls();
            updateBatchControls();
        }, 100);
    }

});