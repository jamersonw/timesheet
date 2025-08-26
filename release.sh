
#!/bin/bash

# Script de Release para Módulo Timesheet
# Uso: ./release.sh [patch|minor|major] ["Descrição da alteração"]

VERSION_TYPE=${1:-patch}
DESCRIPTION=${2:-"Atualizações e melhorias gerais"}

echo "🚀 Iniciando release do módulo Timesheet..."
echo "📋 Tipo de versão: $VERSION_TYPE"
echo "📝 Descrição: $DESCRIPTION"

# Executar build
php build.php "$VERSION_TYPE" "$DESCRIPTION"

# Verificar se o build foi bem-sucedido
if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Release concluído com sucesso!"
    echo "📁 Arquivos ZIP gerados na pasta atual"
    echo ""
    echo "📋 Para usar:"
    echo "1. Extrair o ZIP no diretório modules/ do Perfex CRM"
    echo "2. A estrutura deve ficar: modules/timesheet/"
    echo "3. Ativar o módulo no painel administrativo"
else
    echo "❌ Erro durante o build"
    exit 1
fi
