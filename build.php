
<?php
/**
 * Script de Build para Módulo Timesheet
 * Automatiza versionamento, changelog e geração de ZIP
 */

class TimesheetBuilder
{
    private $currentVersion;
    private $newVersion;
    private $changelogPath;
    private $moduleFiles;
    
    public function __construct()
    {
        $this->changelogPath = __DIR__ . '/CHANGELOG.md';
        $this->loadCurrentVersion();
        $this->defineModuleFiles();
    }
    
    private function loadCurrentVersion()
    {
        $timesheetFile = __DIR__ . '/timesheet.php';
        $content = file_get_contents($timesheetFile);
        preg_match('/Version:\s*(\d+\.\d+\.\d+)/', $content, $matches);
        $this->currentVersion = $matches[1] ?? '1.0.0';
    }
    
    private function defineModuleFiles()
    {
        $this->moduleFiles = [
            'assets/',
            'controllers/',
            'helpers/',
            'language/',
            'migrations/',
            'models/',
            'views/',
            'CHANGELOG.md',
            'install.php',
            'timesheet.php'
        ];
    }
    
    public function build($versionType = 'patch', $changeDescription = '')
    {
        echo "🚀 Iniciando build do módulo Timesheet...\n";
        
        // 1. Calcular nova versão
        $this->calculateNewVersion($versionType);
        echo "📦 Versão: {$this->currentVersion} → {$this->newVersion}\n";
        
        // 2. Atualizar arquivos do módulo
        $this->updateModuleVersion();
        
        // 3. Criar migration se necessário
        $this->createMigration();
        
        // 4. Atualizar changelog
        $this->updateChangelog($changeDescription);
        
        // 5. Gerar ZIP
        $zipFile = $this->generateZip();
        
        echo "✅ Build concluído!\n";
        echo "📁 Arquivo: {$zipFile}\n";
        echo "🏷️  Versão: {$this->newVersion}\n";
        
        return $zipFile;
    }
    
    private function calculateNewVersion($type)
    {
        $parts = explode('.', $this->currentVersion);
        $major = (int)$parts[0];
        $minor = (int)$parts[1];
        $patch = (int)$parts[2];
        
        switch ($type) {
            case 'major':
                $major++;
                $minor = 0;
                $patch = 0;
                break;
            case 'minor':
                $minor++;
                $patch = 0;
                break;
            case 'patch':
            default:
                $patch++;
                break;
        }
        
        $this->newVersion = "{$major}.{$minor}.{$patch}";
    }
    
    private function updateModuleVersion()
    {
        // Atualizar timesheet.php
        $timesheetFile = __DIR__ . '/timesheet.php';
        $content = file_get_contents($timesheetFile);
        $content = preg_replace('/Version:\s*\d+\.\d+\.\d+/', "Version: {$this->newVersion}", $content);
        file_put_contents($timesheetFile, $content);
        
        // Atualizar install.php
        $installFile = __DIR__ . '/install.php';
        $content = file_get_contents($installFile);
        $content = preg_replace('/timesheet_module_version.*?\'[\d.]+\'/', "timesheet_module_version', '{$this->newVersion}'", $content);
        file_put_contents($installFile, $content);
        
        echo "📝 Arquivos do módulo atualizados\n";
    }
    
    private function createMigration()
    {
        $versionNumber = str_replace('.', '', $this->newVersion);
        $migrationFile = __DIR__ . "/migrations/{$versionNumber}_version_{$versionNumber}.php";
        
        if (!file_exists($migrationFile)) {
            $migrationContent = $this->generateMigrationContent($versionNumber);
            file_put_contents($migrationFile, $migrationContent);
            echo "🔄 Migration criada: {$versionNumber}_version_{$versionNumber}.php\n";
        }
    }
    
    private function generateMigrationContent($versionNumber)
    {
        return "<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_{$versionNumber} extends App_module_migration
{
    public function up()
    {
        // Atualização para versão {$this->newVersion}
        
        // Atualizar versão do módulo
        if (!get_option('timesheet_module_version')) {
            add_option('timesheet_module_version', '{$this->newVersion}', 1);
        } else {
            update_option('timesheet_module_version', '{$this->newVersion}');
        }
    }

    public function down()
    {
        // Rollback para versão anterior se necessário
        update_option('timesheet_module_version', '{$this->currentVersion}');
    }
}
";
    }
    
    private function updateChangelog($changeDescription)
    {
        $date = date('Y-m-d');
        $changelog = file_get_contents($this->changelogPath);
        
        $newEntry = "\n## [{$this->newVersion}] - {$date}\n\n";
        
        if ($changeDescription) {
            $newEntry .= "### 🔧 ALTERAÇÕES\n";
            $newEntry .= "- {$changeDescription}\n\n";
        } else {
            $newEntry .= "### 🔧 ALTERAÇÕES\n";
            $newEntry .= "- Atualizações e melhorias gerais\n\n";
        }
        
        // Inserir após o cabeçalho
        $lines = explode("\n", $changelog);
        $headerEnd = 0;
        for ($i = 0; $i < count($lines); $i++) {
            if (strpos($lines[$i], '## [') === 0) {
                $headerEnd = $i;
                break;
            }
        }
        
        if ($headerEnd > 0) {
            array_splice($lines, $headerEnd, 0, explode("\n", $newEntry));
            $changelog = implode("\n", $lines);
        } else {
            $changelog .= $newEntry;
        }
        
        file_put_contents($this->changelogPath, $changelog);
        echo "📋 Changelog atualizado\n";
    }
    
