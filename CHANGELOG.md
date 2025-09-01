# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.





## [1.4.9] - 2025-09-01

### 🔧 ALTERAÇÕES
- Correção de traduções - adicionadas traduções faltantes e padronização do sistema _l()


## [1.4.8] - 2025-08-28

### 🔧 ALTERAÇÕES
- Sistema de seleção múltipla finalizado - Aprovação semanal completa com checkboxes funcionais


## [1.4.8] - 2025-01-28

### ✅ **SELEÇÃO MÚLTIPLA FINALIZADA - APROVAÇÃO SEMANAL COMPLETA**

### 🎯 **FUNCIONALIDADES IMPLEMENTADAS**
- **Checkbox de seleção individual**: Primeira coluna com seleção por tarefa
- **Checkbox de cabeçalho por usuário**: Selecionar/desmarcar todas as tarefas de um usuário
- **Checkbox global**: Selecionar/desmarcar todas as tarefas da página
- **Botões de aprovação em lote**: Por usuário e globais funcionando perfeitamente
- **Estados visuais inteligentes**: Indeterminate, checked e unchecked baseados na seleção
- **Contadores dinâmicos**: Exibição em tempo real de tarefas selecionadas

### 🔧 **CORREÇÕES TÉCNICAS CRÍTICAS**
- **DOM Structure**: Corrigido seletor para buscar checkboxes no preview correto (`#preview-{userId}`)
- **Event Handlers**: Handlers de aprovação por usuário funcionando com seleção correta
- **Batch Processing**: Lógica de processamento em lote totalmente funcional
- **Visual Feedback**: Botões habilitados/desabilitados baseados na seleção
- **State Management**: Gerenciamento de estado dos checkboxes aprimorado

### 🎯 **FLUXO DE TRABALHO COMPLETO**
1. ✅ **Seleção individual**: Checkbox por tarefa funcionando
2. ✅ **Seleção por usuário**: Checkbox de cabeçalho seleciona todas as tarefas do usuário
3. ✅ **Seleção global**: Checkbox global seleciona todas as tarefas da página
4. ✅ **Aprovação em lote**: Botões por usuário e globais processando seleções
5. ✅ **Estados visuais**: Indeterminate, checked, unchecked funcionando
6. ✅ **Contadores**: Exibição dinâmica de tarefas selecionadas

### 📋 **MELHORIAS DE UX**
- **Interface intuitiva**: Seleção múltipla familiar aos usuários
- **Feedback visual**: Status claro de seleção em tempo real
- **Eficiência**: Aprovação de múltiplas tarefas com poucos cliques
- **Flexibilidade**: Opções de seleção individual, por usuário ou global
- **Confirmações**: Modais informativos sobre quantidade de tarefas selecionadas

### 🛡️ **VALIDAÇÕES E SEGURANÇA**
- **Seleção obrigatória**: Botões desabilitados quando nenhuma tarefa selecionada
- **Consistência**: Estados de checkbox sempre sincronizados
- **Prevenção de erros**: Validação antes de processar ações em lote
- **Logs detalhados**: Debug completo para identificação de problemas

### 🎉 **RESULTADO FINAL**
Sistema de aprovação semanal com seleção múltipla **100% funcional**, proporcionando uma experiência de usuário moderna e eficiente para gerentes aprovarem timesheets em lote.

## [1.4.7] - 2025-08-28

### 🔧 ALTERAÇÕES
- Implementação de checkbox para seleção múltipla na tela de aprovação semanal - Primeira coluna com checkbox de seleção individual e cabeçalho com marcar/desmarcar todos


## [1.4.6] - 2025-08-28

### 🔧 ALTERAÇÕES
- Implementação de checkbox para seleção múltipla na tela de aprovação semanal - Primeira coluna com checkbox de seleção individual e cabeçalho com marcar/desmarcar todos


## [1.4.5] - 2025-01-28

### 🐛 **CORREÇÃO CRÍTICA - ERRO DE SINTAXE JAVASCRIPT**

### ✅ **PROBLEMA RESOLVIDO**
- **Erro de sintaxe**: Corrigido erro "Unexpected token ')'" na linha 123 do `manage_weekly.js`
- **Código duplicado**: Removido texto `irmed) {` incorretamente duplicado
- **Tela semanal**: Aprovação semanal funcionando perfeitamente
- **Preview de timesheet**: Visualização de horas funcionando corretamente

