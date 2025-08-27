
<?php
// Script para forçar o recarregamento das permissões do módulo Timesheet

echo "<h2>Recarregando Permissões do Módulo Timesheet</h2>";

// Configurações do banco (use as mesmas do arquivo anterior)
$hostname = 'localhost';
$username = 'u755875096_jamersonw';
$password = '59886320#Jw';
$database = 'u755875096_perfex';
$prefix = 'tbl';

// Conectar ao banco
$mysqli = new mysqli($hostname, $username, $password, $database);

if ($mysqli->connect_error) {
    die("❌ Conexão falhou: " . $mysqli->connect_error);
}

echo "<p style='color: green;'>✅ Conectado ao banco de dados</p>";

// Tabela de permissões
$table_name = $prefix . 'staff_permissions';

// Verificar se a tabela existe
$check_table = $mysqli->query("SHOW TABLES LIKE '$table_name'");
if ($check_table->num_rows() == 0) {
    echo "<p style='color: red;'>❌ Tabela $table_name não existe.</p>";
    $mysqli->close();
    exit;
}

// Verificar permissões atuais do timesheet
$check_permissions = $mysqli->query("SELECT * FROM `$table_name` WHERE feature = 'timesheet'");
echo "<h3>Permissões atuais do Timesheet na base de dados:</h3>";

if ($check_permissions->num_rows() > 0) {
    echo "<ul>";
    while ($row = $check_permissions->fetch_assoc()) {
        echo "<li><strong>" . $row['capability'] . "</strong> (staff_id: " . $row['staff_id'] . ")</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: orange;'>⚠️ Nenhuma permissão encontrada para o timesheet</p>";
}

// Verificar se existe a permissão 'approve'
$check_approve = $mysqli->query("SELECT * FROM `$table_name` WHERE feature = 'timesheet' AND capability = 'approve'");

if ($check_approve->num_rows() > 0) {
    echo "<p style='color: green;'>✅ Permissão 'approve' existe na base de dados</p>";
} else {
    echo "<p style='color: red;'>❌ Permissão 'approve' não encontrada - adicionando...</p>";
    
    // Adicionar permissão approve
    $add_approve = "INSERT INTO `$table_name` (staff_id, feature, capability) VALUES (0, 'timesheet', 'approve')";
    if ($mysqli->query($add_approve)) {
        echo "<p style='color: green;'>✅ Permissão 'approve' adicionada</p>";
    } else {
        echo "<p style='color: red;'>❌ Erro ao adicionar permissão 'approve': " . $mysqli->error . "</p>";
    }
}

// Limpar cache do sistema (se houver tabela de cache)
$cache_tables = ['tbloptions', 'tbluser_meta'];
foreach ($cache_tables as $cache_table) {
    $check_cache = $mysqli->query("SHOW TABLES LIKE '$cache_table'");
    if ($check_cache->num_rows() > 0) {
        // Limpar entradas relacionadas a permissões
        $clear_cache = $mysqli->query("DELETE FROM `$cache_table` WHERE name LIKE '%timesheet%' OR name LIKE '%permission%' OR name LIKE '%staff_capabilities%'");
        if ($clear_cache) {
            echo "<p style='color: green;'>✅ Cache limpo da tabela $cache_table</p>";
        }
    }
}

$mysqli->close();

echo "<hr>";
echo "<h3>🔄 Próximos passos:</h3>";
echo "<ol>";
echo "<li><strong>Desative o módulo Timesheet</strong> em Configurações > Módulos</li>";
echo "<li><strong>Reative o módulo Timesheet</strong> (isso força o recarregamento das permissões)</li>";
echo "<li>Vá para <strong>Configurações > Equipe > Funções</strong></li>";
echo "<li>Edite a função desejada e verifique se a opção <strong>'Aprovar Timesheet'</strong> aparece</li>";
echo "</ol>";

echo "<p style='background-color: #f0f8ff; padding: 10px; border-left: 4px solid #0066cc;'>";
echo "<strong>💡 Dica:</strong> Se ainda não aparecer após reativar o módulo, tente fazer logout e login novamente no sistema.";
echo "</p>";
?>
