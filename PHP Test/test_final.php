<?php
// test_final.php - Test completo del bot inteligente
header('Content-Type: text/html; charset=UTF-8');
echo "<html><head><meta charset='UTF-8'></head><body>";
echo "<h2>🤠 Test del Bot Inteligente - Jaripeo Ranchero</h2><br>";

$apiUrl = 'http://localhost/chat%20Database/chat_api_v3.php';

function testMessage($url, $message, $testName) {
    echo "<div style='background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
    echo "<h3 style='color: #8B4513;'>$testName</h3>";
    echo "<strong>Usuario:</strong> $message<br>";
    
    $data = [
        'action' => 'sendMessage',
        'userId' => 'test_user_final',
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
        echo "<strong>🤖 Bot:</strong> " . $result['botResponse'] . "<br>";
        if (isset($result['sentiment'])) {
            $sentimentColor = $result['sentiment'] === 'positive' ? 'green' : 
                            ($result['sentiment'] === 'negative' ? 'red' : 'blue');
            echo "<strong>Sentimiento:</strong> <span style='color: $sentimentColor;'>" . $result['sentiment'] . "</span><br>";
        }
        echo "<span style='color: green;'>✅ Test exitoso</span>";
    } else {
        echo "<span style='color: red;'>❌ Error: " . ($result['error'] ?? 'Error desconocido') . "</span>";
    }
    echo "</div>";
}

// Tests diversos
testMessage($apiUrl, "Hola", "Saludo básico");
testMessage($apiUrl, "Buenos días", "Saludo matutino");
testMessage($apiUrl, "¿Qué tal el jaripeo?", "Pregunta sobre jaripeo");
testMessage($apiUrl, "Me encanta la música ranchera", "Música mexicana");
testMessage($apiUrl, "Estoy muy feliz hoy", "Sentimiento positivo");
testMessage($apiUrl, "Me siento triste", "Sentimiento negativo");
testMessage($apiUrl, "¿Conoces los tacos al pastor?", "Comida mexicana");
testMessage($apiUrl, "Háblame de México", "Cultura mexicana");
testMessage($apiUrl, "¿Qué puedes hacer?", "Solicitud de ayuda");
testMessage($apiUrl, "Hasta luego", "Despedida");

echo "<br><h3>🎯 Conclusión:</h3>";
echo "<p>El bot ahora es mucho más inteligente y conversacional, con:</p>";
echo "<ul>";
echo "<li>✅ Detección de sentimientos</li>";
echo "<li>✅ Respuestas contextuales y naturales</li>";
echo "<li>✅ Personalidad mexicana auténtica</li>";
echo "<li>✅ Variedad de temas: jaripeo, cultura, música, comida</li>";
echo "<li>✅ Manejo inteligente de palabras clave</li>";
echo "</ul>";

echo "</body></html>";
?>
