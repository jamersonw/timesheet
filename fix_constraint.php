
<?php
/**
 * Script para corrigir constraint única da tabela timesheet_approvals
 * Execute diretamente no navegador: /modules/timesheet/fix_constraint.php
 */

echo "<!DOCTYPE html>";
echo "<html><head><title>Fix Constraint - Timesheet</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 3px; }
</style></head><body>";

echo "<h1>🔧 Correção da Constraint Única - Timesheet</h1>";
echo "<p><em>Executado em: " . date('Y-m-d H:i:s') . "</em></p>";

// Configurações do banco
$hostname = 'localhost';
$username = 'u755875096_jamersonw';
$password = '59886320#Jw';
$database = 'u755875096_perfex';
$table_prefix = 'tbl';

try {
    // Conectar ao banco
    $pdo = new PDO("mysql:host={$hostname};dbname={$database}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<p class='success'>✅ Conectado ao banco de dados</p>";

    // Verificar se a tabela existe
    $check_table = $pdo->query("SHOW TABLES LIKE '{$table_prefix}timesheet_approvals'");
    if ($check_table->rowCount() == 0) {
        echo "<p class='error'>❌ Tabela {$table_prefix}timesheet_approvals não encontrada</p>";
        echo "</body></html>";
        exit;
    }

    echo "<p class='info'>📋 Tabela encontrada: {$table_prefix}timesheet_approvals</p>";

    // Verificar constraint atual
    $check_constraint = $pdo->query("SHOW INDEX FROM {$table_prefix}timesheet_approvals WHERE Key_name = 'unique_staff_week'");
    $has_old_constraint = $check_constraint->rowCount() > 0;

    $check_new_constraint = $pdo->query("SHOW INDEX FROM {$table_prefix}timesheet_approvals WHERE Key_name = 'unique_staff_week_task'");
    $has_new_constraint = $check_new_constraint->rowCount() > 0;

    echo "<p class='info'>🔍 Constraint antiga (unique_staff_week): " . ($has_old_constraint ? 'EXISTE' : 'NÃO EXISTE') . "</p>";
    echo "<p class='info'>🔍 Constraint nova (unique_staff_week_task): " . ($has_new_constraint ? 'EXISTE' : 'NÃO EXISTE') . "</p>";

    if ($has_new_constraint) {
        echo "<p class='success'>✅ Constraint já está corrigida! Nenhuma ação necessária.</p>";
    } else {
        echo "<h3>🚀 Iniciando correção...</h3>";

        // Remover constraint antiga se existir
        if ($has_old_constraint) {
            $pdo->exec("ALTER TABLE {$table_prefix}timesheet_approvals DROP INDEX unique_staff_week");
            echo "<p class='success'>✅ Constraint antiga removida</p>";
        }

        // Adicionar nova constraint
        $pdo->exec("ALTER TABLE {$table_prefix}timesheet_approvals ADD UNIQUE KEY unique_staff_week_task (staff_id, week_start_date, task_id)");
        echo "<p class='success'>✅ Nova constraint criada com task_id</p>";

        echo "<h3>🎉 Correção concluída com sucesso!</h3>";
    }

    // Verificar estrutura final
    echo "<h3>📊 Estrutura final da tabela:</h3>";
    $indexes = $pdo->query("SHOW INDEX FROM {$table_prefix}timesheet_approvals");
    echo "<pre>";
    while ($index = $indexes->fetch(PDO::FETCH_ASSOC)) {
        if (strpos($index['Key_name'], 'unique_staff_week') !== false) {
            echo "Index: {$index['Key_name']} | Coluna: {$index['Column_name']}\n";
        }
    }
    echo "</pre>";

} catch (Exception $e) {
    echo "<p class='error'>❌ ERRO: " . $e->getMessage() . "</p>";
    echo "<p class='info'>💡 Verifique as configurações do banco no início do arquivo</p>";
}

echo "<hr>";
echo "<h3>📝 Configurações atuais do script:</h3>";
echo "<ul>";
echo "<li><strong>Hostname:</strong> {$hostname}</li>";
echo "<li><strong>Database:</strong> {$database}</li>";
echo "<li><strong>Username:</strong> {$username}</li>";
echo "<li><strong>Table Prefix:</strong> {$table_prefix}</li>";
echo "</ul>";

echo "<p><em>Se as configurações estiverem incorretas, edite o arquivo fix_constraint.php</em></p>";
echo "</body></html>";
?>
