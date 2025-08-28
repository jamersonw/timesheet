
<?php
/**
 * Script de Debug para Ativação do Módulo Timesheet
 * Execute este arquivo diretamente no navegador para diagnosticar problemas
 */

// Simular ambiente básico se necessário
if (!defined('BASEPATH')) {
    define('BASEPATH', true);
}

echo "<!DOCTYPE html>";
echo "<html><head><title>Debug - Ativação Módulo Timesheet</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    .debug { color: gray; font-size: 0.9em; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style></head><body>";

echo "<h1>🔧 Debug - Ativação do Módulo Timesheet</h1>";
echo "<p><em>Executado em: " . date('Y-m-d H:i:s') . "</em></p>";

// Função para exibir resultado
function debug_result($message, $status = 'info') {
    $class_map = [
        'success' => 'success',
        'error' => 'error',
        'warning' => 'warning',
        'info' => 'info',
        'debug' => 'debug'
    ];
    
    $class = isset($class_map[$status]) ? $class_map[$status] : 'info';
    echo "<p class='{$class}'>📋 {$message}</p>";
}

// =============================================
// 1. VERIFICAÇÕES BÁSICAS DO PHP
// =============================================
echo "<div class='section'>";
echo "<h2>1️⃣ Verificações Básicas do PHP</h2>";

debug_result("Versão do PHP: " . PHP_VERSION, 'info');
debug_result("Memory Limit: " . ini_get('memory_limit'), 'info');
debug_result("Max Execution Time: " . ini_get('max_execution_time'), 'info');

// Verificar extensões necessárias
$required_extensions = ['mysqli', 'json', 'curl'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        debug_result("Extensão '{$ext}': CARREGADA", 'success');
    } else {
        debug_result("Extensão '{$ext}': NÃO CARREGADA", 'error');
    }
}

echo "</div>";

// =============================================
// 2. VERIFICAÇÃO DE ARQUIVOS
// =============================================
echo "<div class='section'>";
echo "<h2>2️⃣ Verificação de Arquivos do Módulo</h2>";

$required_files = [
    'timesheet.php' => 'Arquivo principal do módulo',
    'install.php' => 'Script de instalação',
    'controllers/Timesheet.php' => 'Controller principal',
    'models/Timesheet_model.php' => 'Model do timesheet',
    'views/my_timesheet.php' => 'View principal'
];

foreach ($required_files as $file => $description) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        $size = filesize($full_path);
        debug_result("{$description} ({$file}): EXISTE ({$size} bytes)", 'success');
        
        // Verificar se é legível
        if (is_readable($full_path)) {
            debug_result("└─ Arquivo é LEGÍVEL", 'debug');
        } else {
            debug_result("└─ Arquivo NÃO É LEGÍVEL", 'error');
        }
    } else {
        debug_result("{$description} ({$file}): NÃO ENCONTRADO", 'error');
    }
}

echo "</div>";

// =============================================
// 3. TESTE DE SINTAXE DOS ARQUIVOS PRINCIPAIS
// =============================================
echo "<div class='section'>";
echo "<h2>3️⃣ Teste de Sintaxe dos Arquivos</h2>";

$files_to_test = ['timesheet.php', 'install.php'];

foreach ($files_to_test as $file) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        // Usar php -l para verificar sintaxe
        $command = "php -l " . escapeshellarg($full_path) . " 2>&1";
        $output = shell_exec($command);
        
        if (strpos($output, 'No syntax errors') !== false) {
            debug_result("Sintaxe do {$file}: OK", 'success');
        } else {
            debug_result("Sintaxe do {$file}: ERRO", 'error');
            echo "<pre>" . htmlspecialchars($output) . "</pre>";
        }
    }
}

echo "</div>";

// =============================================
// 4. VERIFICAÇÃO DE PERMISSÕES DE ARQUIVO
// =============================================
echo "<div class='section'>";
echo "<h2>4️⃣ Verificação de Permissões</h2>";

$module_dir = __DIR__;
debug_result("Diretório do módulo: {$module_dir}", 'info');

if (is_readable($module_dir)) {
    debug_result("Diretório é LEGÍVEL", 'success');
} else {
    debug_result("Diretório NÃO É LEGÍVEL", 'error');
}

if (is_writable($module_dir)) {
    debug_result("Diretório é GRAVÁVEL", 'success');
} else {
    debug_result("Diretório NÃO É GRAVÁVEL (pode afetar logs)", 'warning');
}

