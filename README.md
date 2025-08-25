
# Módulo Timesheet para Perfex CRM

Sistema completo de apontamento de horas com aprovação para profissionais e gerentes de projeto.

## 🚀 Funcionalidades

- **Interface tipo planilha** para apontamento semanal de horas
- **Sistema de aprovação** hierárquico com workflow completo
- **Sincronização bidirecional** com o quadro de horas nativo do Perfex CRM
- **Validações inteligentes** para projetos e tarefas ativas
- **Auto-save discreto** com feedback visual
- **Interface 100% em português brasileiro**
- **Sistema de logs** detalhado para debugging

## 📦 Instalação

1. **Download**: Baixe o arquivo ZIP da versão mais recente
2. **Extração**: Extraia na pasta `modules/` do seu Perfex CRM
3. **Estrutura**: Certifique-se que a estrutura seja `modules/timesheet/`
4. **Ativação**: Ative o módulo no painel administrativo

```
perfex-crm/
└── modules/
    └── timesheet/
        ├── assets/
        ├── controllers/
        ├── helpers/
        ├── language/
        ├── migrations/
        ├── models/
        ├── views/
        ├── install.php
        └── timesheet.php
```

## 🔧 Desenvolvimento

### Requisitos
- PHP 7.4+
- Perfex CRM 2.3.0+
- Extensões PHP: zip, mysqli

### Build e Release

```bash
# Build patch (1.0.0 → 1.0.1)
php build.php patch "Correção de bug crítico"

# Build minor (1.0.0 → 1.1.0)
php build.php minor "Nova funcionalidade implementada"

# Build major (1.0.0 → 2.0.0)
php build.php major "Refatoração completa"

# Usando script shell
./release.sh patch "Descrição da alteração"
```

### Estrutura de Versionamento

- **MAJOR**: Mudanças incompatíveis na API
- **MINOR**: Novas funcionalidades compatíveis
- **PATCH**: Correções de bugs

## 🎯 Como Usar

### Para Profissionais

1. Acesse **Timesheet > Meu Timesheet**
2. Selecione a semana desejada
3. Escolha projeto e tarefa
4. Insira as horas trabalhadas por dia
5. Clique em **Enviar para Aprovação**

### Para Gerentes

1. Acesse **Timesheet > Aprovações**
2. Visualize timesheets pendentes
3. Revise as horas apontadas
4. **Aprovar** ou **Rejeitar** com motivo

## 🔄 Sincronização

O módulo sincroniza automaticamente com o quadro de horas nativo:

- **Timesheet → Quadro**: Ao aprovar, cria timer no Perfex
- **Quadro → Timesheet**: Alterações refletem automaticamente

## 📋 Permissões

- **timesheet_view**: Visualizar próprio timesheet
- **timesheet_create**: Criar entradas de horas
- **timesheet_edit**: Editar entradas não aprovadas
- **timesheet_delete**: Deletar entradas próprias

## 🛠️ Configurações

Acesse **Setup > Configurações > Timesheet**:

- Horas padrão por dia
- Permitir entradas futuras
- Exigir seleção de tarefa
- Auto-envio semanal

## 📊 Relatórios

- Horas por projeto/funcionário
- Status de aprovações pendentes
- Produtividade semanal/mensal
- Integração com relatórios do Perfex

## 🐛 Troubleshooting

### Problemas Comuns

1. **Módulo não ativa**
   - Verifique permissões da pasta modules/
   - Confirme estrutura de arquivos

2. **Auto-save não funciona**
   - Verifique JavaScript habilitado
   - Confirme que não há erros no console

3. **Sincronização falha**
   - Verifique logs de atividade
   - Confirme hooks do Perfex CRM

### Logs e Debug

O módulo registra todas as operações no Log de Atividades do Perfex:

```
[Timesheet] Auto-save realizado para entrada ID 123
[Timesheet Sync] Timer 456 sincronizado com entrada 123
[Timesheet] Aprovação enviada para usuário ID 789
```

## 🔗 Links Úteis

- [Documentação Perfex CRM](https://help.perfexcrm.com/)
- [Desenvolvimento de Módulos](https://help.perfexcrm.com/category/modules/)
- [API do Perfex CRM](https://help.perfexcrm.com/api-documentation/)

## 📝 Changelog

Veja [CHANGELOG.md](CHANGELOG.md) para histórico completo de versões.

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature
3. Commit suas mudanças
4. Push para a branch
5. Abra um Pull Request

## 📄 Licença

Este módulo é licenciado sob os mesmos termos do Perfex CRM.

---

**Desenvolvido para Perfex CRM** - Sistema profissional de gestão de relacionamento com clientes.
