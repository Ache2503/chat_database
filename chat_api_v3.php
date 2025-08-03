<?php
// chat_api_v3.php - Versión inteligente del chatbot

// Activar reporte de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers JSON y CORS
header('Content-Type: application/json; charset=utf-8');
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
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4");
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error de conexión: ' . $e->getMessage()]);
    exit;
}

// Clase para el bot inteligente
class IntelligentBot {
    private $pdo;
    private $userId;
    private $conversationContext;
    
    public function __construct($pdo, $userId) {
        $this->pdo = $pdo;
        $this->userId = $userId;
        $this->loadConversationContext();
    }
    
    // Cargar contexto de conversación del usuario
    private function loadConversationContext() {
        $stmt = $this->pdo->prepare("
            SELECT * FROM bot_conversation_context 
            WHERE user_id = ? 
            ORDER BY updated_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$this->userId]);
        $this->conversationContext = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$this->conversationContext) {
            // Crear nuevo contexto
            $stmt = $this->pdo->prepare("
                INSERT INTO bot_conversation_context (user_id, conversation_state) 
                VALUES (?, 'normal')
            ");
            $stmt->execute([$this->userId]);
            $this->loadConversationContext();
        }
    }
    
    // Actualizar contexto de conversación
    private function updateContext($category = null, $state = 'normal', $preferences = null) {
        $stmt = $this->pdo->prepare("
            UPDATE bot_conversation_context 
            SET last_category = ?, conversation_state = ?, user_preferences = ?, updated_at = NOW()
            WHERE user_id = ?
        ");
        $stmt->execute([$category, $state, $preferences, $this->userId]);
    }
    
    // Detectar sentimiento básico del mensaje
    private function detectSentiment($message) {
        $message = strtolower($message);
        
        $positive = ['genial', 'excelente', 'fantástico', 'perfecto', 'increíble', 'chido', 'padre', 'bueno', 'bien', 'feliz', 'contento'];
        $negative = ['mal', 'terrible', 'horrible', 'triste', 'enojado', 'molesto', 'feo', 'malo', 'pésimo'];
        
        foreach ($positive as $word) {
            if (strpos($message, $word) !== false) return 'positive';
        }
        
        foreach ($negative as $word) {
            if (strpos($message, $word) !== false) return 'negative';
        }
        
        return 'neutral';
    }
    
    // Obtener respuesta basada en tiempo
    private function getTimeBasedResponse($categoryId) {
        $hour = (int)date('H');
        
        if ($hour >= 5 && $hour < 12) {
            $timeCondition = 'morning';
        } elseif ($hour >= 12 && $hour < 17) {
            $timeCondition = 'afternoon';
        } elseif ($hour >= 17 && $hour < 21) {
            $timeCondition = 'evening';
        } else {
            $timeCondition = 'night';
        }
        
        $stmt = $this->pdo->prepare("
            SELECT response_text, emoji 
            FROM bot_time_responses 
            WHERE category_id = ? AND time_condition = ? AND active = 1
        ");
        $stmt->execute([$categoryId, $timeCondition]);
        $timeResponses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($timeResponses)) {
            return $timeResponses[array_rand($timeResponses)];
        }
        
        return null;
    }
    
    // Generar respuesta contextual inteligente
    public function generateResponse($userMessage) {
        $message = strtolower(trim($userMessage));
        $sentiment = $this->detectSentiment($message);
        
        try {
            // Buscar palabras clave más sofisticadas
            $stmt = $this->pdo->prepare("
                SELECT k.keyword, k.category_id, k.priority, c.name as category_name,
                       CHAR_LENGTH(k.keyword) as keyword_length
                FROM bot_keywords k 
                JOIN bot_categories c ON k.category_id = c.id 
                WHERE k.active = 1 AND c.active = 1
                ORDER BY k.priority DESC, keyword_length DESC
            ");
            $stmt->execute();
            $keywords = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $matchedCategory = null;
            $highestScore = 0;
            $matchedKeyword = '';
            
            // Algoritmo mejorado de coincidencia
            foreach ($keywords as $keywordData) {
                $keyword = strtolower($keywordData['keyword']);
                $score = 0;
                
                // Coincidencia exacta
                if (strpos($message, $keyword) !== false) {
                    $score = $keywordData['priority'] + strlen($keyword);
                    
                    // Bonus por coincidencia al inicio del mensaje
                    if (strpos($message, $keyword) === 0) {
                        $score += 5;
                    }
                    
                    // Bonus por coincidencia de palabra completa
                    if (preg_match('/\b' . preg_quote($keyword, '/') . '\b/', $message)) {
                        $score += 3;
                    }
                    
                    if ($score > $highestScore) {
                        $matchedCategory = $keywordData['category_id'];
                        $highestScore = $score;
                        $matchedKeyword = $keyword;
                    }
                }
            }
            
            if ($matchedCategory) {
                // Primero intentar respuesta basada en tiempo para saludos
                if ($matchedCategory == 1) { // Saludos
                    // Temporalmente deshabilitado hasta corregir tabla bot_time_responses
                    // $timeResponse = $this->getTimeBasedResponse($matchedCategory);
                    // if ($timeResponse) {
                    //     $this->updateContext($matchedCategory);
                    //     return $timeResponse['response_text'] . ' ' . $timeResponse['emoji'];
                    // }
                }
                
                // Obtener respuestas normales de la categoría
                $stmt = $this->pdo->prepare("
                    SELECT response_text, emoji, id
                    FROM bot_responses 
                    WHERE category_id = ? AND active = 1
                ");
                $stmt->execute([$matchedCategory]);
                $responses = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($responses)) {
                    // Selección inteligente basada en contexto previo
                    $selectedResponse = $this->selectBestResponse($responses, $sentiment);
                    
                    // Actualizar contador de uso
                    $stmt = $this->pdo->prepare("
                        UPDATE bot_responses 
                        SET usage_count = usage_count + 1 
                        WHERE response_text = ?
                    ");
                    $stmt->execute([$selectedResponse['response_text']]);
                    
                    // Actualizar contexto
                    $this->updateContext($matchedCategory);
                    
                    $finalResponse = $selectedResponse['response_text'] . ' ' . $selectedResponse['emoji'];
                    
                    // Posibilidad de agregar respuesta de seguimiento
                    $followup = $this->getFollowupResponse($matchedCategory, $selectedResponse['id']);
                    if ($followup) {
                        $finalResponse .= "\n\n" . $followup;
                    }
                    
                    return $finalResponse;
                }
            }
            
            // Respuesta por defecto más inteligente
            return $this->getSmartDefaultResponse($sentiment, $message);
            
        } catch (Exception $e) {
            error_log("Error generando respuesta: " . $e->getMessage());
            return $this->getEmergencyResponse();
        }
    }
    
    // Seleccionar mejor respuesta basada en sentimiento
    private function selectBestResponse($responses, $sentiment) {
        // Si hay respuestas múltiples, seleccionar basado en contexto
        if (count($responses) > 1) {
            // Filtrar por contexto previo si es necesario
            $lastCategory = $this->conversationContext['last_category'] ?? null;
            
            // Por ahora, selección aleatoria mejorada
            return $responses[array_rand($responses)];
        }
        
        return $responses[0];
    }
    
    // Obtener respuesta de seguimiento
    private function getFollowupResponse($categoryId, $responseId) {
        $stmt = $this->pdo->prepare("
            SELECT followup_text, probability 
            FROM bot_followup_responses 
            WHERE category_id = ? AND trigger_response_id = ? AND active = 1
        ");
        $stmt->execute([$categoryId, $responseId]);
        $followups = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($followups)) {
            $followup = $followups[array_rand($followups)];
            // Usar probabilidad para decidir si mostrar followup
            if (mt_rand(1, 100) <= ($followup['probability'] * 100)) {
                return $followup['followup_text'];
            }
        }
        
        return null;
    }
    
    // Respuesta por defecto más inteligente
    private function getSmartDefaultResponse($sentiment, $message) {
        // Respuestas específicas por sentimiento
        if ($sentiment === 'positive') {
            $positiveResponses = [
                '¡Órale, qué buena vibra traes! Me contagias tu energía positiva 😄',
                '¡Eso me gusta escuchar! Con esa actitud se puede con todo 💪',
                '¡Padrísimo! Me da mucho gusto que estés contento, compadre 🎉'
            ];
            return $positiveResponses[array_rand($positiveResponses)];
        }
        
        if ($sentiment === 'negative') {
            $supportiveResponses = [
                'Órale, parece que no andas muy bien. ¿Quieres platicar de algo que te anime? 🤗',
                'A veces los días están difíciles, pero todo pasa, hermano. ¿Te puedo ayudar en algo? 💙',
                'No te desanimes, compadre. ¿Qué tal si hablamos de algo que te guste? 🌟'
            ];
            return $supportiveResponses[array_rand($supportiveResponses)];
        }
        
        // Respuesta neutral con sugerencias específicas
        $stmt = $this->pdo->prepare("
            SELECT response_text, emoji 
            FROM bot_default_responses 
            WHERE active = 1
        ");
        $stmt->execute();
        $defaultResponses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($defaultResponses)) {
            $selectedResponse = $defaultResponses[array_rand($defaultResponses)];
            return $selectedResponse['response_text'] . ' ' . $selectedResponse['emoji'];
        }
        
        return $this->getEmergencyResponse();
    }
    
    // Respuesta de emergencia
    private function getEmergencyResponse() {
        return '¡Órale! Algo se trabó por aquí, pero ya estoy de vuelta. ¿En qué te puedo ayudar, compadre? 🤠';
    }
    
    // Obtener historial con análisis
    public function getConversationHistory($limit = 10) {
        $limit = (int)$limit; // Asegurar que sea entero
        $stmt = $this->pdo->prepare("
            SELECT message, sender, created_at, sentiment, context_data 
            FROM chat_messages 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT " . $limit
        );
        $stmt->execute([$this->userId]);
        return array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}

// Manejar diferentes acciones
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_GET['action'] ?? '';

// Debug: log de la acción recibida
error_log("Acción recibida: " . $action);

switch ($action) {
    case 'sendMessage':
        try {
            $userId = $input['userId'] ?? null;
            $userMessage = $input['message'] ?? null;
            
            if (empty($userId) || empty($userMessage)) {
                echo json_encode(['success' => false, 'error' => 'userId y message son requeridos']);
                break;
            }
            
            // Crear instancia del bot inteligente
            $bot = new IntelligentBot($pdo, $userId);
            
            // Detectar sentimiento
            $sentiment = 'neutral';
            $message_lower = strtolower($userMessage);
            $positive_words = ['genial', 'excelente', 'fantástico', 'perfecto', 'chido', 'padre', 'bueno', 'bien'];
            $negative_words = ['mal', 'terrible', 'horrible', 'triste', 'enojado', 'molesto'];
            
            foreach ($positive_words as $word) {
                if (strpos($message_lower, $word) !== false) {
                    $sentiment = 'positive';
                    break;
                }
            }
            
            if ($sentiment === 'neutral') {
                foreach ($negative_words as $word) {
                    if (strpos($message_lower, $word) !== false) {
                        $sentiment = 'negative';
                        break;
                    }
                }
            }
            
            // Guardar mensaje del usuario con contexto
            $stmt = $pdo->prepare("
                INSERT INTO chat_messages (user_id, message, sender, sentiment, created_at) 
                VALUES (?, ?, 'user', ?, NOW())
            ");
            $stmt->execute([$userId, $userMessage, $sentiment]);
            
            // Generar respuesta inteligente
            $botResponse = $bot->generateResponse($userMessage);
            
            // Guardar respuesta del bot
            $stmt = $pdo->prepare("
                INSERT INTO chat_messages (user_id, message, sender, sentiment, created_at) 
                VALUES (?, ?, 'bot', 'positive', NOW())
            ");
            $stmt->execute([$userId, $botResponse]);
            
            echo json_encode([
                'success' => true,
                'botResponse' => $botResponse,
                'timestamp' => date('Y-m-d H:i:s'),
                'sentiment' => $sentiment
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
            
            $bot = new IntelligentBot($pdo, $userId);
            $messages = $bot->getConversationHistory(50);
            
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
            
            // Limpiar también el contexto
            $stmt = $pdo->prepare("DELETE FROM bot_conversation_context WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            echo json_encode(['success' => true]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
        
    // Mantener otras acciones de administración
    case 'get_categories':
        try {
            $stmt = $pdo->prepare("SELECT * FROM bot_categories WHERE active = 1 ORDER BY name");
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $result]);
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
        
    case 'get_table_data':
        try {
            $tableName = $_GET['table'] ?? '';
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 25);
            $search = $_GET['search'] ?? '';
            
            if (empty($tableName)) {
                echo json_encode(['success' => false, 'error' => 'Tabla no especificada']);
                break;
            }
            
            // Validar que la tabla existe y es segura
            $allowedTables = [
                'chat_messages', 'bot_categories', 'bot_keywords', 
                'bot_responses', 'bot_default_responses', 'chat_users'
            ];
            
            if (!in_array($tableName, $allowedTables)) {
                echo json_encode(['success' => false, 'error' => 'Tabla no permitida']);
                break;
            }
            
            $offset = ($page - 1) * $limit;
            
            // Construir consulta con búsqueda si es necesaria
            $whereClause = '';
            $params = [];
            
            if (!empty($search)) {
                // Obtener columnas de la tabla
                $stmt = $pdo->prepare("DESCRIBE `$tableName`");
                $stmt->execute();
                $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                $searchConditions = [];
                foreach ($columns as $column) {
                    $searchConditions[] = "`$column` LIKE ?";
                    $params[] = "%$search%";
                }
                $whereClause = " WHERE " . implode(" OR ", $searchConditions);
            }
            
            // Obtener total de registros
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM `$tableName`" . $whereClause);
            $stmt->execute($params);
            $totalRecords = $stmt->fetchColumn();
            
            // Obtener datos paginados
            $stmt = $pdo->prepare("SELECT * FROM `$tableName`" . $whereClause . " ORDER BY id DESC LIMIT $limit OFFSET $offset");
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Obtener información de la tabla
            $stmt = $pdo->prepare("DESCRIBE `$tableName`");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $data,
                'columns' => $columns,
                'pagination' => [
                    'current_page' => $page,
                    'total_records' => $totalRecords,
                    'total_pages' => ceil($totalRecords / $limit),
                    'limit' => $limit
                ]
            ]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
        
    case 'get_table_stats':
        try {
            $tableName = $_GET['table'] ?? '';
            
            if (empty($tableName)) {
                echo json_encode(['success' => false, 'error' => 'Tabla no especificada']);
                break;
            }
            
            $allowedTables = [
                'chat_messages', 'bot_categories', 'bot_keywords', 
                'bot_responses', 'bot_default_responses', 'chat_users'
            ];
            
            if (!in_array($tableName, $allowedTables)) {
                echo json_encode(['success' => false, 'error' => 'Tabla no permitida']);
                break;
            }
            
            $stats = [];
            
            // Total de registros
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM `$tableName`");
            $stmt->execute();
            $stats['total_records'] = $stmt->fetchColumn();
            
            // Registros activos (si existe columna active)
            $stmt = $pdo->prepare("SHOW COLUMNS FROM `$tableName` LIKE 'active'");
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM `$tableName` WHERE active = 1");
                $stmt->execute();
                $stats['active_records'] = $stmt->fetchColumn();
            }
            
            // Fecha del último registro
            $stmt = $pdo->prepare("SHOW COLUMNS FROM `$tableName` LIKE 'created_at'");
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $stmt = $pdo->prepare("SELECT MAX(created_at) FROM `$tableName`");
                $stmt->execute();
                $stats['last_record'] = $stmt->fetchColumn();
            }
            
            echo json_encode(['success' => true, 'data' => $stats]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
        
    case 'global_search':
        try {
            $query = $_GET['q'] ?? '';
            $type = $_GET['type'] ?? 'all';
            
            if (strlen($query) < 2) {
                echo json_encode(['success' => true, 'data' => []]);
                break;
            }
            
            $results = [];
            $searchTerm = "%$query%";
            
            // Buscar en respuestas del bot
            if ($type === 'all' || $type === 'responses') {
                $stmt = $pdo->prepare("
                    SELECT r.id, r.response_text, r.emoji, c.name as category_name, 'response' as type
                    FROM bot_responses r 
                    JOIN bot_categories c ON r.category_id = c.id 
                    WHERE r.response_text LIKE ? AND r.active = 1
                    LIMIT 10
                ");
                $stmt->execute([$searchTerm]);
                $responses = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($responses as $response) {
                    $results[] = [
                        'type' => 'Respuesta',
                        'title' => substr($response['response_text'], 0, 100) . '...',
                        'content' => "Categoría: {$response['category_name']} | Emoji: {$response['emoji']}",
                        'id' => $response['id'],
                        'table' => 'bot_responses'
                    ];
                }
            }
            
            // Buscar en palabras clave
            if ($type === 'all' || $type === 'keywords') {
                $stmt = $pdo->prepare("
                    SELECT k.id, k.keyword, k.priority, c.name as category_name, 'keyword' as type
                    FROM bot_keywords k 
                    JOIN bot_categories c ON k.category_id = c.id 
                    WHERE k.keyword LIKE ? AND k.active = 1
                    LIMIT 10
                ");
                $stmt->execute([$searchTerm]);
                $keywords = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($keywords as $keyword) {
                    $results[] = [
                        'type' => 'Palabra Clave',
                        'title' => $keyword['keyword'],
                        'content' => "Categoría: {$keyword['category_name']} | Prioridad: {$keyword['priority']}",
                        'id' => $keyword['id'],
                        'table' => 'bot_keywords'
                    ];
                }
            }
            
            // Buscar en categorías
            if ($type === 'all' || $type === 'categories') {
                $stmt = $pdo->prepare("
                    SELECT id, name, description, 'category' as type
                    FROM bot_categories 
                    WHERE (name LIKE ? OR description LIKE ?) AND active = 1
                    LIMIT 10
                ");
                $stmt->execute([$searchTerm, $searchTerm]);
                $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($categories as $category) {
                    $results[] = [
                        'type' => 'Categoría',
                        'title' => $category['name'],
                        'content' => $category['description'],
                        'id' => $category['id'],
                        'table' => 'bot_categories'
                    ];
                }
            }
            
            // Buscar en mensajes del chat
            if ($type === 'all' || $type === 'messages') {
                $stmt = $pdo->prepare("
                    SELECT id, user_id, message, sender, created_at, 'message' as type
                    FROM chat_messages 
                    WHERE message LIKE ?
                    ORDER BY created_at DESC
                    LIMIT 10
                ");
                $stmt->execute([$searchTerm]);
                $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($messages as $message) {
                    $results[] = [
                        'type' => 'Mensaje',
                        'title' => substr($message['message'], 0, 100) . '...',
                        'content' => "Usuario: {$message['user_id']} | Enviado por: {$message['sender']} | Fecha: " . date('d/m/Y H:i', strtotime($message['created_at'])),
                        'id' => $message['id'],
                        'table' => 'chat_messages'
                    ];
                }
            }
            
            echo json_encode(['success' => true, 'data' => $results, 'query' => $query]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Acción no válida']);
        break;
}
?>
