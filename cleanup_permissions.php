
<?php
// Script para limpar permissões desnecessárias do módulo Timesheet

echo "<h2>Limpando Permissões do Módulo Timesheet</h2>";

// Configurações do banco
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

// Listar permissões atuais
echo "<h3>Permissões atuais do Timesheet:</h3>";
$check_permissions = $mysqli->query("SELECT DISTINCT capability FROM `{$prefix}staff_permissions` WHERE feature = 'timesheet'");

if ($check_permissions->num_rows() > 0) {
    echo "<ul>";
    while ($row = $check_permissions->fetch_assoc()) {
        echo "<li>" . $row['capability'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Nenhuma permissão encontrada</p>";
}

// Remover permissões desnecessárias (create, edit, delete)
$permissions_to_remove = ['create', 'edit', 'delete'];

foreach ($permissions_to_remove as $permission) {
    $result = $mysqli->query("DELETE FROM `{$prefix}staff_permissions` WHERE feature = 'timesheet' AND capability = '$permission'");
    
    if ($result) {
        $affected = $mysqli->affected_rows;
        if ($affected > 0) {
            echo "<p style='color: orange;'>🗑️ Removidas $affected permissões de '$permission'</p>";
        } else {
            echo "<p style='color: gray;'>ℹ️ Nenhuma permissão '$permission' encontrada para remover</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Erro ao remover permissão '$permission': " . $mysqli->error . "</p>";
    }
}

// Verificar permissões finais
echo "<h3>Permissões finais do Timesheet:</h3>";
$final_permissions = $mysqli->query("SELECT capability, COUNT(*) as count FROM `{$prefix}staff_permissions` WHERE feature = 'timesheet' GROUP BY capability");

if ($final_permissions->num_rows() > 0) {
    echo "<ul>";
    while ($row = $final_permissions->fetch_assoc()) {
        echo "<li><strong>" . $row['capability'] . "</strong> (" . $row['count'] . " usuários)</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Nenhuma permissão encontrada</p>";
}

$mysqli->close();

echo "<hr>";
echo "<h3>✅ Limpeza concluída!</h3>";
echo "<p><strong>Permissões mantidas:</strong></p>";
echo "<ul>";
echo "<li><strong>view</strong> - Permite ver e criar timesheets próprios</li>";
echo "<li><strong>approve</strong> - Permite acessar telas de aprovação</li>";
echo "</ul>";

echo "<h3>📋 Próximos passos:</h3>";
echo "<ol>";
echo "<li>Acesse <strong>Configurações > Equipe > Funções</strong></li>";
echo "<li>Configure as permissões necessárias para cada função</li>";
echo "<li>Teste o acesso com os usuários</li>";
echo "</ol>";
?>
