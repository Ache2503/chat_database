-- Script para crear la base de datos y tabla del chat
-- Ejecuta esto en phpMyAdmin o tu cliente MySQL

-- Crear la base de datos (opcional si ya tienes una)
CREATE DATABASE IF NOT EXISTS chat_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Usar la base de datos
USE chat_db;

-- Crear tabla para mensajes del chat
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    sender ENUM('user', 'bot') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Opcional: Crear tabla para usuarios (para funcionalidad avanzada)
CREATE TABLE IF NOT EXISTS chat_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100),
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Script SQL para crear sistema de respuestas del bot

-- Tabla para categorías de respuestas
CREATE TABLE IF NOT EXISTS bot_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para palabras clave que activan respuestas
CREATE TABLE IF NOT EXISTS bot_keywords (
    id INT AUTO_INCREMENT PRIMARY KEY,
    keyword VARCHAR(100) NOT NULL,
    category_id INT,
    priority INT DEFAULT 1, -- Mayor número = mayor prioridad
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES bot_categories(id) ON DELETE CASCADE,
    INDEX idx_keyword (keyword),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para las respuestas del bot
CREATE TABLE IF NOT EXISTS bot_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    response_text TEXT NOT NULL,
    emoji VARCHAR(20),
    active BOOLEAN DEFAULT TRUE,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES bot_categories(id) ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_usage (usage_count)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para respuestas por defecto cuando no hay coincidencias
CREATE TABLE IF NOT EXISTS bot_default_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    response_text TEXT NOT NULL,
    emoji VARCHAR(20),
    active BOOLEAN DEFAULT TRUE,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar categorías iniciales
INSERT INTO bot_categories (name, description) VALUES 
('saludos', 'Respuestas para saludos y bienvenidas'),
('jaripeo', 'Respuestas relacionadas con jaripeo y rodeo'),
('cultura_mexicana', 'Respuestas sobre cultura y tradiciones mexicanas'),
('emociones_positivas', 'Respuestas para expresiones positivas'),
('despedidas', 'Respuestas para despedidas'),
('preguntas_ayuda', 'Respuestas para solicitudes de ayuda'),
('comida', 'Respuestas sobre comida mexicana'),
('musica', 'Respuestas sobre música y artistas'),
('deportes', 'Respuestas sobre deportes mexicanos');

-- Insertar palabras clave para saludos
INSERT INTO bot_keywords (keyword, category_id, priority) VALUES 
('hola', 1, 5),
('buenos días', 1, 5),
('buenas tardes', 1, 4),
('buenas noches', 1, 4),
('qué tal', 1, 3),
('cómo estás', 1, 3),
('saludos', 1, 2);

-- Insertar palabras clave para jaripeo
INSERT INTO bot_keywords (keyword, category_id, priority) VALUES 
('jaripeo', 2, 5),
('rodeo', 2, 5),
('charreada', 2, 4),
('toro', 2, 4),
('jinete', 2, 4),
('vaquero', 2, 3),
('caballo', 2, 3),
('ruedo', 2, 3);

-- Insertar palabras clave para cultura mexicana
INSERT INTO bot_keywords (keyword, category_id, priority) VALUES 
('méxico', 3, 5),
('mexicano', 3, 4),
('tradición', 3, 3),
('fiesta', 3, 3),
('cultura', 3, 3);

-- Insertar palabras clave para emociones positivas
INSERT INTO bot_keywords (keyword, category_id, priority) VALUES 
('gracias', 4, 5),
('excelente', 4, 4),
('genial', 4, 4),
('perfecto', 4, 3),
('increíble', 4, 3),
('fantástico', 4, 3);

-- Insertar palabras clave para despedidas
INSERT INTO bot_keywords (keyword, category_id, priority) VALUES 
('adiós', 5, 5),
('hasta luego', 5, 4),
('nos vemos', 5, 4),
('chao', 5, 3),
('bye', 5, 3);

-- Insertar palabras clave para ayuda
INSERT INTO bot_keywords (keyword, category_id, priority) VALUES 
('ayuda', 6, 5),
('qué puedes hacer', 6, 4),
('cómo funciona', 6, 3),
('necesito ayuda', 6, 4);

-- Insertar respuestas para saludos
INSERT INTO bot_responses (category_id, response_text, emoji) VALUES 
(1, '¡Hola! ¿Cómo estás?', '🤠'),
(1, '¡Qué tal, compadre!', '👋'),
(1, '¡Hola! ¿En qué te puedo ayudar?', '😊'),
(1, '¡Buenos días! ¿Cómo amaneciste?', '☀️'),
(1, '¡Buenas tardes! ¿Qué tal el día?', '🌤️'),
(1, '¡Buenas noches! ¿Cómo estuvo tu día?', '🌙');

-- Insertar respuestas para jaripeo
INSERT INTO bot_responses (category_id, response_text, emoji) VALUES 
(2, '¡Viva el jaripeo! Una tradición que nos llena de orgullo', '🐂'),
(2, '¡El jaripeo es pura adrenalina y tradición!', '🎉'),
(2, '¡El rodeo mexicano es el mejor!', '🤠'),
(2, '¡Puro talento y valentía en el rodeo!', '🏆'),
(2, '¡La charreada es nuestro deporte nacional!', '🇲🇽'),
(2, '¡Viva la charrería mexicana!', '🐎'),
(2, '¡Respeto total por esos toros bravos!', '🐂'),
(2, '¡Los toros son los reyes del ruedo!', '💪');

-- Insertar respuestas para cultura mexicana
INSERT INTO bot_responses (category_id, response_text, emoji) VALUES 
(3, '¡Viva México!', '🇲🇽'),
(3, '¡Qué orgullo ser mexicano!', '🎉'),
(3, '¡Nuestras tradiciones son únicas!', '🌟'),
(3, '¡México lindo y querido!', '❤️');

-- Insertar respuestas para emociones positivas
INSERT INTO bot_responses (category_id, response_text, emoji) VALUES 
(4, '¡De nada, para eso estamos!', '😊'),
(4, '¡Un placer ayudarte, compadre!', '🤝'),
(4, '¡Qué bueno que te guste!', '🎉'),
(4, '¡Así me gusta, con esa actitud!', '💪'),
(4, '¡Órale, qué emoción!', '🚀'),
(4, '¡Así se habla!', '🔥');

-- Insertar respuestas para despedidas
INSERT INTO bot_responses (category_id, response_text, emoji) VALUES 
(5, '¡Hasta luego, compadre!', '👋'),
(5, '¡Nos vemos pronto!', '😊'),
(5, '¡Que tengas buen día!', '🌞'),
(5, '¡Cuídate mucho!', '🤗');

-- Insertar respuestas para ayuda
INSERT INTO bot_responses (category_id, response_text, emoji) VALUES 
(6, '¡Claro! Estoy aquí para platicar contigo sobre jaripeo y más. ¿Qué te interesa saber?', '🤔'),
(6, 'Puedo platicar contigo sobre jaripeo, rodeo, cultura mexicana, ¡y lo que se te ocurra!', '🗣️'),
(6, '¡Pregúntame lo que quieras! Me encanta conversar', '💬');

-- Insertar respuestas por defecto
INSERT INTO bot_default_responses (response_text, emoji) VALUES 
('¡Qué interesante lo que dices!', '🤔'),
('¡Órale, cuéntame más!', '👂'),
('¡Así es, compadre!', '👍'),
('¡Qué padre está la plática!', '💬'),
('¡Sigue contándome! Me gusta escucharte', '😊'),
('¡Muy interesante tu punto de vista!', '🧐'),
('¡Esa sí está buena!', '😄'),
('¡Platícame más de eso!', '🗨️');

-- Insertar algunos mensajes de ejemplo (opcional)
INSERT INTO chat_messages (user_id, message, sender) VALUES 
('demo_user', '¡Hola!', 'user'),
('demo_user', '¡Hola! ¿Cómo estás? 🤠', 'bot'),
('demo_user', '¿Qué tal el jaripeo?', 'user'),
('demo_user', '¡Viva el jaripeo! 🐂 Una tradición que nos llena de orgullo', 'bot');