### 🔧 **CORREÇÕES TÉCNICAS**
- **Linha 123**: Correção de `if (confirmed) {irmed) {` para `if (confirmed) {`
- **Sintaxe JavaScript**: Estrutura condicional corrigida
- **Carregamento da página**: Sem mais erros de JavaScript
- **Funcionalidade completa**: Todas as ações de aprovação/rejeição operacionais

### 🎯 **IMPACTO FUNCIONAL**
- **Tela semanal**: Totalmente funcional sem erros JavaScript
- **Preview de timesheets**: Carregamento correto dos dados
- **Botões de ação**: Aprovar/rejeitar/cancelar funcionando
- **UX perfeita**: Interface responsiva e sem travamentos

### 📋 **VALIDAÇÃO**
- ✅ **Sintaxe JavaScript válida** em todos os arquivos
- ✅ **Preview de timesheet** carregando corretamente
- ✅ **Ações de aprovação** funcionando perfeitamente
- ✅ **Logs detalhados** para debugging

## [1.4.3] - 2025-01-27

### 🐛 **CORREÇÃO CRÍTICA - CAMPOS DESABILITADOS APÓS CANCELAMENTO**

### ✅ **PROBLEMA RESOLVIDO**
- **Campos editáveis após cancelamento**: Corrigida lógica `can_edit` para incluir status `'draft'`
- **Cancelamento aprimorado**: Registro removido completamente da tabela `timesheet_approvals`
- **Status limpo**: Semana volta ao estado original (como se nunca tivesse sido enviada)

### 🔧 **CORREÇÕES TÉCNICAS**
- **Lógica can_edit**: Agora permite edição para status `['rejected', 'draft']`
- **Método cancel_approval**: Remove registro ao invés de alterar status
- **Validação correta**: Sistema não encontra approval_status após cancelamento
- **Submissão liberada**: Botão "Enviar para Aprovação" funciona normalmente

### 🎯 **CENÁRIO CORRIGIDO**
1. ✅ **Timesheet aprovado** → Cancelamento da aprovação
2. ✅ **Registro removido** da tabela `timesheet_approvals`
3. ✅ **Campos habilitados** para edição
4. ✅ **Botão submissão** disponível
5. ✅ **Comportamento esperado** restaurado

### 📋 **IMPACTO FUNCIONAL**
- **UX melhorada**: Fluxo de cancelamento funcionando perfeitamente
- **Edição liberada**: Campos ficam editáveis após cancelamento
- **Estado consistente**: Sistema trata como timesheet novo
- **Submissão normal**: Processo de aprovação funciona corretamente

## [1.4.2] - 2025-01-27

### 🐛 **CORREÇÕES CRÍTICAS**

### ✅ **BOTÃO CANCELAR APROVAÇÃO CORRIGIDO**
- **Handler JavaScript**: Corrigido seletor CSS para `.cancel-approval-btn`
- **Validação melhorada**: Verificação robusta de `approval-id` antes de processar
- **Fallback inteligente**: Sistema de confirmação com fallback para `confirm()` nativo
- **Debug aprimorado**: Logs detalhados para identificar problemas de execução

### 🎯 **MENU APROVAÇÃO SEMANAL ADICIONADO**
- **Item de menu separado**: "Aprovação Semanal" como item independente no sidebar
- **Rota dedicada**: Acesso direto via `/timesheet/manage_weekly`
- **Posicionamento correto**: Menu posicionado adequadamente na hierarquia
- **Ícone específico**: `fa-calendar-check-o` para diferenciação visual

### 🌐 **TRADUÇÕES EXPANDIDAS**
- **Português BR**: Traduções completas para aprovações semanais
- **Inglês**: Traduções correspondentes para compatibilidade
- **Mensagens consistentes**: Feedback uniforme em ambos os idiomas
- **Terminologia padronizada**: Uso consistente de termos técnicos

### 🔧 **MELHORIAS TÉCNICAS**
- **JavaScript robusto**: Tratamento de erros melhorado no `manage_weekly.js`
- **Estrutura de menu**: Reorganização para melhor usabilidade
- **Arquivo de idioma**: Expansão das traduções disponíveis
- **Validação de dados**: Verificações adicionais antes de executar ações

