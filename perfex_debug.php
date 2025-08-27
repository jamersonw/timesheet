
<?php
/**
 * Script de diagnóstico específico para Perfex CRM
 * Acesse via: /modules/timesheet/perfex_debug.php
 */

// Não usar BASEPATH aqui para funcionar independentemente
echo "<h2>Diagnóstico do Perfex CRM - Módulo Timesheet</h2>";
echo "<hr>";

// Verificar se estamos no ambiente Perfex
$perfex_root = dirname(dirname(__DIR__));
$config_file = $perfex_root . '/application/config/config.php';

echo "<h3>1. Verificação do Ambiente</h3>";
echo "<strong>Diretório atual:</strong> " . __DIR__ . "<br>";
echo "<strong>Perfex root:</strong> " . $perfex_root . "<br>";
echo "<strong>Config exists:</strong> " . (file_exists($config_file) ? 'Sim' : 'Não') . "<br>";

// Verificar se podemos carregar o Perfex
if (file_exists($config_file)) {
    echo "<strong>Status:</strong> <span style='color: green;'>Ambiente Perfex detectado</span><br>";
} else {
    echo "<strong>Status:</strong> <span style='color: red;'>Ambiente Perfex NÃO detectado</span><br>";
}

echo "<h3>2. Verificação das Funções do Perfex</h3>";

// Tentar carregar o Perfex minimamente
try {
    if (file_exists($config_file)) {
        // Definir constantes necessárias
        if (!defined('BASEPATH')) {
            define('BASEPATH', true);
        }
        
        // Tentar carregar o index do Perfex
        $index_file = $perfex_root . '/index.php';
        if (file_exists($index_file)) {
            echo "<strong>Index file:</strong> Encontrado<br>";
        }
    }
    
    // Verificar funções críticas
    $functions = [
        'get_instance',
        'log_activity', 
        'get_option',
        'add_option',
        'update_option',
        'db_prefix',
        'add_module_permissions'
    ];
    
    foreach ($functions as $func) {
        $exists = function_exists($func);
        $color = $exists ? 'green' : 'red';
        $status = $exists ? 'OK' : 'NÃO DISPONÍVEL';
        echo "<strong>{$func}():</strong> <span style='color: {$color};'>{$status}</span><br>";
    }
    
} catch (Exception $e) {
    echo "<strong>Erro ao carregar Perfex:</strong> <span style='color: red;'>" . $e->getMessage() . "</span><br>";
}

echo "<h3>3. Teste de Conexão com Banco</h3>";

try {
    // Se conseguirmos carregar o CI
    if (function_exists('get_instance')) {
        $CI = &get_instance();
        if ($CI && isset($CI->db)) {
            echo "<strong>Database:</strong> <span style='color: green;'>Conectado</span><br>";
            
            // Tentar uma query simples
            try {
                $result = $CI->db->query("SELECT 1 as test")->row();
                if ($result && $result->test == 1) {
                    echo "<strong>Query test:</strong> <span style='color: green;'>OK</span><br>";
                } else {
                    echo "<strong>Query test:</strong> <span style='color: red;'>FALHOU</span><br>";
                }
            } catch (Exception $e) {
                echo "<strong>Query test:</strong> <span style='color: red;'>ERRO - " . $e->getMessage() . "</span><br>";
            }
            
            // Verificar prefixo
            if (function_exists('db_prefix')) {
                $prefix = db_prefix();
                echo "<strong>DB Prefix:</strong> " . $prefix . "<br>";
            }
            
        } else {
            echo "<strong>Database:</strong> <span style='color: red;'>NÃO CONECTADO</span><br>";
        }
    } else {
        echo "<strong>Database:</strong> <span style='color: orange;'>NÃO TESTADO (CI não disponível)</span><br>";
    }
    
} catch (Exception $e) {
    echo "<strong>Database:</strong> <span style='color: red;'>ERRO - " . $e->getMessage() . "</span><br>";
}

echo "<h3>4. Verificação de Permissões</h3>";

$files_to_check = [
    __DIR__ . '/install.php',
    __DIR__ . '/timesheet.php',
    __DIR__ . '/controllers/Timesheet.php'
];

foreach ($files_to_check as $file) {
    $readable = is_readable($file);
    $writable = is_writable(dirname($file));
    $exists = file_exists($file);
    
    echo "<strong>" . basename($file) . ":</strong> ";
    
    if (!$exists) {
        echo "<span style='color: red;'>NÃO EXISTE</span>";
    } elseif (!$readable) {
        echo "<span style='color: red;'>NÃO LEGÍVEL</span>";
    } elseif (!$writable) {
        echo "<span style='color: orange;'>DIRETÓRIO NÃO GRAVÁVEL</span>";
    } else {
        echo "<span style='color: green;'>OK</span>";
    }
    echo "<br>";
}

echo "<h3>5. Log de Erros</h3>";

// Verificar logs de erro
$error_log = ini_get('error_log');
if ($error_log && file_exists($error_log)) {
    echo "<strong>Error log:</strong> " . $error_log . "<br>";
    
    // Tentar ler as últimas linhas
    try {
        $lines = file($error_log);
        if ($lines) {
            $recent_lines = array_slice($lines, -10);
            echo "<strong>Últimas 10 linhas:</strong><br>";
            echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 200px; overflow: auto;'>";
            foreach ($recent_lines as $line) {
                if (strpos($line, 'Timesheet') !== false) {
                    echo "<span style='color: red; font-weight: bold;'>" . htmlspecialchars($line) . "</span>";
                } else {
                    echo htmlspecialchars($line);
                }
            }
            echo "</pre>";
        }
    } catch (Exception $e) {
        echo "<span style='color: red;'>Erro ao ler log: " . $e->getMessage() . "</span><br>";
    }
} else {
    echo "<strong>Error log:</strong> Não encontrado ou não configurado<br>";
}

echo "<hr>";
echo "<h3>Recomendações:</h3>";
echo "<ul>";
echo "<li>Se as funções do Perfex não estão disponíveis, o módulo precisa ser ativado através da interface admin do Perfex</li>";
echo "<li>Se há erros de permissão, verifique as permissões do diretório modules/timesheet</li>";
echo "<li>Se há erros de banco, verifique a configuração do database.php no Perfex</li>";
echo "<li>Verifique os logs de erro do PHP para mais detalhes</li>";
echo "</ul>";

echo "<p><em>Este diagnóstico ajuda a identificar problemas específicos do ambiente Perfex.</em></p>";
?>