echo "</div>";

// =============================================
// 5. TESTE DE LOG
// =============================================
echo "<div class='section'>";
echo "<h2>5️⃣ Teste de Sistema de Log</h2>";

$log_file = $module_dir . '/timesheet_install.log';

try {
    $test_message = "[" . date('Y-m-d H:i:s') . "] [DEBUG] Teste de log - " . uniqid();
    $result = file_put_contents($log_file, $test_message . "\n", FILE_APPEND | LOCK_EX);
    
    if ($result !== false) {
        debug_result("Sistema de log: FUNCIONANDO", 'success');
        debug_result("Arquivo de log: {$log_file}", 'info');
        
        if (file_exists($log_file)) {
            $log_content = file_get_contents($log_file);
            $lines = explode("\n", trim($log_content));
            $recent_lines = array_slice($lines, -10); // Últimas 10 linhas
            
            echo "<h4>📄 Últimas entradas do log:</h4>";
            echo "<pre>" . htmlspecialchars(implode("\n", $recent_lines)) . "</pre>";
        }
    } else {
        debug_result("Sistema de log: FALHOU", 'error');
    }
} catch (Exception $e) {
    debug_result("Sistema de log: ERRO - " . $e->getMessage(), 'error');
}

echo "</div>";

// =============================================
// 6. TESTE DE INCLUSÃO DO INSTALL.PHP
// =============================================
echo "<div class='section'>";
echo "<h2>6️⃣ Teste de Inclusão do install.php</h2>";

try {
    debug_result("Tentando incluir install.php de forma segura...", 'info');
    
    // Verificar se já foi incluído
    $included_files = get_included_files();
    $install_path = realpath(__DIR__ . '/install.php');
    
    if (in_array($install_path, $included_files)) {
        debug_result("install.php já foi incluído anteriormente", 'warning');
    } else {
        debug_result("install.php ainda não foi incluído", 'info');
    }
    
    // Testar inclusão sem executar (apenas parse)
    ob_start();
    $test_include = include(__DIR__ . '/install.php');
    $output = ob_get_clean();
    
    if ($test_include !== false) {
        debug_result("Inclusão do install.php: SUCESSO", 'success');
        
        if (!empty($output)) {
            echo "<h4>📄 Output da inclusão:</h4>";
            echo "<pre>" . htmlspecialchars($output) . "</pre>";
        }
    } else {
        debug_result("Inclusão do install.php: FALHOU", 'error');
    }
    
} catch (Exception $e) {
    debug_result("ERRO ao incluir install.php: " . $e->getMessage(), 'error');
    echo "<pre>Stack trace: " . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "</div>";

// =============================================
// 7. INFORMAÇÕES DO SISTEMA
// =============================================
echo "<div class='section'>";
echo "<h2>7️⃣ Informações do Sistema</h2>";

debug_result("Sistema Operacional: " . PHP_OS, 'info');
debug_result("SAPI: " . php_sapi_name(), 'info');
debug_result("Servidor Web: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Desconhecido'), 'info');
debug_result("Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Desconhecido'), 'info');
debug_result("Script Filename: " . (__FILE__), 'info');

echo "</div>";

// =============================================
// 8. RECOMENDAÇÕES
// =============================================
echo "<div class='section'>";
echo "<h2>8️⃣ Recomendações</h2>";

echo "<ol>";
echo "<li><strong>Logs Detalhados:</strong> Verifique o arquivo <code>timesheet_install.log</code> para logs detalhados da instalação.</li>";
echo "<li><strong>Permissões:</strong> Certifique-se de que o diretório do módulo tenha permissões adequadas.</li>";
echo "<li><strong>PHP Error Log:</strong> Verifique os logs de erro do PHP para mensagens adicionais.</li>";
echo "<li><strong>Perfex Logs:</strong> Verifique os logs de atividade do Perfex CRM em Configurações > Logs de Atividade.</li>";
echo "<li><strong>Banco de Dados:</strong> Verifique se as credenciais do banco estão corretas em <code>application/config/database.php</code>.</li>";
echo "</ol>";

echo "</div>";

echo "<hr>";
echo "<p><em>Debug concluído. Se os problemas persistirem, envie este relatório junto com os logs para suporte.</em></p>";
echo "</body></html>";
?>