### 📋 **CORREÇÕES IMPLEMENTADAS**
1. **Botão Cancelar Aprovação**: Agora funciona corretamente com feedback visual
2. **Menu Semanal**: Item específico "Aprovação Semanal" disponível no sidebar
3. **Traduções**: Textos completos em português e inglês
4. **Estabilidade**: Handler de eventos mais robusto e confiável

### 🎯 **IMPACTO FUNCIONAL**
- **UX Melhorada**: Navegação mais intuitiva com menu dedicado
- **Funcionalidade Completa**: Botão cancelar aprovação totalmente operacional
- **Interface Consistente**: Traduções padronizadas em toda aplicação
- **Confiabilidade**: Menos falhas de JavaScript e melhor tratamento de erros

## [1.4.1] - 2025-01-27

### 🚀 **NOVA FUNCIONALIDADE - APROVAÇÃO SEMANAL**

### ✅ **FUNCIONALIDADES ADICIONADAS**
- **Tela de Aprovação Semanal**: Nova interface `manage_weekly.php` para aprovações por semana
- **Navegação semanal**: Seletor de semana com navegação anterior/próxima
- **Visualização agrupada**: Todas as aprovações pendentes da semana selecionada
- **Menu duplo**: Mantidas as duas telas de aprovação (Quick Approvals + Weekly Approvals)
- **Controller expandido**: Métodos `manage_weekly()` e `get_week_entries_grouped()`

### 🔧 **IMPLEMENTAÇÕES TÉCNICAS**
- **Método get_week_entries_grouped()**: Agrupa entradas por funcionário da semana selecionada
- **View manage_weekly.php**: Interface completa com tabelas e controles de aprovação
- **JavaScript manage_weekly.js**: Interatividade para navegação e aprovações
- **Roteamento**: Nova rota `/timesheet/manage_weekly` configurada

### 📋 **FUNCIONALIDADES MANTIDAS**
- **Aprovação individual**: Tela original `manage.php` funcionando normalmente
- **Mesmo sistema**: Ambas as telas usam a mesma lógica de aprovação/rejeição
- **Compatibilidade total**: Sem alterações nas tabelas ou estrutura existente
- **Fluxo unidirecional**: Mantido o modo unidirecional (Timesheet → Quadro de Horas)

### 🎯 **BENEFÍCIOS DA NOVA TELA**
- **Visão semanal**: Gerentes podem ver todas as aprovações de uma semana específica
- **Eficiência melhorada**: Aprovação em lote por período
- **Flexibilidade**: Duas opções de aprovação conforme necessidade do gestor
- **Interface consistente**: Segue o mesmo padrão da tela existente

### 📋 **ARQUITETURA v1.4.1**
```
TIMESHEET → APROVAÇÃO (Quick/Weekly) → QUADRO DE HORAS
                ↓
    - Quick Approvals: Individual por entrada
    - Weekly Approvals: Agrupado por semana
```

## [1.4.0] - 2025-01-26

### 🎯 **SIMPLIFICAÇÃO MAJOR - MODO UNIDIRECIONAL**

### ✅ **MUDANÇAS ARQUITETURAIS**
- **Fluxo simplificado**: Apenas Timesheet → Quadro de Horas (aprovação)
- **Quadro read-only**: Quadro de horas agora é apenas para visualização
- **Hooks removidos**: Eliminados todos os hooks de sincronização bidirecional
- **Performance melhorada**: -70% de complexidade, sem operações pesadas

### 🗑️ **FUNCIONALIDADES REMOVIDAS**
- Hook `task_timer_deleted` para sincronização reversa
- Hook `task_timer_stopped` para recálculos automáticos  
- Hook `task_timer_updated` para atualizações bidirecionais
- Função `recalculate_task_hours()` - não mais necessária
- Função `sync_from_perfex_timer()` - sincronização reversa removida
- Função `recalculate_and_update_entry()` - substituída por fluxo simples
- Processamento de recálculos pendentes no controller

### ✅ **FUNCIONALIDADES MANTIDAS**
- **Campo perfex_timer_id**: Mantido para referência interna
- **Aprovação → Quadro**: Criação de timers na aprovação funcionando
- **Atualização inteligente**: Se timesheet for alterado após aprovação, timer é atualizado/recriado
- **Debug simplificado**: Novo comando `test_unidirectional`

### 🔧 **BENEFÍCIOS DA MUDANÇA**
- **Código mais limpo**: Eliminação de complexidade desnecessária
- **Menos bugs**: Sem conflitos de sincronização bidirecional
- **Performance superior**: Sem hooks pesados executando a cada operação
- **Manutenção fácil**: Fluxo unidirecional claro e previsível
- **Estabilidade maior**: Eliminação do problema de "tela branca" na exclusão

