<?php
// test_api.php - Script para probar la API
echo "Probando API del Chat Bot...\n\n";

$apiUrl = 'http://localhost/chat%20database/chat_api_v3.php';

// Test 1: Verificar que la API responde
echo "Test 1: Verificando conectividad...\n";
$response = file_get_contents($apiUrl . '?action=get_categories');
if ($response === false) {
    echo "❌ Error: No se puede conectar a la API\n";
    echo "Verifica que XAMPP esté ejecutándose y que la ruta sea correcta.\n";
    exit;
}

$data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ Error: La respuesta no es JSON válido\n";
    echo "Respuesta recibida: " . substr($response, 0, 200) . "...\n";
    exit;
}

echo "✅ API responde correctamente\n\n";

// Test 2: Verificar base de datos
echo "Test 2: Verificando base de datos...\n";
if (isset($data['success']) && $data['success']) {
    echo "✅ Base de datos conectada correctamente\n";
    echo "Categorías encontradas: " . count($data['data']) . "\n\n";
} else {
    echo "❌ Error en base de datos: " . ($data['error'] ?? 'Error desconocido') . "\n";
    exit;
}

// Test 3: Probar envío de mensaje
echo "Test 3: Probando envío de mensaje...\n";
$testMessage = [
    'action' => 'sendMessage',
    'userId' => 'test_user_123',
    'message' => 'Hola'
];

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($testMessage)
    ]
]);

$response = file_get_contents($apiUrl, false, $context);
$data = json_decode($response, true);

if (isset($data['success']) && $data['success']) {
    echo "✅ Mensaje enviado correctamente\n";
    echo "Respuesta del bot: " . $data['botResponse'] . "\n\n";
} else {
    echo "❌ Error enviando mensaje: " . ($data['error'] ?? 'Error desconocido') . "\n";
}

// Test 4: Probar historial
echo "Test 4: Probando historial...\n";
$response = file_get_contents($apiUrl . '?action=getHistory&userId=test_user_123');
$data = json_decode($response, true);

if (isset($data['success']) && $data['success']) {
    echo "✅ Historial obtenido correctamente\n";
    echo "Mensajes en historial: " . count($data['messages']) . "\n\n";
} else {
    echo "❌ Error obteniendo historial: " . ($data['error'] ?? 'Error desconocido') . "\n";
}

echo "Pruebas completadas.\n";
echo "Si todos los tests pasan ✅, la API está funcionando correctamente.\n";
echo "Si hay errores ❌, revisa la configuración de XAMPP y la base de datos.\n";
?>
