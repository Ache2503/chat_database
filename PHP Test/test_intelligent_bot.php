<?php
// test_intelligent_bot.php - Script para probar el bot inteligente
header('Content-Type: text/html; charset=UTF-8');
echo "<html><head><meta charset='UTF-8'></head><body>";
echo "<h2>Probando Bot Inteligente...</h2><br>";

$apiUrl = 'http://localhost/chat%20Database/chat_api_v3.php';

function testMessage($url, $message, $testName) {
    echo "<h3>$testName</h3>";
    echo "<strong>Mensaje:</strong> $message<br>";
    
    $data = [
        'action' => 'sendMessage',
        'userId' => 'test_user_intelligent',
        'message' => $message
    ];
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($data)
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    $result = json_decode($response, true);
    
    if (isset($result['success']) && $result['success']) {
        echo "<strong>Respuesta del bot:</strong> " . $result['botResponse'] . "<br>";
        if (isset($result['sentiment'])) {
            echo "<strong>Sentimiento detectado:</strong> " . $result['sentiment'] . "<br>";
        }
        echo "<span style='color: green;'>✅ Test exitoso</span><br><br>";
    } else {
        echo "<span style='color: red;'>❌ Error: " . ($result['error'] ?? 'Error desconocido') . "</span><br><br>";
    }
}

// Tests con diferentes tipos de mensajes
testMessage($apiUrl, "Hola", "Test 1: Saludo básico");
testMessage($apiUrl, "Buenos días", "Test 2: Saludo matutino");
testMessage($apiUrl, "¿Qué tal el jaripeo?", "Test 3: Pregunta sobre jaripeo");
testMessage($apiUrl, "Me gusta la música ranchera", "Test 4: Música");
testMessage($apiUrl, "Estoy muy feliz hoy", "Test 5: Sentimiento positivo");
testMessage($apiUrl, "Me siento mal", "Test 6: Sentimiento negativo");
testMessage($apiUrl, "¿Conoces los tacos al pastor?", "Test 7: Comida mexicana");
testMessage($apiUrl, "Háblame de la cultura mexicana", "Test 8: Cultura");
testMessage($apiUrl, "¿Qué puedes hacer?", "Test 9: Solicitud de ayuda");
testMessage($apiUrl, "Adiós", "Test 10: Despedida");

echo "</body></html>";
?>