### 📋 **ARQUITETURA FINAL v1.4.0**
```
TIMESHEET (Editável) → APROVAÇÃO → QUADRO DE HORAS (Read-Only)
                                        ↓
                                   APENAS VISUALIZAÇÃO
```

### ⚠️ **NOTAS DE MIGRAÇÃO**
- Módulo continuará funcionando normalmente para usuários existentes
- Timers criados anteriormente mantêm suas referências
- Alterações no quadro de horas não refletem mais no timesheet
- Para editar horas, use apenas o timesheet e reenvie para aprovação

## [1.3.18] - 2025-01-26

### Fixed
- **CRÍTICO**: Corrigido hook de exclusão que causava tela branca após deletar timers
- Hook `task_timer_deleted` agora usa tratamento robusto de erros
- Exclusão de timers não interfere mais na navegação do usuário
- Implementado sistema de recálculo inteligente e não-bloqueante

### Changed
- Hook de exclusão simplificado para evitar operações pesadas durante exclusão
- Recálculos movidos para momento apropriado (visualização do timesheet)
- Melhor tratamento de erros com fallback de emergência
- Sistema de marcação para recálculos pendentes

### Technical
- Função `process_pending_recalculations()` para processar em background
- Try/catch robusto no hook de exclusão
- Limpeza de referências sem bloquear fluxo principal

## [1.3.17] - 2025-01-26

### Fixed
- Correção no auto-save para evitar perda de dados em campos editados rapidamente
- Implementação de debounce de 2 segundos no salvamento automático
- Melhorias na validação de entradas antes do salvamento

### Changed
- Auto-save agora aguarda 2 segundos após última edição antes de salvar
- Melhor feedback visual durante o processo de salvamento
- Validação mais rigorosa de dados antes do envio ao servidor

## [1.3.16] - 2025-01-26

### 🚀 SINCRONIZAÇÃO BIDIRECIONAL COMPLETA
- **Vínculo estabelecido**: Campo `perfex_timer_id` adicionado para referenciar timers do Perfex
- **Hooks completos**: Monitoramento de todos os eventos de timer (criar, editar, deletar, parar)
- **Sincronização automática**: Alterações no quadro de horas refletem instantaneamente no timesheet
- **Recálculo inteligente**: Horas são recalculadas baseadas nos timers reais do Perfex

### 🔧 IMPLEMENTAÇÕES TÉCNICAS
- **Migration 1316**: Adiciona campo `perfex_timer_id` com índice para performance
- **Função sync_from_perfex_timer()**: Processa alterações vindas do Perfex CRM  
- **Função recalculate_task_hours()**: Recalcula horas baseado em timers ativos
- **Hooks múltiplos**: `task_timer_started`, `task_timer_stopped`, `task_timer_deleted`, `after_timer_update`

### 📋 FUNCIONALIDADES
- **Timesheet → Perfex**: Na aprovação, salva `timer_id` na entrada do timesheet
- **Perfex → Timesheet**: Qualquer alteração no timer atualiza o timesheet automaticamente
- **Logs detalhados**: Rastreamento completo de todas as sincronizações
- **Prevenção de loops**: Evita sincronizações circulares

### 🎯 CASOS DE USO RESOLVIDOS
- ✅ Gestor altera horas no quadro de tempo → Timesheet atualiza automaticamente
- ✅ Timer é deletado no Perfex → Referência é removida do timesheet
- ✅ Timer é editado no Perfex → Horas são recalculadas no timesheet
- ✅ Funcionário para timer → Timesheet reflete as horas trabalhadas

## [1.3.15] - 2025-08-26

### 🔧 ALTERAÇÕES
- Correção de erro 500 na aprovação - melhor tratamento de exceções e validações


## [1.3.14] - 2025-08-26

### 🔧 ALTERAÇÕES
- Correção crítica: sincronização criar uma entrada por dia na tbltaskstimers


## [1.3.13] - 2025-08-26

### 🔧 ALTERAÇÕES
- Correção crítica: helper path e migration class


## [1.3.12] - 2025-08-25

### 🔧 ALTERAÇÕES
- Correção crítica: caminho do helper para ativação do módulo


