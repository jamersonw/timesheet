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
        return isNaN(numericValue) ? 0 : numericureturn;
    }

    var $saveIndicator = $('#save-indicator');
    var saveTimeout;
    var saveQueue = [];
    var isProcessingQueue = false;
    var backupSaveInterval;
    var pendingChanges = new Set();

    // Inicializar backup automático a cada 30 segundos
    function initBackupSave() {
        backupSaveInterval = setInterval(function() {
            if (pendingChanges.size > 0) {
                console.log('🔄 [BACKUP-SAVE] Executando salvamento de backup para', pendingChanges.size, 'campos pendentes');
                saveAllPendingChanges();
            }
        }, 30000); // 30 segundos
    }

    // Processar fila de salvamento sequencialmente
    function processQueue() {
        if (isProcessingQueue || saveQueue.length === 0) {
            return;
        }

        isProcessingQueue = true;
        var $input = saveQueue.shift();
        var inputId = $input.data('input-id') || ($input.data('day') + '_' + $input.closest('tr').data('task-id'));

        $saveIndicator.html('<i class="fa fa-spinner fa-spin text-primary"></i> Salvando...');

        saveEntry($input).then(function(response) {
            pendingChanges.delete(inputId);

            // Se há mais itens na fila, continuar processando
            if (saveQueue.length > 0) {
                setTimeout(function() {
                    isProcessingQueue = false;
                    processQueue();
                }, 100); // Pequeno delay entre salvamentos
            } else {
                isProcessingQueue = false;
                $saveIndicator.html('<i class="fa fa-check text-success"></i> Tudo salvo');
                setTimeout(function() { 
                    if (saveQueue.length === 0) $saveIndicator.html(''); 
                }, 2500);
            }
        }).catch(function(error) {
            console.error('❌ [QUEUE-SAVE] Erro ao salvar:', error);
            isProcessingQueue = false;
            $saveIndicator.html('<i class="fa fa-times text-danger"></i> Erro ao salvar');
            setTimeout(function() { $saveIndicator.html(''); }, 3000);

            // Continuar com próximo item mesmo se este falhou
            if (saveQueue.length > 0) {
                setTimeout(function() {
                    processQueue();
                }, 1000);
            }
        });
    }

    // Adicionar à fila de salvamento
    function addToSaveQueue($input) {
        var inputId = $input.data('input-id') || ($input.data('day') + '_' + $input.closest('tr').data('task-id'));

        // Remover duplicatas da fila (manter apenas a última alteração)
        saveQueue = saveQueue.filter(function(item) {
            var itemId = item.data('input-id') || (item.data('day') + '_' + item.closest('tr').data('task-id'));
            return itemId !== inputId;
        });

        // Adicionar à fila
        saveQueue.push($input);
        pendingChanges.add(inputId);

        // Iniciar processamento se não estiver em andamento
        processQueue();
    }

    // Salvar todas as alterações pendentes
    function saveAllPendingChanges() {
        $('.hours-input').each(function() {
            var $input = $(this);
            var inputId = $input.data('input-id') || ($input.data('day') + '_' + $input.closest('tr').data('task-id'));

            if (pendingChanges.has(inputId)) {
                addToSaveQueue($input.clone());
            }
        });
    }

    // Auto-save melhorado com debounce de 1.5 segundos
    $(document).on('blur', '.hours-input', function() {
        var $input = $(this);
        var value = $input.val().trim();

        // Sempre formatar o valor, incluindo 0
        var formattedValue = formatHours(value);
        $input.val(formattedValue);

        // Marcar como alteração pendente
        var inputId = $input.data('input-id') || ($input.data('day') + '_' + $input.closest('tr').data('task-id'));
        pendingChanges.add(inputId);

        // Limpar timeout anterior e definir novo com 1.5 segundos
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(function() {
            addToSaveQueue($input);
        }, 1500); // Aumentado de 300ms para 1.5 segundos
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
            console.warn("⚠️ [SAVE-ENTRY] Salvamento abortado: task-id ou project-id não encontrado na linha da tabela (TR).", { 
                'task-id': taskId, 
                'project-id': projectId,
                'row-html': $row[0].outerHTML.substring(0, 200) + '...'
            });
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

    // Salvamento forçado de todas as entradas (usado antes de submissão)
    function saveAllEntries() {
        return new Promise(function(resolve, reject) {
            $saveIndicator.html('<i class="fa fa-spinner fa-spin text-warning"></i> Salvamento forçado em andamento...');

            // Limpar fila atual e timeout
            clearTimeout(saveTimeout);
            saveQueue = [];
            isProcessingQueue = false;

            var promises = [];
            var totalInputs = 0;
            var processedInputs = 0;

            $('.hours-input:not(:disabled)').each(function() {
                var $input = $(this);
                var inputId = $input.data('input-id') || ($input.data('day') + '_' + $input.closest('tr').data('task-id'));
                totalInputs++;

                var promise = saveEntry($input).then(function(response) {
                    pendingChanges.delete(inputId);
                    processedInputs++;

                    // Atualizar progresso
                    $saveIndicator.html('<i class="fa fa-spinner fa-spin text-warning"></i> Salvando ' + processedInputs + '/' + totalInputs + '...');

                    return response;
                }).catch(function(error) {
                    processedInputs++;
                    console.error('❌ [FORCE-SAVE] Erro ao salvar entrada:', error);
                    return error;
                });

                promises.push(promise);
            });

            if (promises.length === 0) {
                $saveIndicator.html('<i class="fa fa-check text-success"></i> Nada para salvar');
                setTimeout(function() { $saveIndicator.html(''); }, 1500);
                resolve();
                return;
            }

            Promise.allSettled(promises).then(function(results) {
                var successful = results.filter(r => r.status === 'fulfilled' && r.value.success !== false).length;
                var failed = results.length - successful;

                if (failed === 0) {
                    $saveIndicator.html('<i class="fa fa-check text-success"></i> Todas as ' + successful + ' entradas salvas');
                    pendingChanges.clear();
                    resolve();
                } else {
                    $saveIndicator.html('<i class="fa fa-exclamation-triangle text-warning"></i> ' + successful + ' salvas, ' + failed + ' falharam');
                    reject({ message: failed + ' entradas falharam ao salvar' });
                }

                setTimeout(function() { $saveIndicator.html(''); }, 3000);
            });
        });
    }

    $('#submit-timesheet').on('click', function() {
        var $btn = $(this);

        // Validar se existe pelo menos uma linha de projeto/tarefa
        if ($('#timesheet-entries tr').length === 0) {
            TimesheetModals.warning('Você deve adicionar pelo menos um projeto/tarefa antes de enviar o timesheet.', 'Nenhuma Atividade Selecionada');
            return;
        }

        $btn.prop('disabled', true); 

        // Executar salvamento forçado antes da submissão
        console.log('🚀 [SUBMIT] Iniciando salvamento forçado antes da submissão');
        saveAllEntries().then(function() {
            // Aguardar um momento para garantir que o servidor processou todas as alterações
            setTimeout(function() {
                $saveIndicator.html('<i class="fa fa-check text-success"></i> Todas as entradas salvas');

                // Verificar se todas as horas são zeradas
                var totalHours = 0;
                $('.hours-input').each(function() {
                    totalHours += parseHours($(this).val());
                });

                var confirmMessage = timesheet_data.confirm_submit || 'Tem certeza que deseja enviar este timesheet para aprovação? Esta ação não pode ser desfeita.';

                if (totalHours === 0) {
                    confirmMessage += '<br><br><strong class="text-warning"><i class="fa fa-exclamation-triangle"></i> Atenção:</strong> Você está enviando um timesheet sem nenhuma hora lançada (todos os dias estão zerados).';
                }

                // Usar modal elegante ao invés de confirm()
                TimesheetModals.confirm({
                    title: 'Enviar para Aprovação',
                    message: confirmMessage,
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
            title: _l('timesheet_cancel_submission'),
            message: timesheet_data.confirm_cancel_submission || _l('timesheet_confirm_cancel_submission'),
            icon: 'fa-undo',
            confirmText: _l('cancel_submission'),
            cancelText: _l('keep_as_is'),
            confirmClass: 'timesheet-modal-btn-warning'
        }).then(function(confirmed) {
            if (confirmed) {
                var data = {};
                data.week_start = timesheet_data.week_start;
                data[csrfData.token_name] = csrfData.hash;
                $.post(timesheet_data.admin_url + 'timesheet/cancel_submission', data).done(function(response) {
                    response = JSON.parse(response);
                    if (response.success) {
                        TimesheetModals.notify('success', response.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        TimesheetModals.notify('danger', response.message);
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
            row_html += '<td class="text-center"><input type="text" class="form-control hours-input text-center" data-day="'+i+'" data-input-id="'+taskId+'_'+i+'" placeholder="0,00"></td>';
        }
        row_html += '<td class="text-center total-hours"><strong>0,00</strong></td>' + 
                    '<td class="text-center"><button type="button" class="btn btn-danger btn-xs remove-row"><i class="fa fa-trash"></i></button></td>' +
                    '</tr>';

        $('#timesheet-entries').append(row_html);
        $('#project-modal').modal('hide');

        // Verificar se deve mostrar o botão de submissão
        checkSubmitButtonVisibility();

        $('#project-select').val('').trigger('change');
    });

    $(document).on('click', '.remove-row', function(){
        var $row = $(this).closest('tr');
        TimesheetModals.confirm({
            title: _l('timesheet_remove_row'),
            message: _l('timesheet_confirm_remove_row'),
            icon: 'fa-trash',
            confirmText: _l('remove'),
            cancelText: _l('cancel'),
            confirmClass: 'timesheet-modal-btn-danger'
        }).then(function(confirmed) {
            if (confirmed) {
                $row.remove();
                updateTotals();
                checkSubmitButtonVisibility();
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

    // Inicializar sistema de backup automático
    initBackupSave();

    // Função para verificar se deve mostrar o botão de submissão
    function checkSubmitButtonVisibility() {
        var hasEntries = $('#timesheet-entries tr').length > 0;
        var $submitBtn = $('#submit-timesheet');
        
        if (hasEntries) {
            $submitBtn.show();
        } else {
            $submitBtn.hide();
        }
    }

    // Verificar visibilidade inicial do botão
    checkSubmitButtonVisibility();

    // Limpeza quando a página for fechada
    $(window).on('beforeunload', function() {
        clearInterval(backupSaveInterval);

        // Se há alterações pendentes, avisar o usuário
        if (pendingChanges.size > 0) {
            return 'Você tem alterações não salvas. Tem certeza que deseja sair?';
        }
    });

    // Salvamento forçado ao navegar para outra página
    $(document).on('click', 'a[href], button[type="submit"]', function(e) {
        if (pendingChanges.size > 0 && !$(this).hasClass('hours-input') && !$(this).hasClass('remove-row')) {
            e.preventDefault();
            var originalTarget = this;

            $saveIndicator.html('<i class="fa fa-spinner fa-spin text-info"></i> Salvando antes de navegar...');

            saveAllEntries().then(function() {
                // Continuar com a navegação
                if (originalTarget.href) {
                    window.location.href = originalTarget.href;
                } else if (originalTarget.onclick) {
                    originalTarget.onclick();
                }
            }).catch(function() {
                TimesheetModals.warning('Algumas alterações podem não ter sido salvas. Deseja continuar mesmo assim?', 'Alterações Pendentes')
                .then(function(confirmed) {
                    if (confirmed) {
                        if (originalTarget.href) {
                            window.location.href = originalTarget.href;
                        } else if (originalTarget.onclick) {
                            originalTarget.onclick();
                        }
                    }
                });
            });
        }
    });
});