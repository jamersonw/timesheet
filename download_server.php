
<?php
/**
 * Servidor simples para download do ZIP do módulo
 */

$zipFile = __DIR__ . '/timesheet-v1.3.9.zip';

if (!file_exists($zipFile)) {
    http_response_code(404);
    die('Arquivo ZIP não encontrado. Execute: php build.php patch "Download do módulo"');
}

// Headers para download
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="timesheet-v1.3.9.zip"');
header('Content-Length: ' . filesize($zipFile));
header('Cache-Control: no-cache');

// Enviar arquivo
readfile($zipFile);
exit;
?>
