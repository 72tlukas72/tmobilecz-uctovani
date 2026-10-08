<?php
header('Content-Type: application/json; charset=utf-8');

$dataDir = __DIR__ . '/data';

// Vytvoření adresáře data, pokud neexistuje
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

$method = $_SERVER['REQUEST_METHOD'];

// NAČTENÍ VYÚČTOVÁNÍ PODLE ID (GET request)
if ($method === 'GET') {
    $id = isset($_GET['id']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_GET['id']) : '';
    
    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Chybí parametry ID']);
        exit;
    }

    $filePath = $dataDir . '/' . $id . '.json';
    if (file_exists($filePath)) {
        echo file_get_contents($filePath);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Vyúčtování nenalezeno']);
    }
    exit;
}

// ULOŽENÍ NOVÉHO VYÚČTOVÁNÍ (POST request)
if ($method === 'POST') {
    $input = file_get_contents('php://input');
    $decoded = json_decode($input, true);

    if (!$decoded || !isset($decoded['participants'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Neplatná struktura dat']);
        exit;
    }

    // Vygenerování 10místného náhodného ID
    $id = bin2hex(random_bytes(5));
    $filePath = $dataDir . '/' . $id . '.json';

    if (file_put_contents($filePath, json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        echo json_encode(['status' => 'ok', 'id' => $id]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Chyba při zápisu do souboru']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Metoda není podporována']);