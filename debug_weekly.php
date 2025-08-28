
<?php
/**
 * Debug específico para a tela de aprovação semanal
 * Execute diretamente no navegador: /debug_weekly.php
 */

echo "<!DOCTYPE html>";
echo "<html><head><title>Debug Aprovação Semanal - Timesheet</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    .warning { color: orange; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style></head><body>";

echo "<h1>🔍 Debug da Tela de Aprovação Semanal</h1>";
echo "<p><em>Executado em: " . date('Y-m-d H:i:s') . "</em></p>";

// Configurações do banco
$hostname = 'localhost';
$username = 'u755875096_jamersonw';
$password = '59886320#Jw';
$database = 'u755875096_perfex';
$table_prefix = 'tbl';

try {
    // Conectar ao banco
    $pdo = new PDO("mysql:host={$hostname};dbname={$database}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p class='success'>✅ Conectado ao banco de dados</p>";
    
    // Obter semana atual
    $week_start = isset($_GET['week']) ? $_GET['week'] : date('Y-m-d', strtotime('monday this week'));
    echo "<h3>📅 Semana analisada: " . $week_start . "</h3>";
    
    // 1. Verificar tabela de aprovações
    echo "<h3>1️⃣ Verificando tabela timesheet_approvals</h3>";
    
    $check_table = $pdo->query("SHOW TABLES LIKE '{$table_prefix}timesheet_approvals'");
    if ($check_table->rowCount() == 0) {
        echo "<p class='error'>❌ Tabela {$table_prefix}timesheet_approvals não encontrada!</p>";
        exit;
    }
    
    echo "<p class='success'>✅ Tabela de aprovações existe</p>";
    
    // 2. Buscar todas as aprovações da semana
    echo "<h3>2️⃣ Buscando aprovações da semana</h3>";
    
    $stmt = $pdo->prepare("
        SELECT ta.*, s.firstname, s.lastname, s.email, p.name as project_name, t.name as task_name 
        FROM {$table_prefix}timesheet_approvals ta
        JOIN {$table_prefix}staff s ON s.staffid = ta.staff_id
        LEFT JOIN {$table_prefix}projects p ON p.id = ta.project_id
        LEFT JOIN {$table_prefix}tasks t ON t.id = ta.task_id
        WHERE ta.week_start_date = ?
        ORDER BY s.firstname, ta.status
    ");
    
    $stmt->execute([$week_start]);
    $all_approvals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p class='info'>📊 Total de aprovações encontradas: " . count($all_approvals) . "</p>";
    
    if (count($all_approvals) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Staff</th><th>Projeto</th><th>Tarefa</th><th>Status</th><th>Submetido em</th></tr>";
        foreach ($all_approvals as $approval) {
            echo "<tr>";
            echo "<td>" . $approval['id'] . "</td>";
            echo "<td>" . $approval['firstname'] . " " . $approval['lastname'] . "</td>";
            echo "<td>" . $approval['project_name'] . "</td>";
            echo "<td>" . $approval['task_name'] . "</td>";
            echo "<td><span class='" . ($approval['status'] == 'pending' ? 'warning' : ($approval['status'] == 'approved' ? 'success' : 'error')) . "'>" . $approval['status'] . "</span></td>";
            echo "<td>" . $approval['submitted_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 3. Simular a query que a função get_weekly_all_approvals faz
    echo "<h3>3️⃣ Simulando query do método get_weekly_all_approvals</h3>";
    
    // Primeiro: buscar funcionários únicos
    $stmt = $pdo->prepare("
        SELECT DISTINCT ta.staff_id, s.firstname, s.lastname, s.email
        FROM {$table_prefix}timesheet_approvals ta
        JOIN {$table_prefix}staff s ON s.staffid = ta.staff_id
        WHERE ta.week_start_date = ?
        AND ta.status IN ('pending', 'approved')
        ORDER BY s.firstname ASC
    ");
    
    $stmt->execute([$week_start]);
    $staff_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p class='info'>👥 Funcionários únicos com aprovações: " . count($staff_list) . "</p>";
    
    $weekly_approvals = [];
    
    foreach ($staff_list as $staff) {
        echo "<h4>👤 Processando: " . $staff['firstname'] . " " . $staff['lastname'] . " (ID: " . $staff['staff_id'] . ")</h4>";
        
        // Buscar tarefas deste funcionário
        $stmt = $pdo->prepare("
            SELECT ta.*, p.name as project_name, t.name as task_name
            FROM {$table_prefix}timesheet_approvals ta
            LEFT JOIN {$table_prefix}projects p ON p.id = ta.project_id
            LEFT JOIN {$table_prefix}tasks t ON t.id = ta.task_id
            WHERE ta.staff_id = ?
            AND ta.week_start_date = ?
            AND ta.status IN ('pending', 'approved')
            ORDER BY ta.status ASC, p.name ASC
        ");
        
        $stmt->execute([$staff['staff_id'], $week_start]);
        $task_approvals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p class='info'>📋 Tarefas encontradas: " . count($task_approvals) . "</p>";
        
        if (count($task_approvals) > 0) {
            echo "<table>";
            echo "<tr><th>Projeto</th><th>Tarefa</th><th>Status</th></tr>";
            
            $pending_count = 0;
            $approved_count = 0;
            
            foreach ($task_approvals as $task) {
                echo "<tr>";
                echo "<td>" . $task['project_name'] . "</td>";
                echo "<td>" . $task['task_name'] . "</td>";
                echo "<td><span class='" . ($task['status'] == 'pending' ? 'warning' : 'success') . "'>" . $task['status'] . "</span></td>";
                echo "</tr>";
                
                if ($task['status'] == 'pending') $pending_count++;
                if ($task['status'] == 'approved') $approved_count++;
            }
            echo "</table>";
            
            // Determinar status consolidado
            $consolidated_status = $pending_count > 0 ? 'pending' : 'approved';
            
            echo "<p class='info'>📈 Status consolidado: <strong>" . $consolidated_status . "</strong></p>";
            echo "<p class='info'>• Pendentes: " . $pending_count . "</p>";
            echo "<p class='info'>• Aprovadas: " . $approved_count . "</p>";
            
            $weekly_approvals[] = [
                'staff_id' => $staff['staff_id'],
                'firstname' => $staff['firstname'],
                'lastname' => $staff['lastname'],
                'email' => $staff['email'],
                'status' => $consolidated_status,
                'total_tasks' => count($task_approvals),
                'pending_tasks' => $pending_count,
                'approved_tasks' => $approved_count
            ];
        }
        
        echo "<hr>";
    }
    
    // 4. Resultado final
    echo "<h3>4️⃣ Resultado Final da Simulação</h3>";
    echo "<p class='success'>✅ Total de aprovações semanais que seriam retornadas: " . count($weekly_approvals) . "</p>";
    
    if (count($weekly_approvals) > 0) {
        echo "<table>";
        echo "<tr><th>Staff</th><th>Email</th><th>Status</th><th>Total Tarefas</th><th>Pendentes</th><th>Aprovadas</th></tr>";
        foreach ($weekly_approvals as $approval) {
            echo "<tr>";
            echo "<td>" . $approval['firstname'] . " " . $approval['lastname'] . "</td>";
            echo "<td>" . $approval['email'] . "</td>";
            echo "<td><span class='" . ($approval['status'] == 'pending' ? 'warning' : 'success') . "'>" . $approval['status'] . "</span></td>";
            echo "<td>" . $approval['total_tasks'] . "</td>";
            echo "<td>" . $approval['pending_tasks'] . "</td>";
            echo "<td>" . $approval['approved_tasks'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 5. Verificar se há entradas na tabela timesheet_entries
    echo "<h3>5️⃣ Verificando entradas do timesheet</h3>";
    
    $stmt = $pdo->prepare("
        SELECT te.*, s.firstname, s.lastname, p.name as project_name, t.name as task_name
        FROM {$table_prefix}timesheet_entries te
        JOIN {$table_prefix}staff s ON s.staffid = te.staff_id
        LEFT JOIN {$table_prefix}projects p ON p.id = te.project_id
        LEFT JOIN {$table_prefix}tasks t ON t.id = te.task_id
        WHERE te.week_start_date = ?
        AND te.hours > 0
        ORDER BY s.firstname, te.day_of_week
    ");
    
    $stmt->execute([$week_start]);
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p class='info'>📝 Entradas de timesheet encontradas: " . count($entries) . "</p>";
    
    if (count($entries) > 0) {
        echo "<details><summary>Ver entradas de timesheet</summary>";
        echo "<table>";
        echo "<tr><th>Staff</th><th>Projeto</th><th>Tarefa</th><th>Dia da Semana</th><th>Horas</th><th>Status</th></tr>";
        foreach ($entries as $entry) {
            echo "<tr>";
            echo "<td>" . $entry['firstname'] . " " . $entry['lastname'] . "</td>";
            echo "<td>" . $entry['project_name'] . "</td>";
            echo "<td>" . $entry['task_name'] . "</td>";
            echo "<td>" . $entry['day_of_week'] . "</td>";
            echo "<td>" . $entry['hours'] . "h</td>";
            echo "<td>" . $entry['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</details>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ ERRO: " . $e->getMessage() . "</p>";
    echo "<p class='info'>💡 Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h3>🔗 Links úteis:</h3>";
echo "<ul>";
echo "<li><a href='?week=" . date('Y-m-d', strtotime($week_start . ' -7 days')) . "'>← Semana anterior</a></li>";
echo "<li><a href='?week=" . date('Y-m-d', strtotime('monday this week')) . "'>Semana atual</a></li>";
echo "<li><a href='?week=" . date('Y-m-d', strtotime($week_start . ' +7 days')) . "'>Semana seguinte →</a></li>";
echo "</ul>";

echo "</body></html>";
?>
