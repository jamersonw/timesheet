$(document).ready(function() {
    // Funções globais para formatação de horas
    function formatHours(value) {
        if (value === null || value === undefined || value === '') return '0,00';
        var numericValue = parseFloat(value.toString().replace(',', '.'));
        if (isNaN(numericValue)) return '0,00';
        return numericValue.toFixed(2).replace('.', ',');
    }

    function parseHours(value) {
        if (value === null || value === undefined || value === '') return 0;
        var numericValue = parseFloat(value.toString().replace(',', '.'));
        return isNaN(numericValue) ? 0 : numericValue;
    }

    var $saveIndicator = $('#save-indicator');
    var saveTimeout;

    // Auto-save ao sair do campo
    $(document).on('blur', '.hours-input', function() {
        var $input = $(this);
        var value = $input.val().trim();

        // Sempre formatar o valor, incluindo 0
        var formattedValue = formatHours(value);
        $input.val(formattedValue);

        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(function() {
            saveEntry($input);
        }, 300);
    });

    // Limpar indicador e reformatar para edição
    $(document).on('focus', '.hours-input', function() {
        var $input = $(this);
        $saveIndicator.html('');
        if (parseHours($input.val()) > 0) {
            $input.val($input.val().replace('.', ','));
        } else {
            $input.val('');
        }
    });

    // ================== FUNÇÃO SAVEENTRY COM LOGS DETALHADOS ==================
    function saveEntry($input) {
        var $row = $input.closest('tr');
        var taskId = $row.data('task-id');
        var projectId = $row.data('project-id');

        // VALIDAÇÃO FRONT-END: Previne chamadas AJAX desnecessárias se a tarefa não estiver definida.
        if (!taskId || !projectId) {
            console.warn("⚠️ [SAVE-ENTRY] Salvamento abortado: task-id ou project-id não encontrado na linha da tabela (TR).", { 'task-id': taskId, 'project-id': projectId });
            return Promise.resolve({ success: true, message: 'Nenhuma tarefa selecionada para salvar' });
        }

        return new Promise(function(resolve, reject) {
            $saveIndicator.html('<i class="fa fa-spinner fa-spin"></i> Salvando...');

            var hours = parseHours($input.val());
            var data = {
                project_id: projectId,
                task_id: taskId,
                week_start: timesheet_data.week_start,
                day_of_week: $input.data('day'),
                hours: hours
            };
            data[csrfData.token_name] = csrfData.hash;

            console.groupCollapsed("🔵 [SAVE-ENTRY] Tentando salvar para o dia: " + $input.data('day'));
            console.log("➡️ Dados enviados via POST:", data);
            console.log("➡️ URL:", timesheet_data.admin_url + 'timesheet/save_entry');

            $.post(timesheet_data.admin_url + 'timesheet/save_entry', data)
            .done(function(response) {
                try {
                    response = typeof response === 'string' ? JSON.parse(response) : response;
                } catch (e) {
                    console.error("❌ Falha ao parsear a resposta do servidor. Resposta bruta:", response);
                    $saveIndicator.html('<i class="fa fa-times text-danger"></i> Erro de Servidor!');
                    reject({ responseText: response });
                    console.groupEnd();
                    return;
                }

                console.log("⬅️ Resposta do servidor recebida:", response);

                if (response.success) {
                    $saveIndicator.html('<i class="fa fa-check text-success"></i> Salvo');
                    resolve(response);
                } else {
                    $saveIndicator.html('<i class="fa fa-times text-danger"></i> Falha!');
                    TimesheetModals.notify('danger', response.message || 'Ocorreu um erro desconhecido ao salvar.');
                    reject(response);
                }
                setTimeout(function() { $saveIndicator.html(''); }, 2500);
                updateTotals();
                console.groupEnd();
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error("❌ Falha na requisição AJAX:");
                console.error("Status Code:", jqXHR.status);
                console.error("Status Text:", textStatus);
                console.error("Error Thrown:", errorThrown);
                console.error("Resposta Completa do Servidor:", jqXHR.responseText);

                $saveIndicator.html('<i class="fa fa-times text-danger"></i> Erro de conexão');
                setTimeout(function() { $saveIndicator.html(''); }, 2500);

                TimesheetModals.notify('danger', 'Erro de conexão ou erro interno no servidor. Verifique o console.');
                reject(jqXHR);
                console.groupEnd();
            });
        });
    }

    function saveAllEntries() {
        var promises = [];
        $('.hours-input').each(function() {
            promises.push(saveEntry($(this)));
        });
        return Promise.all(promises);
    }

    $('#submit-timesheet').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true); 

        // Mostrar indicador de salvamento
        $saveIndicator.html('<i class="fa fa-spinner fa-spin"></i> Salvando todas as entradas...');

        saveAllEntries().then(function() {
            // Aguardar um momento para garantir que o servidor processou todas as alterações
            setTimeout(function() {
                $saveIndicator.html('<i class="fa fa-check text-success"></i> Todas as entradas salvas');

                // Usar modal elegante ao invés de confirm()
                TimesheetModals.confirm({
                    title: 'Enviar para Aprovação',
                    message: timesheet_data.confirm_submit || 'Tem certeza que deseja enviar este timesheet para aprovação? Esta ação não pode ser desfeita.',
                    icon: 'fa-paper-plane',
                    confirmText: 'Enviar',
                    cancelText: 'Cancelar',
                    confirmClass: 'timesheet-modal-btn-success'
                }).then(function(confirmed) {
                    if (confirmed) {
                        $saveIndicator.html('<i class="fa fa-spinner fa-spin"></i> Enviando para aprovação...');

                        var data = {};
                        data.week_start = timesheet_data.week_start;
                        data[csrfData.token_name] = csrfData.hash;

                        $.post(timesheet_data.admin_url + 'timesheet/submit_week', data).done(function(response) {
                            try {
                                response = typeof response === 'string' ? JSON.parse(response) : response;
                            } catch (e) {
                                console.error("Erro ao parsear resposta da submissão:", response);
                                TimesheetModals.error('Erro de comunicação com o servidor');
                                $btn.prop('disabled', false);
                                $saveIndicator.html('');
                                return;
                            }

                            if (response.success) {
                                $saveIndicator.html('<i class="fa fa-check text-success"></i> Enviado com sucesso!');
                                TimesheetModals.notify('success', response.message);
                                setTimeout(function(){ location.reload(); }, 1500);
                            } else {
                                $saveIndicator.html('<i class="fa fa-times text-danger"></i> Erro na submissão');
                                TimesheetModals.notify('danger', response.message);
                                $btn.prop('disabled', false);
                                setTimeout(function() { $saveIndicator.html(''); }, 3000);
                            }
                        }).fail(function(jqXHR) {
                            console.error("Falha na requisição de submissão:", jqXHR.responseText);
                            $saveIndicator.html('<i class="fa fa-times text-danger"></i> Erro de conexão');
                            TimesheetModals.notify('danger', 'Erro de conexão ao enviar para aprovação');
                            $btn.prop('disabled', false);
                            setTimeout(function() { $saveIndicator.html(''); }, 3000);
                        });
                    } else {
                        $btn.prop('disabled', false);
                        setTimeout(function() { $saveIndicator.html(''); }, 2000);
                    }
                });
            }, 500); // Aguardar 500ms para o servidor processar
        }).catch(function() {
            $saveIndicator.html('<i class="fa fa-times text-danger"></i> Erro ao salvar');
            TimesheetModals.notify('danger', 'Falha ao salvar as horas antes do envio. Tente novamente.');
            $btn.prop('disabled', false);
            setTimeout(function() { $saveIndicator.html(''); }, 3000);
        });
    });

    $('#cancel-submission').on('click', function() {
        TimesheetModals.confirm({
            title: 'Cancelar Submissão',
            message: timesheet_data.confirm_cancel_submission || 'Tem certeza que deseja cancelar a submissão deste timesheet? Ele voltará ao status de rascunho.',
            icon: 'fa-undo',
            confirmText: 'Cancelar Submissão',
            cancelText: 'Manter Como Está',
            confirmClass: 'timesheet-modal-btn-warning'
        }).then(function(confirmed) {
            if (confirmed) {
                var data = {};
                data.week_start = timesheet_data.week_start;
                data[csrfData.token_name] = csrfData.hash;
                $.post(timesheet_data.admin_url + 'timesheet/cancel_submission', data).done(function(response) {
                    response = JSON.parse(response);
                    if (response.success) {
                        TimesheetModals.success(response.message, 'Submissão Cancelada').then(function() {
                            location.reload();
                        });
                    } else {
                        TimesheetModals.error(response.message, 'Erro ao Cancelar');
                    }
                });
            }
        });
    });

    $('#add-project-row').on('click', function() {
        $('#project-modal').modal('show');
    });

    $('#project-select').on('change', function(){
        var project_id = $(this).val();
        if(project_id) {
            $.get(timesheet_data.admin_url + 'timesheet/get_project_tasks/' + project_id, function(tasks){
                tasks = JSON.parse(tasks);
                var $taskSelect = $('#task-select');
                $taskSelect.empty().append('<option value="">Selecione uma tarefa</option>');
                $.each(tasks, function(i, task){
                    $taskSelect.append('<option value="'+task.id+'">'+task.name+'</option>');
                });
                $('#task-group').show();
            });
        } else {
            $('#task-group').hide();
        }
    });

    $('#add-project-confirm').on('click', function() {
        var projectId = $('#project-select').val();
        var taskId = $('#task-select').val();
        var projectName = $('#project-select').find('option:selected').text();
        var taskName = $('#task-select').find('option:selected').text();

        if(!projectId || !taskId) {
            TimesheetModals.warning('Por favor, selecione um projeto E uma tarefa.', 'Seleção Obrigatória');
            return;
        }

        if ($('tr[data-project-id="'+projectId+'"][data-task-id="'+taskId+'"]').length > 0) {
            TimesheetModals.warning('Este projeto/tarefa já foi adicionado à sua planilha.', 'Projeto Duplicado');
            return;
        }

        var row_html = '<tr data-project-id="'+projectId+'" data-task-id="'+taskId+'">' +
            '<td><strong>'+projectName+'</strong><br><small class="text-muted">'+taskName+'</small></td>';
        for (var i = 1; i <= 7; i++) {
            row_html += '<td class="text-center"><input type="text" class="form-control hours-input text-center" data-day="'+i+'" placeholder="0,00"></td>';
        }
        row_html += '<td class="text-center total-hours"><strong>0,00</strong></td>' + 
                    '<td class="text-center"><button type="button" class="btn btn-danger btn-xs remove-row"><i class="fa fa-trash"></i></button></td>' +
                    '</tr>';

        $('#timesheet-entries').append(row_html);
        $('#project-modal').modal('hide');

        $('#project-select').val('').trigger('change');
    });

    $(document).on('click', '.remove-row', function(){
        var $row = $(this).closest('tr');
        TimesheetModals.confirm({
            title: 'Remover Linha',
            message: 'Tem certeza que deseja remover esta linha? Todas as horas lançadas nela serão perdidas.',
            icon: 'fa-trash',
            confirmText: 'Remover',
            cancelText: 'Cancelar',
            confirmClass: 'timesheet-modal-btn-danger'
        }).then(function(confirmed) {
            if (confirmed) {
                $row.remove();
                updateTotals();
            }
        });
    });

    function updateTotals() {
        var dailyTotals = Array(8).fill(0);
        var weekTotal = 0;

        $('#timesheet-entries tr').each(function() {
            var rowTotal = 0;
            $(this).find('.hours-input').each(function() {
                var hours = parseHours($(this).val());
                var day = $(this).data('day');
                dailyTotals[day] += hours;
                rowTotal += hours;
            });
            $(this).find('.total-hours strong').text(formatHours(rowTotal));
        });

        for (var i = 1; i <= 7; i++) {
            $('.daily-total[data-day="' + i + '"]').text(formatHours(dailyTotals[i]));
            weekTotal += dailyTotals[i];
        }
        $('.week-total').text(formatHours(weekTotal));
    }

    updateTotals();
});