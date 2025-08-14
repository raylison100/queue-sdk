<?php

declare(strict_types=1);

/**
 * Load Test Dashboard Server
 *
 * Serve API endpoints e interface web para o sistema de testes de carga
 */

// Função para buscar arquivos de resultados
function getLatestResults(string $directory): ?array
{
    $files = glob($directory . '/data/results/result-*.json');

    if (empty($files)) {
        return null;
    }

    // Ordenar por data de modificação (mais recente primeiro)
    usort($files, function ($a, $b) {
        return filemtime($b) - filemtime($a);
    });

    $latestFile = $files[0];
    $content = file_get_contents($latestFile);

    if ($content === false) {
        return null;
    }

    return json_decode($content, true);
}

// Função para buscar todos os resultados
function getAllResults(string $directory): array
{
    $files = glob($directory . '/data/results/result-*.json');
    $results = [];

    foreach ($files as $file) {
        $content = file_get_contents($file);
        if ($content !== false) {
            $data = json_decode($content, true);
            if ($data) {
                $results[] = $data;
            }
        }
    }

    // Ordenar por timestamp
    usort($results, function ($a, $b) {
        return strtotime($b['timestamp'] ?? '0') - strtotime($a['timestamp'] ?? '0');
    });

    return $results;
}

// Função para executar testes
function executeTest(string $testType, array $params = []): array
{
    $scriptPath = __DIR__ . '/runner.sh';

    if (!file_exists($scriptPath)) {
        return [
            'success' => false,
            'message' => 'Script runner.sh não encontrado'
        ];
    }

    // Montar argumentos
    $args = [escapeshellarg($testType)];

    // Topic (sempre passar um topic válido)
    $topic = !empty($params['topic']) ? $params['topic'] : "dashboard-{$testType}-" . time();
    $args[] = escapeshellarg($topic);

    // Messages
    $messages = $params['messages'] ?? '10';
    $args[] = escapeshellarg((string)$messages);

    // Batch size
    $batchSize = $params['batchSize'] ?? $params['batch_size'] ?? '5';
    $args[] = escapeshellarg((string)$batchSize);

    // Workers
    $args[] = escapeshellarg((string)($params['workers'] ?? '1'));

    // Timeout
    $args[] = escapeshellarg((string)($params['timeout'] ?? '60'));

    // Queue type
    $queueType = $params['queueType'] ?? $params['queue_type'] ?? 'kafka';
    $args[] = escapeshellarg($queueType);

    $argsStr = implode(' ', $args);

    // Executar o teste em background
    $command = "cd " . escapeshellarg(__DIR__) . " && nohup ./runner.sh $argsStr > /tmp/test-{$topic}.log 2>&1 &";

    shell_exec($command);

    return [
        'success' => true,
        'message' => "Teste iniciado com sucesso",
        'topic' => $topic,
        'params' => $params
    ];
}

// Função para servir dados via HTTP
function serveDashboardData(): void
{
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: Content-Type');

    $directory = __DIR__;
    $latestResult = getLatestResults($directory);
    $allResults = getAllResults($directory);

    if ($latestResult && !empty($allResults)) {
        // Converter formato de dados para o esperado pelo dashboard
        $performanceData = [];
        foreach (array_slice($allResults, 0, 10) as $result) { // Últimos 10 resultados
            $producerDuration = $result['producer']['duration'] ?? 0;
            $consumerDuration = $result['consumer']['duration'] ?? 0;
            $totalDuration = $producerDuration + $consumerDuration;

            $performanceData[] = [
                'topic' => $result['topic'] ?? 'unknown',
                'messages' => $result['config']['messages'] ?? 0,
                'batch' => $result['config']['batch_size'] ?? 0,
                'partitions' => $result['config']['partitions'] ?? 1,
                'workers' => $result['config']['workers'] ?? 1,
                'timeout' => $result['config']['timeout'] ?? 60,
                'producer' => round($result['producer']['rate'] ?? 0, 1), // Coluna Producer (MSG/S)
                'consumer' => round($result['consumer']['rate'] ?? 0, 1), // Coluna Consumer (MSG/S)
                'totalDuration' => round($totalDuration, 2), // Tempo total (Producer + Consumer)
                'efficiency' => $result['efficiency'] ?? 0,
                'errors' => ($result['producer']['errors'] ?? 0) + ($result['consumer']['errors'] ?? 0),
                'timestamp' => $result['timestamp'] ?? ''
            ];
        }

        $dashboard = [
            'connectivity' => [
                'status' => 'passed',
                'kafka' => true,
                'sqs' => true
            ],
            'summary' => [
                'totalTests' => count($allResults),
                'passed' => count(array_filter($allResults, fn($r) => ($r['efficiency'] ?? 0) >= 90)),
                'failed' => count(array_filter($allResults, fn($r) => ($r['efficiency'] ?? 0) < 90)),
                'avgEfficiency' => round(array_sum(array_column($allResults, 'efficiency')) / count($allResults), 1)
            ],
            'performance' => $performanceData,
            'realtime' => [
                'currentTest' => $latestResult['topic'] ?? 'none',
                'status' => 'completed',
                'phase' => 'finished',
                'progress' => 100
            ]
        ];
    } else {
        // Dados padrão quando não há resultados
        $dashboard = [
            'connectivity' => [
                'status' => 'passed',
                'kafka' => true,
                'sqs' => true
            ],
            'summary' => [
                'totalTests' => 0,
                'passed' => 0,
                'failed' => 0,
                'avgEfficiency' => 0
            ],
            'performance' => [],
            'realtime' => [
                'currentTest' => 'none',
                'status' => 'idle',
                'phase' => 'ready',
                'progress' => 0
            ]
        ];
    }

    $response = [
        'success' => true,
        'source' => $latestResult ? 'real' : 'empty',
        'message' => $latestResult ? 'Dados carregados dos arquivos de resultado' : 'Nenhum resultado encontrado',
        'results' => $dashboard,
        'timestamp' => date('c')
    ];

    echo json_encode($response, JSON_PRETTY_PRINT);
}

