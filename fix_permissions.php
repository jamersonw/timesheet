
<?php
/**
 * Script para corrigir permissões do módulo Timesheet
 * Execute via: /modules/timesheet/fix_permissions.php
 */

// Verificar se estamos no ambiente correto
$perfex_root = dirname(dirname(__DIR__));
$config_file = $perfex_root . '/application/config/config.php';

if (!file_exists($config_file)) {
    die('Este script deve ser executado a partir do diretório modules/timesheet/ dentro do Perfex CRM');
}

echo "<h2>Corrigindo Permissões do Módulo Timesheet</h2>";

// Carregar configuração do banco MANUALMENTE (sem incluir o arquivo database.php)
$db_config_content = file_get_contents($perfex_root . '/application/config/database.php');

// Extrair configurações usando regex
preg_match("/\['hostname'\]\s*=\s*['\"]([^'\"]+)['\"]/", $db_config_content, $hostname_match);
preg_match("/\['username'\]\s*=\s*['\"]([^'\"]+)['\"]/", $db_config_content, $username_match);
preg_match("/\['password'\]\s*=\s*['\"]([^'\"]*)['\"]/", $db_config_content, $password_match);
preg_match("/\['database'\]\s*=\s*['\"]([^'\"]+)['\"]/", $db_config_content, $database_match);
preg_match("/\['dbprefix'\]\s*=\s*['\"]([^'\"]*)['\"]/", $db_config_content, $prefix_match);

if (!$hostname_match || !$username_match || !$database_match) {
    die('Não foi possível extrair configurações do banco de dados');
}

$hostname = $hostname_match[1];
$username = $username_match[1];
$password = isset($password_match[1]) ? $password_match[1] : '';
$database = $database_match[1];
$dbprefix = isset($prefix_match[1]) ? $prefix_match[1] : 'tbl_';

echo "<p><strong>Configurações detectadas:</strong></p>";
echo "<ul>";
echo "<li>Host: " . $hostname . "</li>";
echo "<li>Database: " . $database . "</li>";
echo "<li>Prefix: " . $dbprefix . "</li>";
echo "</ul>";

try {
    // Conectar ao banco
    $mysqli = new mysqli($hostname, $username, $password, $database);

    if ($mysqli->connect_error) {
        die('Erro de conexão: ' . $mysqli->connect_error);
    }

    echo "<p style='color: green;'>✅ Conectado ao banco de dados</p>";

    // Definir permissões corretas
    $permissions = [
        'view' => 'Visualizar',
        'create' => 'Criar', 
        'edit' => 'Editar',
        'delete' => 'Deletar',
        'approve' => 'Aprovar Timesheet'
    ];

    $table_name = $dbprefix . 'staff_permissions';
    
    echo "<p>Tabela de permissões: <strong>$table_name</strong></p>";

    // Verificar se a tabela existe
    $table_check = $mysqli->query("SHOW TABLES LIKE '$table_name'");
    if ($table_check->num_rows == 0) {
        echo "<p style='color: red;'>❌ Tabela $table_name não existe. Verifique se o Perfex CRM está instalado corretamente.</p>";
        $mysqli->close();
        exit;
    }

    // Limpar permissões existentes do timesheet
    $delete_sql = "DELETE FROM `$table_name` WHERE feature = 'timesheet'";
    if ($mysqli->query($delete_sql)) {
        echo "<p style='color: green;'>✅ Permissões antigas removidas</p>";
    }

    // Inserir permissões corretas
    foreach ($permissions as $permission => $name) {
        $insert_sql = "INSERT INTO `$table_name` (feature, capability) VALUES ('timesheet', '$permission')";
        
        if ($mysqli->query($insert_sql)) {
            echo "<p style='color: green;'>✅ Permissão '$permission' ($name) adicionada</p>";
        } else {
            echo "<p style='color: red;'>❌ Erro ao adicionar '$permission': " . $mysqli->error . "</p>";
        }
    }

    // Verificar resultado final
    $check_sql = "SELECT * FROM `$table_name` WHERE feature = 'timesheet'";
    $result = $mysqli->query($check_sql);
    
    echo "<h3>Permissões Instaladas:</h3>";
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        $perm_name = isset($permissions[$row['capability']]) ? $permissions[$row['capability']] : $row['capability'];
        echo "<li><strong>" . $row['capability'] . "</strong> - " . $perm_name . "</li>";
    }
    echo "</ul>";

    echo "<p style='color: green; font-weight: bold;'>✅ Correção concluída! Vá para Configurações > Equipe > Cargos e verifique as permissões do módulo Timesheet.</p>";
    echo "<p><em>Nota: Pode ser necessário fazer logout e login novamente para as permissões fazerem efeito.</em></p>";
    echo "<hr>";
    echo "<p><strong>Importante:</strong> Este script é apenas para correção emergencial. Na instalação normal do módulo através do painel administrativo, essas permissões são criadas automaticamente.</p>";

    $mysqli->close();

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}
?>