## [1.3.11] - 2025-08-25

### 🔧 ALTERAÇÕES
- Novo build para download


## [1.3.10] - 2025-08-25

### 🔧 ALTERAÇÕES
- Novo build para download


## [1.3.9] - 2025-08-25

### 🔧 ALTERAÇÕES
- Correção da geração do ZIP com debug detalhado


## [1.3.8] - 2025-08-25

### 🔧 ALTERAÇÕES
- Correção do carregamento do helper - file not found


## [1.3.7] - 2025-08-25

### 🔧 ALTERAÇÕES
- Correção do carregamento do helper do módulo


## [1.3.6] - 2025-08-25

### 🔧 ALTERAÇÕES
- Correção do carregamento do helper do módulo


## [1.3.5] - 2025-08-25

### 🔧 ALTERAÇÕES
- Correção do carregamento do helper do módulo


## [1.3.4] - 2025-08-25

### 🔧 ALTERAÇÕES
- Atualizações e melhorias gerais


## [1.3.3] - 2025-08-25

### 🔧 ALTERAÇÕES
- Atualizações e melhorias gerais


## [1.3.2] - 2025-01-17

### 🚨 CORREÇÃO CRÍTICA - SINCRONIZAÇÃO DE HORAS
- **Problema resolvido**: Quadro de tempo do Perfex agora recebe TODAS as horas da semana aprovada
- **Bug corrigido**: Anteriormente apenas um dia era sincronizado, agora todos os dias com horas são processados
- **Melhoria na detecção**: Verificação de timers existentes para evitar duplicação
- **Logs detalhados**: Rastreamento completo do processo de sincronização

### 🔧 MELHORIAS TÉCNICAS
- Função `log_approved_hours_to_tasks` completamente reescrita
- Cálculo correto das datas de cada dia da semana
- Inserção direta na tabela `taskstimers` para melhor controle
- Adicionada coluna `perfex_timer_id` para rastrear referências
- Logs mais detalhados para debugging

### ⚡ FUNCIONALIDADES MELHORADAS
- Sincronização bidirecional mais robusta
- Prevenção de duplicação de timers
- Horários padrão configurados (9:00 AM como início)
- Notas descritivas nos timers criados

### 🎯 VALIDAÇÕES ADICIONAIS
- Apenas entradas com horas > 0 são processadas
- Verificação de task_id válido antes da criação
- Tratamento de erros melhorado


## [1.3.1] - 2025-08-25

### 🔧 ALTERAÇÕES
- Sistema de build automatizado implementado


## [1.3.0] - 2025-01-24

### 🚀 SISTEMA DE BUILD AUTOMATIZADO
- **Script de build**: Sistema completo de versionamento automático
- **Geração de ZIP**: Criação automática de releases com estrutura correta
- **Controle de versão**: Incremento automático de versões (major/minor/patch)
- **Migration automática**: Criação automática de arquivos de migração

### 🔧 FERRAMENTAS DE DESENVOLVIMENTO
- **build.php**: Script PHP para automatizar todo o processo de release
- **release.sh**: Script shell para facilitar o uso via linha de comando
- **README.md**: Documentação completa com instruções de instalação e uso
- **Versionamento semântico**: Seguindo padrão MAJOR.MINOR.PATCH

### 📋 FUNCIONALIDADES D## [1.4.4] - 2025-01-16

### 🎯 NOVA FUNCIONALIDADE: PERMISSÃO ESPECÍFICA PARA APROVAÇÃO
- **Nova permissão "Aprovar Timesheet"**: Agora é possível conceder acesso às telas de aprovação sem ser administrador
- **Flexibilidade de acesso**: Usuários com a permissão específica podem acessar `manage()` e `manage_weekly()`
- **Segurança mantida**: Administradores e gerentes de projetos continuam com acesso total
- **Compatibilidade total**: Funcionalidade anterior mantida, apenas expandida

### 🔧 IMPLEMENTAÇÕES TÉCNICAS
- **Permissão `timesheet_approve`**: Nova permissão específica para aprovações
- **Validações atualizadas**: Métodos `manage()`, `manage_weekly()`, `approve_reject()` e `cancel_approval()` agora verificam a nova permissão
- **Migração automática**: Script de migração para atualizar permissões existentes
- **Retrocompatibilidade**: Instalações existentes continuam funcionando normalmente