// Função para gerar arquivo estático de dados
function generateStaticData(): void
{
    $directory = __DIR__;
    $data = getLatestResults($directory);

    $output = [
        'dashboard_data' => $data,
        'generated_at' => date('c'),
        'source' => $data ? 'real' : 'empty'
    ];

    file_put_contents($directory . '/dashboard-data.json', json_encode($output, JSON_PRETTY_PRINT));
    echo "Dashboard data generated at: " . $directory . "/dashboard-data.json\n";
}

// Executar baseado no contexto
if (php_sapi_name() === 'cli') {
    // Executado via linha de comando
    echo "🎯 Queue SDK Load Test Dashboard Server\n";
    echo "=====================================\n\n";

    if (isset($argv[1]) && $argv[1] === 'generate') {
        generateStaticData();
    } else {
        echo "Uso: php server.php generate\n";
        echo "     php -S localhost:8080 server.php\n";
    }
} else {
    // Executado via HTTP
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header('Access-Control-Allow-Headers: Content-Type');

    $requestUri = $_SERVER['REQUEST_URI'];
    $method = $_SERVER['REQUEST_METHOD'];

    // Separar o path dos parâmetros de query
    $parsedUrl = parse_url($requestUri);
    $path = $parsedUrl['path'] ?? '/';

    if ($path === '/api/test-results') {
        serveDashboardData();
    } elseif ($method === 'POST' && ($path === '/api/run-test' || $path === '/server.php' || basename($path) === 'server.php')) {
        // Endpoint para executar testes
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $testType = $input['testType'] ?? $input['test_type'] ?? 'basic';
        $params = $input;

        // Validar tipo de teste
        $validTypes = ['basic', 'simple', 'performance'];
        if (!in_array($testType, $validTypes, true)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Tipo de teste inválido. Use: ' . implode(', ', $validTypes)
            ]);
            exit;
        }

        $result = executeTest($testType, $params);
        echo json_encode($result, JSON_PRETTY_PRINT);
    } elseif ($path === '/api/progress' && isset($_GET['topic']) && isset($_GET['role'])) {
        // Endpoint para progresso em tempo real
        $topic = preg_replace('/[^a-zA-Z0-9\-_]/', '', $_GET['topic']);
        $role = $_GET['role'] === 'consumer' ? 'consumer' : 'producer';
        $file = __DIR__ . "/data/progress/progress-{$topic}-{$role}.json";

        header('Content-Type: application/json');

        if (file_exists($file)) {
            $json = file_get_contents($file);
            $data = json_decode($json, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                echo json_encode([
                    'success' => true,
                    'data' => $data
                ], JSON_PRETTY_PRINT);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Arquivo de progresso corrompido ou inválido'
                ]);
            }
        } else {
            // Retornar 200 com success: false em vez de 404
            echo json_encode([
                'success' => false,
                'status' => 'waiting',
                'message' => "Teste ainda não iniciado ou arquivo de progresso não criado",
                'topic' => $topic,
                'role' => $role
            ]);
        }
    } else {
        // Servir o dashboard HTML
        $dashboardFile = __DIR__ . '/index.html';
        if (file_exists($dashboardFile)) {
            header('Content-Type: text/html');
            readfile($dashboardFile);
        } else {
            header('HTTP/1.1 404 Not Found');
            echo "Dashboard HTML not found";
        }
    }
}
