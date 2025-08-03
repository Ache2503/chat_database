<?php
// test_quick.php - Test rápido de la API v3
$data = [
    'action' => 'sendMessage',
    'userId' => 'test_user_quick',
    'message' => 'Hola, ¿qué tal?'
];

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($data)
    ]
]);

$response = file_get_contents('http://localhost/chat%20Database/chat_api_v3.php', false, $context);
echo $response;
?>