    private function generateZip()
    {
        $zipName = "timesheet-v{$this->newVersion}.zip";
        $zipPath = __DIR__ . "/{$zipName}";
        
        // Remover ZIP anterior se existir
        if (file_exists($zipPath)) {
            unlink($zipPath);
        }
        
        $zip = new ZipArchive();
        $result = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        
        if ($result !== TRUE) {
            $error = match($result) {
                ZipArchive::ER_OK => 'No error',
                ZipArchive::ER_MULTIDISK => 'Multi-disk zip archives not supported',
                ZipArchive::ER_RENAME => 'Renaming temporary file failed',
                ZipArchive::ER_CLOSE => 'Closing zip archive failed',
                ZipArchive::ER_SEEK => 'Seek error',
                ZipArchive::ER_READ => 'Read error',
                ZipArchive::ER_WRITE => 'Write error',
                ZipArchive::ER_CRC => 'CRC error',
                ZipArchive::ER_ZIPCLOSED => 'Containing zip archive was closed',
                ZipArchive::ER_NOENT => 'No such file',
                ZipArchive::ER_EXISTS => 'File already exists',
                ZipArchive::ER_OPEN => 'Can not open file',
                ZipArchive::ER_TMPOPEN => 'Failure to create temporary file',
                ZipArchive::ER_ZLIB => 'Zlib error',
                ZipArchive::ER_MEMORY => 'Memory allocation failure',
                ZipArchive::ER_CHANGED => 'Entry has been changed',
                ZipArchive::ER_COMPNOTSUPP => 'Compression method not supported',
                ZipArchive::ER_EOF => 'Premature EOF',
                ZipArchive::ER_INVAL => 'Invalid argument',
                ZipArchive::ER_NOZIP => 'Not a zip archive',
                ZipArchive::ER_INTERNAL => 'Internal error',
                ZipArchive::ER_INCONS => 'Zip archive inconsistent',
                ZipArchive::ER_REMOVE => 'Can not remove file',
                ZipArchive::ER_DELETED => 'Entry has been deleted',
                default => "Unknown error code: {$result}"
            };
            throw new Exception("Não foi possível criar o arquivo ZIP: {$error}");
        }
        
        // Verificar se o diretório está acessível
        if (!is_readable(__DIR__)) {
            throw new Exception("Diretório não acessível para leitura");
        }
        
        $filesAdded = 0;
        foreach ($this->moduleFiles as $file) {
            $fullPath = __DIR__ . '/' . $file;
            
            if (is_file($fullPath)) {
                if (is_readable($fullPath)) {
                    $zip->addFile($fullPath, 'timesheet/' . $file);
                    $filesAdded++;
                    echo "✓ Arquivo adicionado: {$file}\n";
                } else {
                    echo "⚠️  Arquivo não legível: {$file}\n";
                }
            } elseif (is_dir($fullPath)) {
                $dirFilesAdded = $this->addDirectoryToZip($zip, $fullPath, 'timesheet/' . $file);
                $filesAdded += $dirFilesAdded;
                echo "✓ Diretório adicionado: {$file} ({$dirFilesAdded} arquivos)\n";
            } else {
                echo "⚠️  Arquivo/diretório não encontrado: {$file}\n";
            }
        }
        
        if ($filesAdded === 0) {
            throw new Exception("Nenhum arquivo foi adicionado ao ZIP");
        }
        
        if (!$zip->close()) {
            throw new Exception("Erro ao fechar o arquivo ZIP");
        }
        
        // Verificar se o arquivo foi criado
        if (!file_exists($zipPath)) {
            throw new Exception("Arquivo ZIP não foi criado");
        }
        
        $fileSize = filesize($zipPath);
        echo "📦 ZIP gerado: {$zipName} ({$filesAdded} arquivos, " . round($fileSize/1024, 2) . " KB)\n";
        return $zipPath;
    }
    
    private function addFilesToZip($zip, $basePath, $zipPrefix)
    {
        foreach ($this->moduleFiles as $file) {
            $fullPath = $basePath . '/' . $file;
            
            if (is_file($fullPath)) {
                $zip->addFile($fullPath, $zipPrefix . '/' . $file);
            } elseif (is_dir($fullPath)) {
                $this->addDirectoryToZip($zip, $fullPath, $zipPrefix . '/' . $file);
            }
        }
    }
    
    private function addDirectoryToZip($zip, $dirPath, $zipPrefix)
    {
        $filesAdded = 0;
        
        if (!is_dir($dirPath) || !is_readable($dirPath)) {
            echo "⚠️  Diretório não acessível: {$dirPath}\n";
            return $filesAdded;
        }
        
        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($iterator as $file) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($dirPath) + 1);
                
                if ($file->isDir()) {
                    $zip->addEmptyDir($zipPrefix . '/' . $relativePath);
                } elseif ($file->isFile() && is_readable($filePath)) {
                    $zip->addFile($filePath, $zipPrefix . '/' . $relativePath);
                    $filesAdded++;
                }
            }
        } catch (Exception $e) {
            echo "⚠️  Erro ao processar diretório {$dirPath}: " . $e->getMessage() . "\n";
        }
        
        return $filesAdded;
    }
}

// CLI Usage
if (php_sapi_name() === 'cli') {
    $versionType = $argv[1] ?? 'patch';
    $description = $argv[2] ?? '';
    
    $builder = new TimesheetBuilder();
    $builder->build($versionType, $description);
}
?>
