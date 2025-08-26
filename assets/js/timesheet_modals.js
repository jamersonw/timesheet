
/**
 * Sistema de Modais Elegantes para Timesheet
 * Substitui confirm() e alert() nativos por modais customizados
 */

var TimesheetModals = {
    
    // Contador para IDs únicos
    modalCounter: 0,
    
    /**
     * Criar modal de confirmação elegante
     */
    confirm: function(options) {
        return new Promise(function(resolve) {
            var defaults = {
                title: 'Confirmar Ação',
                message: 'Tem certeza que deseja continuar?',
                icon: 'fa-question-circle',
                confirmText: 'Confirmar',
                cancelText: 'Cancelar',
                confirmClass: 'timesheet-modal-btn-primary',
                type: 'confirm'
            };
            
            var config = Object.assign({}, defaults, options);
            
            var modalId = 'timesheet-modal-' + (++TimesheetModals.modalCounter);
            
            var modalHtml = `
                <div class="timesheet-modal-overlay" id="${modalId}">
                    <div class="timesheet-modal ${config.type}">
                        <div class="timesheet-modal-header">
                            <h4 class="timesheet-modal-title">
                                <i class="fa ${config.icon}"></i>
                                ${config.title}
                            </h4>
                            <button type="button" class="timesheet-modal-close">&times;</button>
                        </div>
                        <div class="timesheet-modal-body">
                            ${config.message}
                        </div>
                        <div class="timesheet-modal-footer">
                            <button type="button" class="timesheet-modal-btn timesheet-modal-btn-secondary" data-action="cancel">
                                ${config.cancelText}
                            </button>
                            <button type="button" class="timesheet-modal-btn ${config.confirmClass}" data-action="confirm">
                                ${config.confirmText}
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(modalHtml);
            var $modal = $('#' + modalId);
            
            // Mostrar modal
            $modal.css('display', 'flex');
            
            // Event handlers
            $modal.find('[data-action="confirm"]').on('click', function() {
                TimesheetModals.close(modalId);
                resolve(true);
            });
            
            $modal.find('[data-action="cancel"], .timesheet-modal-close').on('click', function() {
                TimesheetModals.close(modalId);
                resolve(false);
            });
            
            // Fechar com ESC
            $(document).on('keydown.modal-' + modalId, function(e) {
                if (e.keyCode === 27) {
                    TimesheetModals.close(modalId);
                    resolve(false);
                }
            });
            
            // Fechar clicando no overlay
            $modal.on('click', function(e) {
                if (e.target === this) {
                    TimesheetModals.close(modalId);
                    resolve(false);
                }
            });
        });
    },
    
    /**
     * Modal de sucesso
     */
    success: function(message, title) {
        return TimesheetModals.alert({
            title: title || 'Sucesso!',
            message: message,
            icon: 'fa-check-circle',
            confirmText: 'OK',
            confirmClass: 'timesheet-modal-btn-success',
            type: 'success'
        });
    },
    
    /**
     * Modal de erro
     */
    error: function(message, title) {
        return TimesheetModals.alert({
            title: title || 'Erro!',
            message: message,
            icon: 'fa-exclamation-triangle',
            confirmText: 'OK',
            confirmClass: 'timesheet-modal-btn-danger',
            type: 'danger'
        });
    },
    
    /**
     * Modal de alerta
     */
    warning: function(message, title) {
        return TimesheetModals.alert({
            title: title || 'Atenção!',
            message: message,
            icon: 'fa-exclamation-triangle',
            confirmText: 'OK',
            confirmClass: 'timesheet-modal-btn-warning',
            type: 'warning'
        });
    },
    
    /**
     * Modal de alerta genérico
     */
    alert: function(options) {
        return new Promise(function(resolve) {
            var defaults = {
                title: 'Informação',
                message: '',
                icon: 'fa-info-circle',
                confirmText: 'OK',
                confirmClass: 'timesheet-modal-btn-primary',
                type: 'confirm'
            };
            
            var config = Object.assign({}, defaults, options);
            
            var modalId = 'timesheet-modal-' + (++TimesheetModals.modalCounter);
            
            var modalHtml = `
                <div class="timesheet-modal-overlay" id="${modalId}">
                    <div class="timesheet-modal ${config.type}">
                        <div class="timesheet-modal-header">
                            <h4 class="timesheet-modal-title">
                                <i class="fa ${config.icon}"></i>
                                ${config.title}
                            </h4>
                            <button type="button" class="timesheet-modal-close">&times;</button>
                        </div>
                        <div class="timesheet-modal-body">
                            ${config.message}
                        </div>
                        <div class="timesheet-modal-footer">
                            <button type="button" class="timesheet-modal-btn ${config.confirmClass}" data-action="confirm">
                                ${config.confirmText}
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(modalHtml);
            var $modal = $('#' + modalId);
            
            // Mostrar modal
            $modal.css('display', 'flex');
            
            // Event handlers
            $modal.find('[data-action="confirm"], .timesheet-modal-close').on('click', function() {
                TimesheetModals.close(modalId);
                resolve(true);
            });
            
            // Fechar com ESC
            $(document).on('keydown.modal-' + modalId, function(e) {
                if (e.keyCode === 27) {
                    TimesheetModals.close(modalId);
                    resolve(true);
                }
            });
            
            // Fechar clicando no overlay
            $modal.on('click', function(e) {
                if (e.target === this) {
                    TimesheetModals.close(modalId);
                    resolve(true);
                }
            });
        });
    },
    
    /**
     * Modal com input de texto (para motivo de rejeição)
     */
    prompt: function(options) {
        return new Promise(function(resolve) {
            var defaults = {
                title: 'Digite uma informação',
                message: '',
                placeholder: 'Digite aqui...',
                icon: 'fa-edit',
                confirmText: 'Confirmar',
                cancelText: 'Cancelar',
                confirmClass: 'timesheet-modal-btn-primary',
                required: false
            };
            
            var config = Object.assign({}, defaults, options);
            
            var modalId = 'timesheet-modal-' + (++TimesheetModals.modalCounter);
            
            var modalHtml = `
                <div class="timesheet-modal-overlay" id="${modalId}">
                    <div class="timesheet-modal">
                        <div class="timesheet-modal-header">
                            <h4 class="timesheet-modal-title">
                                <i class="fa ${config.icon}"></i>
                                ${config.title}
                            </h4>
                            <button type="button" class="timesheet-modal-close">&times;</button>
                        </div>
                        <div class="timesheet-modal-body">
                            ${config.message ? '<p>' + config.message + '</p>' : ''}
                            <textarea id="modal-textarea-${modalId}" placeholder="${config.placeholder}"></textarea>
                        </div>
                        <div class="timesheet-modal-footer">
                            <button type="button" class="timesheet-modal-btn timesheet-modal-btn-secondary" data-action="cancel">
                                ${config.cancelText}
                            </button>
                            <button type="button" class="timesheet-modal-btn ${config.confirmClass}" data-action="confirm" ${config.required ? 'disabled' : ''}>
                                ${config.confirmText}
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(modalHtml);
            var $modal = $('#' + modalId);
            var $textarea = $('#modal-textarea-' + modalId);
            var $confirmBtn = $modal.find('[data-action="confirm"]');
            
            // Mostrar modal e focar no textarea
            $modal.css('display', 'flex');
            setTimeout(function() { $textarea.focus(); }, 100);
            
            // Validação em tempo real se campo é obrigatório
            if (config.required) {
                $textarea.on('input', function() {
                    var hasText = $(this).val().trim().length > 0;
                    $confirmBtn.prop('disabled', !hasText);
                });
            }
            
            // Event handlers
            $confirmBtn.on('click', function() {
                var value = $textarea.val().trim();
                if (config.required && !value) {
                    $textarea.focus();
                    return;
                }
                TimesheetModals.close(modalId);
                resolve(value || null);
            });
            
            $modal.find('[data-action="cancel"], .timesheet-modal-close').on('click', function() {
                TimesheetModals.close(modalId);
                resolve(null);
            });
            
            // Enter para confirmar, ESC para cancelar
            $textarea.on('keydown', function(e) {
                if (e.keyCode === 13 && e.ctrlKey) { // Ctrl+Enter
                    $confirmBtn.click();
                }
            });
            
            $(document).on('keydown.modal-' + modalId, function(e) {
                if (e.keyCode === 27) {
                    TimesheetModals.close(modalId);
                    resolve(null);
                }
            });
            
            // Fechar clicando no overlay
            $modal.on('click', function(e) {
                if (e.target === this) {
                    TimesheetModals.close(modalId);
                    resolve(null);
                }
            });
        });
    },
    
    /**
     * Fechar modal
     */
    close: function(modalId) {
        var $modal = $('#' + modalId);
        $modal.find('.timesheet-modal').css('animation', 'modalSlideOut 0.2s ease-in');
        
        setTimeout(function() {
            $modal.remove();
            $(document).off('keydown.modal-' + modalId);
        }, 200);
    }
};

// Adicionar animação de saída ao CSS dinamicamente
$('<style>').text(`
    @keyframes modalSlideOut {
        from {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
        to {
            opacity: 0;
            transform: translateY(-30px) scale(0.95);
        }
    }
`).appendTo('head');
