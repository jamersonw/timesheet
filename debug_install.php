
<?php
/**
 * Script de debug para testar a instalação do módulo Timesheet
 * Acesse via: /modules/timesheet/debug_install.php
 */

// Simular ambiente do Perfex CRM
define('BASEPATH', true);

// Função para simular logs
function log_activity($message) {
    echo "[" . date('Y-m-d H:i:s') . "] " . $message . "<br>\n";
    flush();
}

// Função para simular get_option
function get_option($option) {
    return false; // Simula que não existe
}

// Função para simular add_option
function add_option($option, $value, $autoload = 0) {
    echo "ADD_OPTION: $option = $value<br>\n";
    return true;
}

// Função para simular update_option
function update_option($option, $value) {
    echo "UPDATE_OPTION: $option = $value<br>\n";
    return true;
}

// Função para simular add_module_permissions
function add_module_permissions($module, $permissions) {
    echo "ADD_PERMISSIONS: $module - " . count($permissions) . " permissões<br>\n";
    foreach ($permissions as $perm) {
        echo "  - " . $perm['name'] . " (" . $perm['short_name'] . ")<br>\n";
    }
    return true;
}

// Função para simular db_prefix
function db_prefix() {
    return 'tbl_';
}

// Simular CI
class MockDB {
    public $char_set = 'utf8';
    
    public function table_exists($table) {
        echo "CHECK_TABLE: $table<br>\n";
        return false; // Simula que não existe
    }
    
    public function query($sql) {
        echo "EXECUTE_SQL: " . substr($sql, 0, 100) . "...<br>\n";
        return true;
    }
    
    public function error() {
        return ['message' => 'No error'];
    }
}

class MockCI {
    public $db;
    
    public function __construct() {
        $this->db = new MockDB();
    }
}

$CI = new MockCI();

echo "<h2>Debug da Instalação do Módulo Timesheet</h2>";
echo "<hr>";

try {
    echo "<h3>Testando install.php...</h3>";
    require_once(__DIR__ . '/install.php');
    echo "<h3 style='color: green;'>✅ Instalação testada com sucesso!</h3>";
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Erro durante a instalação:</h3>";
    echo "<strong>Mensagem:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Arquivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Linha:</strong> " . $e->getLine() . "<br>";
    echo "<strong>Stack Trace:</strong><br><pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><em>Este é um teste simulado. Use este script para identificar problemas antes de ativar o módulo no Perfex.</em></p>";
?>
