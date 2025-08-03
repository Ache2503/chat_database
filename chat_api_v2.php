<?php
// chat_api.php - Versión actualizada con respuestas desde BD

// Activar reporte de errores para debugging (remover en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers JSON y CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'chat_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error de conexión: ' . $e->getMessage()]);
    exit;
}

// Función mejorada para generar respuestas desde la BD
function generateBotResponse($userMessage, $pdo) {
    $message = strtolower(trim($userMessage));
    
    try {
        // Buscar palabras clave que coincidan con el mensaje
        $stmt = $pdo->prepare("
            SELECT k.keyword, k.category_id, k.priority, c.name as category_name
            FROM bot_keywords k 
            JOIN bot_categories c ON k.category_id = c.id 
            WHERE k.active = 1 AND c.active = 1
            ORDER BY k.priority DESC, LENGTH(k.keyword) DESC
        ");
        $stmt->execute();
        $keywords = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $matchedCategory = null;
        $highestPriority = 0;
        
        // Encontrar la mejor coincidencia
        foreach ($keywords as $keywordData) {
            $keyword = strtolower($keywordData['keyword']);
            if (strpos($message, $keyword) !== false && $keywordData['priority'] >= $highestPriority) {
                $matchedCategory = $keywordData['category_id'];
                $highestPriority = $keywordData['priority'];
            }
        }
        
        if ($matchedCategory) {
            // Obtener respuestas de la categoría encontrada
            $stmt = $pdo->prepare("
                SELECT response_text, emoji 
                FROM bot_responses 
                WHERE category_id = ? AND active = 1
            ");
            $stmt->execute([$matchedCategory]);
            $responses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($responses)) {
                $selectedResponse = $responses[array_rand($responses)];
                
                // Actualizar contador de uso
                $stmt = $pdo->prepare("
                    UPDATE bot_responses 
                    SET usage_count = usage_count + 1 
                    WHERE response_text = ?
                ");
                $stmt->execute([$selectedResponse['response_text']]);
                
                return $selectedResponse['response_text'] . ' ' . $selectedResponse['emoji'];
            }
        }
        
        // Si no hay coincidencia, usar respuesta por defecto
        $stmt = $pdo->prepare("
            SELECT response_text, emoji 
            FROM bot_default_responses 
            WHERE active = 1
        ");
        $stmt->execute();
        $defaultResponses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($defaultResponses)) {
            $selectedResponse = $defaultResponses[array_rand($defaultResponses)];
            
            // Actualizar contador de uso
            $stmt = $pdo->prepare("
                UPDATE bot_default_responses 
                SET usage_count = usage_count + 1 
                WHERE response_text = ?
            ");
            $stmt->execute([$selectedResponse['response_text']]);
            
            return $selectedResponse['response_text'] . ' ' . $selectedResponse['emoji'];
        }
        
        // Fallback si no hay respuestas en BD
        return '¡Qué interesante! 🤔';
        
    } catch (Exception $e) {
        error_log("Error generando respuesta: " . $e->getMessage());
        return '¡Hola! ¿Cómo estás? 😊';
    }
}

// Función para administrar respuestas (agregar/editar/eliminar)
function manageResponses($action, $data, $pdo) {
    switch ($action) {
        case 'addCategory':
            $stmt = $pdo->prepare("INSERT INTO bot_categories (name, description) VALUES (?, ?)");
            return $stmt->execute([$data['name'], $data['description']]);
            
        case 'addKeyword':
            $stmt = $pdo->prepare("INSERT INTO bot_keywords (keyword, category_id, priority) VALUES (?, ?, ?)");
            return $stmt->execute([$data['keyword'], $data['category_id'], $data['priority']]);
            
        case 'addResponse':
            $stmt = $pdo->prepare("INSERT INTO bot_responses (category_id, response_text, emoji) VALUES (?, ?, ?)");
            return $stmt->execute([$data['category_id'], $data['text'], $data['emoji']]);
            
        case 'addDefaultResponse':
            $stmt = $pdo->prepare("INSERT INTO bot_default_responses (response_text, emoji) VALUES (?, ?)");
            return $stmt->execute([$data['response_text'], $data['emoji']]);
            
        case 'deleteResponse':
            $stmt = $pdo->prepare("UPDATE bot_responses SET active = 0 WHERE id = ?");
            return $stmt->execute([$data['id']]);
            
        case 'getCategories':
            $stmt = $pdo->prepare("SELECT * FROM bot_categories WHERE active = 1 ORDER BY name");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        case 'getResponsesByCategory':
            $stmt = $pdo->prepare("
                SELECT r.id, r.response_text as text, r.emoji, c.name as category_name 
                FROM bot_responses r 
                JOIN bot_categories c ON r.category_id = c.id 
                WHERE r.category_id = ? AND r.active = 1
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([$data['category_id']]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        case 'getStats':
            $stmt = $pdo->prepare("
                SELECT 
                    c.name as category,
                    COUNT(r.id) as total_responses,
                    SUM(r.usage_count) as total_usage
                FROM bot_categories c
                LEFT JOIN bot_responses r ON c.id = r.category_id AND r.active = 1
                WHERE c.active = 1
                GROUP BY c.id, c.name
                ORDER BY total_usage DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return false;
}

// Manejar diferentes acciones
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_GET['action'] ?? '';

// Debug: log de la acción recibida
error_log("Acción recibida: " . $action);

// Verificar que tenemos una acción válida
if (empty($action)) {
    echo json_encode(['success' => false, 'error' => 'No se especificó ninguna acción']);
    exit;
}

switch ($action) {
    case 'sendMessage':
        try {
            $userId = $input['userId'] ?? null;
            $userMessage = $input['message'] ?? null;
            
            if (empty($userId) || empty($userMessage)) {
                echo json_encode(['success' => false, 'error' => 'userId y message son requeridos']);
                break;
            }
            
            // Guardar mensaje del usuario
            $stmt = $pdo->prepare("INSERT INTO chat_messages (user_id, message, sender, created_at) VALUES (?, ?, 'user', NOW())");
            $stmt->execute([$userId, $userMessage]);
            
            // Generar respuesta del bot usando la BD
            $botResponse = generateBotResponse($userMessage, $pdo);
            
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
            $userId = $_GET['userId'] ?? null;
            
            if (empty($userId)) {
                echo json_encode(['success' => false, 'error' => 'userId es requerido']);
                break;
            }
            
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
        
    // Nuevas acciones para administrar respuestas
    case 'get_categories':
        try {
            $result = manageResponses('getCategories', [], $pdo);
            echo json_encode(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
        
    case 'add_category':
        try {
            $result = manageResponses('addCategory', $input, $pdo);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Categoría creada correctamente']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error al crear categoría']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
        
    case 'add_keyword':
        try {
            $result = manageResponses('addKeyword', $input, $pdo);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Palabra clave agregada correctamente']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error al agregar palabra clave']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
        
    case 'add_response':
        try {
            $result = manageResponses('addResponse', $input, $pdo);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Respuesta agregada correctamente']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error al agregar respuesta']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
        
    case 'get_responses':
        try {
            $categoryId = $_GET['category_id'] ?? null;
            if ($categoryId) {
                $result = manageResponses('getResponsesByCategory', ['category_id' => $categoryId], $pdo);
                echo json_encode(['success' => true, 'data' => $result]);
            } else {
                echo json_encode(['success' => false, 'error' => 'ID de categoría requerido']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
        
    case 'delete_response':
        try {
            $result = manageResponses('deleteResponse', $input, $pdo);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Respuesta eliminada correctamente']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error al eliminar respuesta']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
        
    case 'get_stats':
        try {
            // Obtener estadísticas básicas
            $stats = [];
            
            // Total de usuarios únicos
            $stmt = $pdo->prepare("SELECT COUNT(DISTINCT user_id) as total_users FROM chat_messages");
            $stmt->execute();
            $stats['total_users'] = $stmt->fetchColumn();
            
            // Usuarios activos hoy
            $stmt = $pdo->prepare("SELECT COUNT(DISTINCT user_id) as active_today FROM chat_messages WHERE DATE(created_at) = CURDATE()");
            $stmt->execute();
            $stats['active_today'] = $stmt->fetchColumn();
            
            // Total de mensajes
            $stmt = $pdo->prepare("SELECT COUNT(*) as total_messages FROM chat_messages");
            $stmt->execute();
            $stats['total_messages'] = $stmt->fetchColumn();
            
            // Tiempo promedio de respuesta (simulado)
            $stats['avg_response_time'] = '1.2';
            
            echo json_encode(['success' => true, 'data' => $stats]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
        
    case 'admin':
        $adminAction = $input['adminAction'] ?? '';
        $result = manageResponses($adminAction, $input['data'] ?? [], $pdo);
        
        if ($result !== false) {
            echo json_encode(['success' => true, 'data' => $result]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error en operación administrativa']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Acción no válida']);
        break;
}
?>