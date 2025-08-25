# Changelog - Módulo Timesheet











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

### 📋 FUNCIONALIDADES DO BUILD SYSTEM
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