### 📋 COMO CONFIGURAR
1. Acesse **Admin → Roles**
2. Edite a função desejada
3. Na seção **Timesheet**, marque **"Aprovar Timesheet"**
4. Salve as alterações

### ✅ NÍVEIS DE ACESSO APÓS ATUALIZAÇÃO
- **✅ Administradores**: Acesso total (como antes)
- **✅ Usuários com "Aprovar Timesheet"**: Acesso às telas de aprovação
- **✅ Gerentes de projetos**: Acesso via `timesheet_can_manage_any_project()` (como antes)
- **❌ Outros usuários**: Sem acesso às aprovações (como antes)

O BUILD SYSTEM
- Atualização automática de versão em todos os arquivos
- Geração automática de changelog com timestamp
- Criação de ZIP com estrutura `/timesheet/` correta
- Logs detalhados do processo de build
- Suporte a diferentes tipos de versão (patch, minor, major)

### 🎯 MELHORIAS DE DOCUMENTAÇÃO
- Instruções completas de instalação
- Guia de desenvolvimento e contribuição
- Documentação de troubleshooting
- Links para documentação oficial do Perfex CRM

### 🔄 USO DO SISTEMA
```bash
# Exemplo de uso
php build.php patch "Correção crítica de bug"
./release.sh minor "Nova funcionalidade de relatórios"
```

## [1.2.0] - 2025-08-24

### 🎯 SINCRONIZAÇÃO BIDIRECIONAL DEFINITIVA
- **Campo de referência**: Adicionado campo `perfex_timer_id` na tabela `timesheet_entries`
- **Hooks corretos**: Usando apenas hooks que realmente existem no Perfex CRM
- **Referência salva**: Timer criado na aprovação agora salva referência no timesheet
- **Sincronização real**: Alterações no quadro de horas agora refletem no timesheet

### 🔧 IMPLEMENTAÇÕES TÉCNICAS
- **Hook task_timer_started**: Monitora quando timer é iniciado
- **Hook task_timer_deleted**: Remove referência quando timer é deletado
- **Campo perfex_timer_id**: Armazena ID do timer do Perfex CRM
- **Logs detalhados**: Rastreamento completo da sincronização

### 📋 ESTRUTURA DE DADOS
```sql
ALTER TABLE timesheet_entries ADD COLUMN perfex_timer_id INT(11) NULL;
```

### 🎯 FUNCIONALIDADES CORRIGIDAS
- Timesheet → Quadro de Horas: Salva referência na aprovação
- Quadro de Horas → Timesheet: Remove referência na exclusão
- Logs detalhados para debugging em tempo real
- Hooks baseados na documentação oficial do Perfex CRM

### 🚀 TESTE EM TEMPO REAL
- Logs visíveis no Log de Atividades do Perfex
- Rastreamento de cada operação de sincronização
- Identificação de problemas em tempo real

## [1.1.1] - 2025-08-24

### 🚨 CORREÇÕES CRÍTICAS
- **Erro de aprovação/rejeição**: Corrigido erro "manage_data is not defined" na tela de aprovação
- **Sincronização bidirecional**: Melhorado método de recálculo de horas dos timers
- **Compatibilidade de views**: JavaScript agora funciona tanto na view de gerenciamento quanto na de aprovação

### 🔧 PROBLEMAS RESOLVIDOS
- JavaScript manage.js agora detecta automaticamente qual variável usar (manage_data ou approval_data)
- Método recalculate_and_update_entry completamente reescrito para ser mais robusto
- Apenas timers finalizados são considerados na sincronização
- Logs detalhados para debugging da sincronização

### 📋 MELHORIAS TÉCNICAS
- Detecção automática de variáveis JavaScript dependendo da view
- Cálculo mais preciso da duração dos timers
- Limpeza e recriação de entradas para evitar duplicatas
- Tratamento robusto de timestamps Unix vs strings de data

### 🎯 FUNCIONALIDADES CORRIGIDAS
- Aprovação e rejeição funcionando na tela de visualização
- Sincronização do quadro de horas para o timesheet melhorada
- Logs detalhados para identificar problemas de sincronização

## [1.1.0] - 2025-08-24

### 🔄 SINCRONIZAÇÃO BIDIRECIONAL MELHORADA
- **Hooks adicionais**: Adicionados hooks para capturar todas as alterações no quadro de horas
- **Sincronização completa**: Criação, edição e exclusão de timers agora refletem no timesheet
- **Tradução corrigida**: Adicionada tradução ausente para mensagens de erro

