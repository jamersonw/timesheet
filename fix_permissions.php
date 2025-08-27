
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
$db_config_file = $perfex_root . '/application/config/database.php';

if (!file_exists($db_config_file)) {
    die('Arquivo database.php não encontrado em: ' . $db_config_file);
}

$db_config_content = file_get_contents($db_config_file);

if (empty($db_config_content)) {
    die('Não foi possível ler o conteúdo do arquivo database.php');
}

echo "<h3>Debug - Conteúdo do database.php (primeiras 500 chars):</h3>";
echo "<pre>" . htmlspecialchars(substr($db_config_content, 0, 500)) . "...</pre>";

// Múltiplos padrões de regex para diferentes formatos
$patterns = [
    'hostname' => [
        "/\['hostname'\]\s*=\s*['\"]([^'\"]+)['\"]/",
        "/\[\"hostname\"\]\s*=\s*['\"]([^'\"]+)['\"]/",
        "/hostname['\"]?\s*=>\s*['\"]([^'\"]+)['\"]/",
    ],
    'username' => [
        "/\['username'\]\s*=\s*['\"]([^'\"]+)['\"]/",
        "/\[\"username\"\]\s*=\s*['\"]([^'\"]+)['\"]/",
        "/username['\"]?\s*=>\s*['\"]([^'\"]+)['\"]/",
    ],
    'password' => [
        "/\['password'\]\s*=\s*['\"]([^'\"]*)['\"]/",
        "/\[\"password\"\]\s*=\s*['\"]([^'\"]*)['\"]/",
        "/password['\"]?\s*=>\s*['\"]([^'\"]*)['\"]/",
    ],
    'database' => [
        "/\['database'\]\s*=\s*['\"]([^'\"]+)['\"]/",
        "/\[\"database\"\]\s*=\s*['\"]([^'\"]+)['\"]/",
        "/database['\"]?\s*=>\s*['\"]([^'\"]+)['\"]/",
    ],
    'dbprefix' => [
        "/\['dbprefix'\]\s*=\s*['\"]([^'\"]*)['\"]/",
        "/\[\"dbprefix\"\]\s*=\s*['\"]([^'\"]*)['\"]/",
        "/dbprefix['\"]?\s*=>\s*['\"]([^'\"]*)['\"]/",
    ]
];

$config = [];

foreach ($patterns as $key => $pattern_list) {
    $found = false;
    foreach ($pattern_list as $pattern) {
        if (preg_match($pattern, $db_config_content, $matches)) {
            $config[$key] = $matches[1];
            $found = true;
            break;
        }
    }
    if (!$found && $key !== 'dbprefix' && $key !== 'password') {
        echo "<p style='color: red;'>❌ Não foi possível extrair: <strong>$key</strong></p>";
        echo "<p>Padrões testados:</p><ul>";
        foreach ($pattern_list as $pattern) {
            echo "<li><code>" . htmlspecialchars($pattern) . "</code></li>";
        }
        echo "</ul>";
        die('Erro na extração de configurações');
    }
}

// Valores padrão
$hostname = $config['hostname'] ?? '';
$username = $config['username'] ?? '';
$password = $config['password'] ?? '';
$database = $config['database'] ?? '';
$dbprefix = $config['dbprefix'] ?? 'tbl_';

if (empty($hostname) || empty($username) || empty($database)) {
    die('Configurações essenciais não encontradas. Hostname: ' . $hostname . ', Username: ' . $username . ', Database: ' . $database);
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
