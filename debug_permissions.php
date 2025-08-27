
<?php
// Script para debug das permissões do módulo Timesheet

echo "<h2>Debug de Permissões do Módulo Timesheet</h2>";

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

// Verificar permissões na tabela
$staff_id = isset($_GET['staff_id']) ? (int)$_GET['staff_id'] : 3;

echo "<h3>Debug para Staff ID: $staff_id</h3>";

// Buscar todas as permissões do usuário
$permissions_query = "SELECT * FROM `{$prefix}staff_permissions` WHERE staff_id = $staff_id AND feature = 'timesheet'";
$permissions_result = $mysqli->query($permissions_query);

echo "<h4>Permissões do Timesheet na base de dados:</h4>";
if ($permissions_result && $permissions_result->num_rows > 0) {
    echo "<ul>";
    while ($row = $permissions_result->fetch_assoc()) {
        echo "<li><strong>" . $row['capability'] . "</strong></li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>❌ Nenhuma permissão encontrada para este usuário</p>";
}

// Verificar se o usuário é admin
$staff_query = "SELECT * FROM `{$prefix}staff` WHERE staffid = $staff_id";
$staff_result = $mysqli->query($staff_query);

if ($staff_result && $staff_row = $staff_result->fetch_assoc()) {
    echo "<h4>Informações do Staff:</h4>";
    echo "<ul>";
    echo "<li><strong>Nome:</strong> " . $staff_row['firstname'] . " " . $staff_row['lastname'] . "</li>";
    echo "<li><strong>Email:</strong> " . $staff_row['email'] . "</li>";
    echo "<li><strong>É Admin:</strong> " . ($staff_row['admin'] == '1' ? 'SIM' : 'NÃO') . "</li>";
    echo "<li><strong>Ativo:</strong> " . ($staff_row['active'] == '1' ? 'SIM' : 'NÃO') . "</li>";
    echo "</ul>";
}

// Verificar projetos que o usuário pode gerenciar
$projects_query = "SELECT COUNT(*) as count FROM `{$prefix}projects` WHERE addedfrom = $staff_id";
$projects_result = $mysqli->query($projects_query);
$projects_row = $projects_result->fetch_assoc();

echo "<h4>Projetos que pode gerenciar:</h4>";
echo "<p>" . $projects_row['count'] . " projeto(s)</p>";

// Verificar roles/funções do usuário
$role_query = "SELECT r.* FROM `{$prefix}roles` r 
               JOIN `{$prefix}staff` s ON s.role = r.roleid 
               WHERE s.staffid = $staff_id";
$role_result = $mysqli->query($role_query);

if ($role_result && $role_row = $role_result->fetch_assoc()) {
    echo "<h4>Função/Role:</h4>";
    echo "<ul>";
    echo "<li><strong>Nome:</strong> " . $role_row['name'] . "</li>";
    echo "<li><strong>ID:</strong> " . $role_row['roleid'] . "</li>";
    echo "</ul>";
}

$mysqli->close();

echo "<hr>";
echo "<h3>🔍 Análise:</h3>";
echo "<ul>";
echo "<li>Se o usuário tem a permissão 'approve', deve ver os menus de aprovação</li>";
echo "<li>Se o usuário tem 'view', 'create' ou 'edit', deve ver pelo menos 'Meu Timesheet'</li>";
echo "<li>Se nada aparece, pode ser um problema de cache do navegador ou sessão</li>";
echo "</ul>";

echo "<h3>📋 Próximos passos:</h3>";
echo "<ol>";
echo "<li>Faça logout e login novamente</li>";
echo "<li>Limpe o cache do navegador</li>";
echo "<li>Verifique os logs de atividade do sistema</li>";
echo "</ol>";
?>