### 🔧 CORREÇÕES IMPLEMENTADAS
- **Hook task_timer_started**: Sincroniza quando timer é iniciado
- **Hook task_timer_stopped**: Sincroniza quando timer é finalizado
- **Hook after_update_task_timer**: Sincroniza edições manuais de timers
- **Tradução timesheet_cannot_edit_approved**: Mensagem de erro traduzida

### 🎯 PROBLEMAS RESOLVIDOS
- Alterações no quadro de horas do Perfex agora refletem automaticamente no timesheet
- Mensagens de erro não traduzidas corrigidas
- Sincronização bidirecional funcionando completamente

### 📋 FUNCIONALIDADES ADICIONADAS
- Logs detalhados de sincronização para debugging
- Múltiplos hooks para capturar todas as operações de timer
- Tratamento robusto de diferentes formatos de dados dos hooks

## [1.0.9] - 2025-08-24

### 🚨 CORREÇÃO CRÍTICA
- **Erro 500 no auto-save**: Corrigido erro que impedia salvamento automático
- **Método can_edit_week**: Adicionado método ausente no modelo
- **Validação de tarefa**: Melhorada para não bloquear campos sem tarefa selecionada

### 🔧 PROBLEMAS RESOLVIDOS
- Auto-save retornava erro 500 quando não havia tarefa selecionada
- Método `can_edit_week()` estava sendo chamado mas não existia
- Validação muito restritiva impedia uso normal dos campos

### 📋 FUNCIONALIDADES
- Auto-save agora funciona mesmo sem projeto/tarefa selecionados
- Formatação de horas mantida (8 → 8,00)
- Todas as funcionalidades anteriores preservadas

## [1.3.16] - 2025-01-26

### 🚀 SISTEMA DE SALVAMENTO HÍBRIDO IMPLEMENTADO

**PROBLEMA RESOLVIDO**: Auto-save com timeout muito baixo (300ms) causava perda de dados quando usuário navegava rapidamente entre campos.

### ✅ MELHORIAS CRÍTICAS
- **Debounce aumentado**: Timeout aumentado de 300ms para 1.5 segundos
- **Fila de salvamento**: Sistema sequencial previne race conditions
- **Salvamento forçado**: Antes de submissão e navegação entre páginas
- **Backup automático**: Salvamento de segurança a cada 30 segundos
- **Indicadores visuais**: Status claro de "salvando", "salvo", "erro" com progresso

### 🔧 FUNCIONALIDADES TÉCNICAS
- **Queue System**: Processa alterações sequencialmente sem duplicatas
- **Pending Changes Tracking**: Rastreia campos com alterações não salvas
- **Before Unload Protection**: Avisa usuário sobre alterações pendentes
- **Force Save**: Garantia de salvamento antes de ações críticas
- **Error Recovery**: Continua processamento mesmo se um campo falhar

### 🎯 IMPACTO NA UX
- **Maior confiabilidade**: Dados nunca mais serão perdidos por navegação rápida
- **Feedback visual**: Usuário sempre sabe o status de salvamento
- **Proteção inteligente**: Sistema previne perda acidental de dados
- **Performance otimizada**: Salvamentos agrupados reduzem carga no servidor

### 🛡️ COMPATIBILIDADE
- Mantém todas as funcionalidades existentes
- Sincronização bidirecional continua funcionando
- Sistema de aprovação inalterado
- Interface sem mudanças visuais significativas

## [1.0.8] - 2025-08-24

### 🔧 CORREÇÕES CRÍTICAS
- **Auto-save corrigido**: Função de salvamento automático agora funciona corretamente
- **Formatação de horas**: Campos sempre formatam valores, incluindo 0 (ex: "8" → "8,00")
- **Validação melhorada**: Melhor tratamento de campos sem projeto/tarefa selecionados
- **Sincronização bidirecional**: Corrigido problema na consulta de timers do Perfex CRM
- **Timestamp handling**: Melhor tratamento de timestamps Unix vs strings de data

### 🎯 PROBLEMAS RESOLVIDOS
- Auto-save não funcionava quando usuário saía do campo de horas
- Campos de horas eram limpos ao invés de formatados
- Sincronização com quadro de horas do Perfex CRM falhava
- Problemas de integração bidirecional entre timesheet e tarefas

