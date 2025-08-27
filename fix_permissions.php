
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

// Carregar configuração do banco manualmente
$db_config_file = $perfex_root . '/application/config/database.php';

if (!file_exists($db_config_file)) {
    die('Arquivo de configuração do banco não encontrado');
}

// Definir constantes necessárias antes de carregar o config
if (!defined('BASEPATH')) {
    define('BASEPATH', true);
}
if (!defined('APPPATH')) {
    define('APPPATH', $perfex_root . '/application/');
}
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'production');
}

// Carregar configuração do banco
$db = array();
include $db_config_file;

if (empty($db['default'])) {
    die('Configuração do banco de dados não encontrada');
}

$db_config = $db['default'];

try {
    // Conectar ao banco
    $mysqli = new mysqli(
        $db_config['hostname'], 
        $db_config['username'], 
        $db_config['password'], 
        $db_config['database']
    );

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

    $table_name = $db_config['dbprefix'] . 'staff_permissions';
    
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

    $mysqli->close();

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}
?>
