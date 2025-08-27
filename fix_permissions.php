
<?php
/**
 * Script para corrigir permissões do módulo Timesheet
 * Execute via: /modules/timesheet/fix_permissions.php
 */

// Verificar se estamos no ambiente correto
$perfex_root = dirname(dirname(__DIR__));
$index_file = $perfex_root . '/index.php';

if (!file_exists($index_file)) {
    die('Este script deve ser executado a partir do diretório modules/timesheet/');
}

// Incluir o bootstrap do Perfex
define('BASEPATH', true);
require_once $perfex_root . '/application/config/database.php';

// Configuração básica do banco
$db_config = $db['default'];

try {
    $mysqli = new mysqli(
        $db_config['hostname'], 
        $db_config['username'], 
        $db_config['password'], 
        $db_config['database']
    );

    if ($mysqli->connect_error) {
        die('Erro de conexão: ' . $mysqli->connect_error);
    }

    echo "<h2>Corrigindo Permissões do Módulo Timesheet</h2>";

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

    $mysqli->close();

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}
?>
