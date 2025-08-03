-- Actualización del sistema de chat para hacerlo más inteligente
-- Base de datos mejorada para chatbot Jaripeo Ranchero

USE chat_db;

-- Crear tabla para contexto de conversación
CREATE TABLE IF NOT EXISTS bot_conversation_context (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(50) NOT NULL,
    last_category VARCHAR(50),
    conversation_state VARCHAR(50) DEFAULT 'normal',
    user_preferences TEXT, -- JSON con preferencias del usuario
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear tabla para respuestas de seguimiento
CREATE TABLE IF NOT EXISTS bot_followup_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    trigger_response_id INT,
    followup_text TEXT NOT NULL,
    probability DECIMAL(3,2) DEFAULT 0.3, -- Probabilidad de usar esta respuesta de seguimiento
    active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (category_id) REFERENCES bot_categories(id) ON DELETE CASCADE,
    FOREIGN KEY (trigger_response_id) REFERENCES bot_responses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear tabla para respuestas inteligentes basadas en tiempo
CREATE TABLE IF NOT EXISTS bot_time_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    time_condition VARCHAR(50), -- 'morning', 'afternoon', 'evening', 'night'
    response_text TEXT NOT NULL,
    emoji VARCHAR(20),
    active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (category_id) REFERENCES bot_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear tabla para respuestas con múltiples variaciones
CREATE TABLE IF NOT EXISTS bot_response_variations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    base_response_id INT,
    variation_text TEXT NOT NULL,
    usage_weight DECIMAL(3,2) DEFAULT 1.0, -- Peso para selección aleatoria
    active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (base_response_id) REFERENCES bot_responses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Limpiar datos existentes para actualizar
DELETE FROM bot_keywords;
DELETE FROM bot_responses;
DELETE FROM bot_default_responses;

-- Insertar palabras clave mejoradas con más variaciones
INSERT INTO bot_keywords (keyword, category_id, priority) VALUES 
-- Saludos (categoría 1)
('hola', 1, 10),
('hello', 1, 10),
('buenos días', 1, 10),
('buenas tardes', 1, 9),
('buenas noches', 1, 9),
('buen día', 1, 8),
('qué tal', 1, 7),
('cómo estás', 1, 7),
('cómo andas', 1, 7),
('saludos', 1, 6),
('hey', 1, 6),
('holi', 1, 6),
('buenas', 1, 5),
('qué onda', 1, 8),
('qué pedo', 1, 7),

-- Jaripeo y rodeo (categoría 2)
('jaripeo', 2, 10),
('rodeo', 2, 10),
('vaquero', 2, 9),
('toro', 2, 9),
('caballo', 2, 8),
('montadura', 2, 7),
('charrería', 2, 9),
('charro', 2, 8),
('jinete', 2, 8),
('lazo', 2, 6),
('espolones', 2, 6),
('arena', 2, 5),

-- Cultura mexicana (categoría 3)
('méxico', 3, 10),
('mexicano', 3, 9),
('tradición', 3, 8),
('cultura', 3, 8),
('mariachi', 3, 7),
('fiesta', 3, 7),
('día de muertos', 3, 9),
('cinco de mayo', 3, 8),
('independencia', 3, 8),
('revolución', 3, 7),

-- Emociones positivas (categoría 4)
('genial', 4, 8),
('excelente', 4, 8),
('increíble', 4, 7),
('fantástico', 4, 7),
('perfecto', 4, 6),
('maravilloso', 4, 6),
('buenísimo', 4, 6),
('padrísimo', 4, 8),
('chido', 4, 7),
('padre', 4, 6),

-- Despedidas (categoría 5)
('adiós', 5, 10),
('hasta luego', 5, 9),
('nos vemos', 5, 8),
('bye', 5, 7),
('chao', 5, 6),
('hasta la vista', 5, 7),
('cuídate', 5, 8),
('que tengas buen día', 5, 7),

-- Preguntas y ayuda (categoría 6)
('ayuda', 6, 10),
('help', 6, 9),
('qué puedes hacer', 6, 8),
('información', 6, 7),
('pregunta', 6, 6),
('duda', 6, 7),
('no entiendo', 6, 8),
('explícame', 6, 7),

-- Comida (categoría 7)
('tacos', 7, 10),
('comida', 7, 9),
('mexicana', 7, 8),
('enchiladas', 7, 8),
('quesadillas', 7, 8),
('pozole', 7, 7),
('mole', 7, 7),
('tamales', 7, 8),
('guacamole', 7, 6),
('salsa', 7, 6),

-- Música (categoría 8)
('música', 8, 9),
('canción', 8, 8),
('banda', 8, 8),
('norteño', 8, 9),
('ranchera', 8, 9),
('mariachi', 8, 8),
('corrido', 8, 8),
('conjunto', 8, 7),

-- Deportes (categoría 9)
('fútbol', 9, 8),
('lucha libre', 9, 9),
('box', 9, 7),
('boxeo', 9, 7),
('deportes', 9, 6);

-- Respuestas mejoradas y más conversacionales
INSERT INTO bot_responses (category_id, response_text, emoji) VALUES 
-- Saludos más naturales y variados
(1, '¡Órale, qué tal compadre! ¿Cómo amaneciste hoy?', '🤠'),
(1, '¡Ey, qué onda! ¿Todo bien por tu rancho?', '👋'),
(1, '¡Buenos días, amigo! ¿Listo para echarle ganas al día?', '☀️'),
(1, '¡Buenas tardes! ¿Cómo va tu día, compañero?', '🌤️'),
(1, '¡Buenas noches! ¿Cómo estuvo tu jornada?', '🌙'),
(1, '¡Hola! Me da mucho gusto saludarte, ¿en qué te puedo ayudar?', '😊'),
(1, '¡Qué tal, ranchero! ¿Todo tranquilo en el corral?', '🐎'),
(1, '¡Saludos! ¿Qué te trae por estos lares?', '🏜️'),
(1, '¡Órale! Otro día más en el rancho, ¿verdad compañero?', '🌵'),

-- Jaripeo y rodeo más detallados
(2, 'Ah, el jaripeo... ¡Esa sí es tradición pura! ¿Ya fuiste a alguno este año? Los jinetes de ahora están muy buenos', '🐂'),
(2, '¡El rodeo es pasión pura, hermano! Me platicas, ¿montas a caballo o nada más admiras el espectáculo?', '🤠'),
(2, 'Los vaqueros de verdad saben que no es solo montar, es entender al animal. ¿Qué opinas del jaripeo moderno?', '🐎'),
(2, '¡Uf, los toros bravos son otro nivel! ¿Has visto alguna competencia profesional? Son impresionantes', '💪'),
(2, 'La charrería mexicana es patrimonio cultural, ¿sabías? Me encanta ver cómo se mantienen vivas nuestras tradiciones', '🇲🇽'),
(2, '¿Sabes montar? Porque manejar un caballo brioso requiere técnica y mucho respeto por el animal', '🏇'),

-- Cultura mexicana más profunda
(3, '¡México lindo y querido! Nuestra cultura es tan rica... ¿Cuál tradición mexicana te gusta más?', '🇲🇽'),
(3, 'Los mariachis son el alma de las fiestas, ¿no crees? Su música llega directo al corazón', '🎵'),
(3, 'El Día de Muertos es hermoso, honramos a nuestros ancestros con alegría. ¿Cómo lo celebras tú?', '💀'),
(3, 'Las tradiciones mexicanas tienen siglos de historia. Me fascina cómo se mantienen vivas', '🎭'),
(3, '¿Has ido a alguna feria patronal? Son la esencia de los pueblos mexicanos', '🎪'),

-- Emociones positivas más entusiastas
(4, '¡Eso suena padrísimo! Me alegra saber que estás contento, compadre', '😄'),
(4, '¡Órale, qué buena onda! Así me gusta, con energía positiva', '🎉'),
(4, '¡Excelente! Esa actitud es la que necesitamos para salir adelante', '👍'),
(4, '¡Genial! Me contagias tu buen humor, así se hace ranchero', '🤗'),
(4, '¡Ándale! Con esa energía puedes con todo lo que te propongas', '💪'),

-- Despedidas más cálidas
(5, '¡Hasta la vista, compadre! Que tengas un excelente día y nos vemos pronto', '👋'),
(5, '¡Cuídate mucho! Y ya sabes, aquí estaremos cuando gustes platicar', '🤝'),
(5, '¡Órale, que te vaya bonito! Regresa cuando quieras, siempre eres bienvenido', '☀️'),
(5, 'Nos vemos después, amigo. ¡Que la pases increíble!', '😊'),
(5, '¡Hasta luego! May el viento sople a tu favor, como dicen los vaqueros', '🌬️'),

-- Ayuda más específica
(6, 'Claro que te ayudo, para eso estamos. ¿Qué necesitas saber sobre jaripeo, cultura mexicana o música?', '🤝'),
(6, 'Dime en qué te puedo echar la mano. Puedo platicar de tradiciones, jaripeo, comida mexicana y más', '💬'),
(6, 'Estoy aquí para lo que necesites, hermano. ¿Te interesa algo en particular?', '🎯'),
(6, '¡Por supuesto! Me gusta ayudar. ¿Qué tema te llama la atención?', '❓'),

-- Comida más descriptiva
(7, '¡Los tacos! ¿Hay algo más mexicano? ¿De qué los prefieres? ¿Pastor, carnitas, barbacoa?', '🌮'),
(7, 'La comida mexicana es patrimonio de la humanidad, ¿sabías? ¿Cuál es tu platillo favorito?', '🍽️'),
(7, '¡Uy, el mole! Ese sí es arte culinario puro. ¿Has probado el mole poblano auténtico?', '🍲'),
(7, 'Los tamales en las fiestas navideñas... ¡No hay nada como el sabor de casa!', '🫔'),
(7, '¿Guacamole casero? ¡Eso sí está bueno! Con limón, sal y chile, ¿verdad?', '🥑'),

-- Música más específica
(8, '¡La música ranchera llega al alma! ¿Qué cantante te gusta más? ¿Vicente, Alejandro, Pedro Infante?', '🎤'),
(8, 'Los corridos cuentan historias reales, son como crónicas cantadas. ¿Conoces algunos clásicos?', '🎸'),
(8, 'La banda sinaloense tiene un sonido único, ¿no te parece? Esos metales suenan padrísimos', '🎺'),
(8, '¿Has escuchado música norteña en vivo? Con acordeón y bajo sexto suena increíble', '🪗'),

-- Deportes
(9, '¡El fútbol mueve pasiones! ¿De qué equipo eres? Aquí respetamos a todos los aficionados', '⚽'),
(9, 'La lucha libre mexicana es espectáculo puro. ¿Tienes algún luchador favorito?', '🤼'),
(9, 'El boxeo mexicano tiene mucha tradición. Grandes campeones hemos tenido', '🥊');

-- Respuestas por defecto más variadas y conversacionales
INSERT INTO bot_default_responses (response_text, emoji) VALUES 
('Órale, eso está interesante. Cuéntame más, me gusta escuchar buenas pláticas', '🤔'),
('¡Ey, qué onda! No estoy seguro de haber entendido bien, ¿me lo explicas de otra manera?', '😅'),
('Hmm, eso no me queda muy claro, compadre. ¿Podrías darme más detalles?', '🧐'),
('¡Órale! Eso suena bien, aunque me gustaría saber más al respecto', '👂'),
('Ah, mira... no estoy completamente seguro, pero me parece interesante lo que dices', '💭'),
('¿Sabes qué? Mejor platícame de jaripeo, música ranchera o tradiciones mexicanas, de eso sí sé bastante', '🎯'),
('No tengo mucha información sobre eso, pero puedo contarte cosas padre sobre la cultura mexicana', '🌟'),
('Esa está difícil, hermano. ¿Qué tal si hablamos de algo que me emocione más, como el rodeo?', '🤠'),
('¡Órale! Me dejaste pensando... ¿Por qué no mejor me preguntas sobre tradiciones mexicanas?', '🇲🇽'),
('Esa no me la sabía, compadre. Pero seguro puedo platicar contigo de otras cosas padres', '✨');

-- Insertar respuestas basadas en tiempo
INSERT INTO bot_time_responses (category_id, time_condition, response_text, emoji) VALUES 
(1, 'morning', '¡Buenos días, compadre! ¿Ya desayunaste? El día está perfecto para trabajar en el rancho', '🌅'),
(1, 'morning', '¡Qué tal el amanecer! ¿Listo para otra jornada de trabajo duro?', '☀️'),
(1, 'afternoon', '¡Buenas tardes! ¿Cómo va el día? Esperemos que con mucho éxito', '🌤️'),
(1, 'afternoon', '¡Ey, qué tal la tarde! ¿Todo bien por tu lado?', '🌞'),
(1, 'evening', '¡Buenas noches! ¿Cómo estuvo tu día de trabajo, ranchero?', '🌆'),
(1, 'evening', '¡Órale, ya cayó la noche! ¿Todo tranquilo después de la jornada?', '🌙'),
(1, 'night', '¡Buenas noches! Es hora de descansar después de tanto trabajo', '✨'),
(1, 'night', '¡Órale, ya es tarde! ¿Aún andas despierto, compadre?', '🌃');

-- Respuestas de seguimiento (se activarán después de ciertas respuestas)
INSERT INTO bot_followup_responses (category_id, trigger_response_id, followup_text, probability) VALUES 
(2, 1, '¿Has participado alguna vez en competencias de jaripeo?', 0.4),
(2, 2, '¿Qué tipo de eventos de rodeo te gustan más?', 0.3),
(7, 1, '¿Conoces alguna receta familiar especial?', 0.3),
(8, 1, '¿Sabes tocar algún instrumento?', 0.2);

-- Actualizar estructura para hacer el bot más contextual
ALTER TABLE chat_messages ADD COLUMN context_data TEXT COMMENT 'JSON con datos de contexto';
ALTER TABLE chat_messages ADD COLUMN sentiment VARCHAR(20) DEFAULT 'neutral' COMMENT 'Sentimiento detectado';
