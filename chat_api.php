<?php
// chat_api.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight OPTIONS request para CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Configuración de la base de datos (ajusta según tu configuración de XAMPP)
$host = 'localhost';
$dbname = 'chat_db'; // Nombre de tu base de datos
$username = 'root';
$password = ''; // Por defecto en XAMPP está vacía

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error de conexión: ' . $e->getMessage()]);
    exit;
}

// Función para generar respuestas inteligentes
function generateBotResponse($userMessage) {
    $message = strtolower($userMessage);
    
    // Respuestas contextuales basadas en palabras clave
    $responses = [
        // Saludos
        'hola' => ['¡Hola! ¿Cómo estás? 🤠', '¡Qué tal, compadre! 👋', '¡Hola! ¿En qué te puedo ayudar? 😊'],
        'buenos días' => ['¡Buenos días! ¿Cómo amaneciste? ☀️', '¡Buen día, amigo! 🌅'],
        'buenas tardes' => ['¡Buenas tardes! ¿Qué tal el día? 🌤️', '¡Tarde buena! ¿Cómo vas? 👋'],
        'buenas noches' => ['¡Buenas noches! ¿Cómo estuvo tu día? 🌙', '¡Noche buena! Que descanses 😴'],
        
        // Jaripeo y cultura mexicana
        'jaripeo' => ['¡Viva el jaripeo! 🐂 Una tradición que nos llena de orgullo', '¡El jaripeo es pura adrenalina y tradición! 🎉'],
        'rodeo' => ['¡El rodeo mexicano es el mejor! 🤠', '¡Puro talento y valentía en el rodeo! 🏆'],
        'charreada' => ['¡La charreada es nuestro deporte nacional! 🇲🇽', '¡Viva la charrería mexicana! 🐎'],
        'toro' => ['¡Respeto total por esos toros bravos! 🐂', '¡Los toros son los reyes del ruedo! 💪'],
        
        // Emociones positivas
        'gracias' => ['¡De nada, para eso estamos! 😊', '¡Un placer ayudarte, compadre! 🤝'],
        'excelente' => ['¡Qué bueno que te guste! 🎉', '¡Así me gusta, con esa actitud! 💪'],
        'genial' => ['¡Órale, qué emoción! 🚀', '¡Así se habla! 🔥'],
        
        // Preguntas sobre el sistema
        'ayuda' => ['¡Claro! Estoy aquí para platicar contigo sobre jaripeo y más. ¿Qué te interesa saber? 🤔'],
        'qué puedes hacer' => ['Puedo platicar contigo sobre jaripeo, rodeo, cultura mexicana, ¡y lo que se te ocurra! 🗣️'],
        
        // Respuestas por defecto
        'default' => [
            '¡Qué interesante lo que dices! 🤔',
            '¡Órale, cuéntame más! 👂',
            '¡Así es, compadre! 👍',
            '¡Qué padre está la plática! 💬',
            '¡Sigue contándome! Me gusta escucharte 😊'
        ]
    ];
    
    // Buscar palabras clave en el mensaje
    foreach ($responses as $keyword => $possibleResponses) {
        if ($keyword !== 'default' && strpos($message, $keyword) !== false) {
            return $possibleResponses[array_rand($possibleResponses)];
        }
    }
    
    // Si no encuentra palabras clave específicas, usar respuestas por defecto
    return $responses['default'][array_rand($responses['default'])];
}

// Manejar diferentes acciones
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'sendMessage':
        try {
            $userId = $input['userId'];
            $userMessage = $input['message'];
            
            // Guardar mensaje del usuario
            $stmt = $pdo->prepare("INSERT INTO chat_messages (user_id, message, sender, created_at) VALUES (?, ?, 'user', NOW())");
            $stmt->execute([$userId, $userMessage]);
            
            // Generar respuesta del bot
            $botResponse = generateBotResponse($userMessage);
            
            // Guardar respuesta del bot
            $stmt = $pdo->prepare("INSERT INTO chat_messages (user_id, message, sender, created_at) VALUES (?, ?, 'bot', NOW())");
            $stmt->execute([$userId, $botResponse]);
            
            echo json_encode([
                'success' => true,
                'botResponse' => $botResponse,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
        
    case 'getHistory':
        try {
            $userId = $_GET['userId'];
            
            $stmt = $pdo->prepare("SELECT message, sender, created_at FROM chat_messages WHERE user_id = ? ORDER BY created_at ASC LIMIT 50");
            $stmt->execute([$userId]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'messages' => $messages
            ]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
        
    case 'clearHistory':
        try {
            $userId = $input['userId'];
            
            $stmt = $pdo->prepare("DELETE FROM chat_messages WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            echo json_encode(['success' => true]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Acción no válida']);
        break;
}
?>