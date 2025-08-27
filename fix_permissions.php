
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

// Verificar se database.php inclui app-config.php
if (strpos($db_config_content, 'app-config.php') !== false) {
    echo "<p style='color: blue;'>ℹ️ Detectado uso do app-config.php. Tentando extrair configurações...</p>";
    
    $app_config_file = $perfex_root . '/application/config/app-config.php';
    if (file_exists($app_config_file)) {
        echo "<p style='color: green;'>✅ Arquivo app-config.php encontrado</p>";
        $app_config_content = file_get_contents($app_config_file);
        
        echo "<h3>Debug - Conteúdo do app-config.php (primeiras 800 chars):</h3>";
        echo "<pre>" . htmlspecialchars(substr($app_config_content, 0, 800)) . "...</pre>";
        
        // Usar app-config.php em vez do database.php
        $db_config_content = $app_config_content;
    } else {
        echo "<p style='color: red;'>❌ app-config.php não encontrado em: $app_config_file</p>";
    }
}

// Padrões mais abrangentes incluindo variáveis PHP
$patterns = [
    'hostname' => [
        "/\\\$db\['default'\]\['hostname'\]\s*=\s*['\"]([^'\"]+)['\"]/",
        "/\\\$database\['hostname'\]\s*=\s*['\"]([^'\"]+)['\"]/", 
        "/define\(['\"]APP_DB_HOSTNAME['\"],\s*['\"]([^'\"]+)['\"]\)/",
        "/\\\$hostname\s*=\s*['\"]([^'\"]+)['\"]/",
        "/['\"]hostname['\"]?\s*=>\s*['\"]([^'\"]+)['\"]/",
    ],
    'username' => [
        "/\\\$db\['default'\]\['username'\]\s*=\s*['\"]([^'\"]+)['\"]/",
        "/\\\$database\['username'\]\s*=\s*['\"]([^'\"]+)['\"]/",
        "/define\(['\"]APP_DB_USERNAME['\"],\s*['\"]([^'\"]+)['\"]\)/",
        "/\\\$username\s*=\s*['\"]([^'\"]+)['\"]/",
        "/['\"]username['\"]?\s*=>\s*['\"]([^'\"]+)['\"]/",
    ],
    'password' => [
        "/\\\$db\['default'\]\['password'\]\s*=\s*['\"]([^'\"]*)['\"]/",
        "/\\\$database\['password'\]\s*=\s*['\"]([^'\"]*)['\"]/",
        "/define\(['\"]APP_DB_PASSWORD['\"],\s*['\"]([^'\"]*)['\"]\)/",
        "/\\\$password\s*=\s*['\"]([^'\"]*)['\"]/",
        "/['\"]password['\"]?\s*=>\s*['\"]([^'\"]*)['\"]/",
    ],
    'database' => [
        "/\\\$db\['default'\]\['database'\]\s*=\s*['\"]([^'\"]+)['\"]/",
        "/\\\$database\['database'\]\s*=\s*['\"]([^'\"]+)['\"]/",
        "/define\(['\"]APP_DB_NAME['\"],\s*['\"]([^'\"]+)['\"]\)/",
        "/\\\$dbname\s*=\s*['\"]([^'\"]+)['\"]/",
        "/['\"]database['\"]?\s*=>\s*['\"]([^'\"]+)['\"]/",
    ],
    'dbprefix' => [
        "/\\\$db\['default'\]\['dbprefix'\]\s*=\s*['\"]([^'\"]*)['\"]/",
        "/\\\$database\['dbprefix'\]\s*=\s*['\"]([^'\"]*)['\"]/",
        "/define\(['\"]APP_DB_PREFIX['\"],\s*['\"]([^'\"]*)['\"]\)/",
        "/\\\$dbprefix\s*=\s*['\"]([^'\"]*)['\"]/",
        "/['\"]dbprefix['\"]?\s*=>\s*['\"]([^'\"]*)['\"]/",
    ]
];

$config = [];

echo "<h3>Tentando extrair configurações...</h3>";

foreach ($patterns as $key => $pattern_list) {
    $found = false;
    echo "<strong>Buscando $key:</strong><br>";
    
    foreach ($pattern_list as $pattern) {
        if (preg_match($pattern, $db_config_content, $matches)) {
            $config[$key] = $matches[1];
            $found = true;
            echo "<span style='color: green;'>✅ Encontrado: {$matches[1]}</span><br>";
            break;
        }
    }
    
    if (!$found) {
        echo "<span style='color: orange;'>⚠️ Não encontrado</span><br>";
        if ($key !== 'dbprefix' && $key !== 'password') {
            echo "<p>Padrões testados para <strong>$key</strong>:</p><ul>";
            foreach ($pattern_list as $pattern) {
                echo "<li><code>" . htmlspecialchars($pattern) . "</code></li>";
            }
            echo "</ul>";
        }
    }
    echo "<br>";
}

// Valores com fallbacks
$hostname = $config['hostname'] ?? '';
$username = $config['username'] ?? '';
$password = $config['password'] ?? '';
$database = $config['database'] ?? '';
$dbprefix = $config['dbprefix'] ?? 'tbl_';

if (empty($hostname) || empty($username) || empty($database)) {
    echo "<h3>❌ Configurações Críticas Faltando</h3>";
    echo "<p>Por favor, verifique manualmente os arquivos:</p>";
    echo "<ul>";
    echo "<li><strong>database.php:</strong> " . $db_config_file . "</li>";
    echo "<li><strong>app-config.php:</strong> " . ($app_config_file ?? 'N/A') . "</li>";
    echo "</ul>";
    echo "<p><strong>Encontrado:</strong></p>";
    echo "<ul>";
    echo "<li>Hostname: " . ($hostname ?: 'VAZIO') . "</li>";
    echo "<li>Username: " . ($username ?: 'VAZIO') . "</li>";
    echo "<li>Database: " . ($database ?: 'VAZIO') . "</li>";
    echo "<li>Prefix: " . $dbprefix . "</li>";
    echo "</ul>";
    die('Não foi possível conectar ao banco de dados com as configurações encontradas.');
}

// Usar as configurações extraídas
// (as variáveis já foram definidas no processo de extração acima)

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