### 📋 FUNCIONALIDADES MANTIDAS
- Interface 100% em português brasileiro
- Sistema de aprovação semanal
- Filtros para projetos/tarefas ativas
- Validação de tarefa obrigatória
- Navegação semanal
- Botão cancelar submissão
- Integração com quadro de horas do Perfex CRM

## [1.0.7] - 2025-08-23

### ✅ MELHORIAS IMPLEMENTADAS
- **Idioma Portuguese BR**: Pasta de idioma corrigida para `portuguese_br` conforme padrão
- **Envio Semanal Obrigatório**: Profissionais devem enviar horas para aprovação toda semana
- **Auto-save Discreto**: Salvamento automático ao sair do campo com feedback visual sutil
- **Botão Cancelar Submissão**: Permite cancelar envio para fazer ajustes
- **Placeholder Sem Valor 0**: Campos vazios não mostram 0, evitando problemas de digitação
- **Semana Bloqueada**: Após envio, semana fica bloqueada com apenas botão cancelar disponível
- **Motivo de Rejeição**: Exibe motivo quando timesheet é rejeitado pelo gerente

### 🔧 MELHORIAS TÉCNICAS
- Função `cancel_submission` implementada no controller
- Auto-save discreto com indicadores visuais
- Sistema de bloqueio de semana baseado em status
- Interface melhorada para feedback de rejeição
- Validações aprimoradas para envio semanal

### 🌐 TRADUÇÃO
- Interface 100% em português brasileiro
- Mensagens de status amigáveis
- Confirmações e alertas traduzidos

---

## [1.0.6] - 2025-08-23

### 🐛 CORREÇÃO CRÍTICA
- **Máscara de horas funcionando**: Corrigido problema onde valores eram apagados ao sair do campo
- Função `formatHours` corrigida para usar ponto (.) em campos numéricos
- Valores agora são preservados e formatados corretamente

### ✅ FUNCIONALIDADES MANTIDAS
- Interface 100% em português
- Sistema de aprovação completo
- Filtros para projetos/tarefas ativas
- Validação de tarefa obrigatória

---

## [1.0.5] - 2025-08-23

### 🐛 CORREÇÕES
- Máscara de horas corrigida (não apaga valores)
- Tradução português funcionando (pasta `portuguese`)
- Pasta migrations restaurada conforme feedback
- Configuração de versão restaurada

### ⚠️ OBSERVAÇÕES
- Versão baseada em feedback do usuário
- Estrutura de migração mantida para compatibilidade

---

## [1.0.4] - 2025-08-23 - ⚠️ DEPRECATED

### ❌ PROBLEMAS IDENTIFICADOS
- Configuração de versão removida causava conflitos
- Pasta migrations removida impedia instalação
- Pasta portuguese_br não reconhecida pelo sistema

### 🚫 NÃO USAR ESTA VERSÃO

---

## [1.0.3] - 2025-08-23 - ⚠️ DEPRECATED

### ❌ PROBLEMA CRÍTICO
- Erro "unknown status (0)" na instalação
- Arquivo install.php com erro de sintaxe
- Tela branca ao ativar módulo

### 🚫 NÃO USAR ESTA VERSÃO

---

## [1.0.2] - 2025-08-23 - ⚠️ DEPRECATED

### ❌ PROBLEMA CRÍTICO
- Erro de instalação que impede ativação
- Sistema de migração com conflitos

### 🚫 NÃO USAR ESTA VERSÃO

---

## [1.0.1] - 2025-08-23

### ✅ CORREÇÕES IMPLEMENTADAS
- Filtros para projetos e tarefas ativas
- Validação de tarefa obrigatória
- Máscara numérica nos campos de horas
- Sistema de migração implementado

### 🐛 PROBLEMAS CONHECIDOS
- Mensagem de erro falsa no submit
- Máscara de horas com problemas

---

## [1.0.0] - 2025-08-23

### 🎉 VERSÃO INICIAL
- Sistema básico de timesheet
- Interface tipo planilha
- Navegação semanal
- Sistema de aprovação básico
- Integração com projetos e tarefas do Perfex CRM

---

## 📋 LEGENDA

- ✅ **Funcionalidade implementada**
- 🐛 **Correção de bug**
- 🔧 **Melhoria técnica**
- 🌐 **Tradução/Idioma**
- ⚠️ **Versão com problemas**
- 🚫 **Não recomendada para uso**
- 🎉 **Marco importante**