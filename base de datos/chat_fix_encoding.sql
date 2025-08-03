-- Limpieza y reinserción con codificación correcta
USE chat_db;

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Limpiar datos existentes
DELETE FROM bot_keywords;
DELETE FROM bot_responses;
DELETE FROM bot_default_responses;

-- Insertar palabras clave
INSERT INTO bot_keywords (keyword, category_id, priority) VALUES 
-- Saludos (categoría 1)
('hola', 1, 10),
('hello', 1, 10),
('buenos dias', 1, 10),
('buenas tardes', 1, 9),
('buenas noches', 1, 9),
('buen dia', 1, 8),
('que tal', 1, 7),
('como estas', 1, 7),
('como andas', 1, 7),
('saludos', 1, 6),
('hey', 1, 6),
('holi', 1, 6),
('buenas', 1, 5),
('que onda', 1, 8),

-- Jaripeo y rodeo (categoría 2)
('jaripeo', 2, 10),
('rodeo', 2, 10),
('vaquero', 2, 9),
('toro', 2, 9),
('caballo', 2, 8),
('charreria', 2, 9),
('charro', 2, 8),
('jinete', 2, 8),

-- Cultura mexicana (categoría 3)
('mexico', 3, 10),
('mexicano', 3, 9),
('tradicion', 3, 8),
('cultura', 3, 8),
('mariachi', 3, 7),
('fiesta', 3, 7),

-- Emociones positivas (categoría 4)
('genial', 4, 8),
('excelente', 4, 8),
('increible', 4, 7),
('fantastico', 4, 7),
('perfecto', 4, 6),
('padrisimo', 4, 8),
('chido', 4, 7),
('padre', 4, 6),

-- Despedidas (categoría 5)
('adios', 5, 10),
('hasta luego', 5, 9),
('nos vemos', 5, 8),
('bye', 5, 7),
('chao', 5, 6),
('cuidate', 5, 8),

-- Ayuda (categoría 6)
('ayuda', 6, 10),
('help', 6, 9),
('que puedes hacer', 6, 8),
('informacion', 6, 7),
('duda', 6, 7),

-- Comida (categoría 7)
('tacos', 7, 10),
('comida', 7, 9),
('enchiladas', 7, 8),
('quesadillas', 7, 8),
('tamales', 7, 8),

-- Música (categoría 8)
('musica', 8, 9),
('cancion', 8, 8),
('banda', 8, 8),
('ranchera', 8, 9),

-- Deportes (categoría 9)
('futbol', 9, 8),
('lucha libre', 9, 9),
('deportes', 9, 6);

-- Insertar respuestas sin caracteres especiales problemáticos
INSERT INTO bot_responses (category_id, response_text, emoji) VALUES 
-- Saludos
(1, 'Hola compadre! Como amaneciste hoy?', '🤠'),
(1, 'Que tal! Todo bien por tu rancho?', '👋'),
(1, 'Buenos dias, amigo! Listo para echarle ganas al dia?', '☀️'),
(1, 'Buenas tardes! Como va tu dia, companero?', '🌤️'),
(1, 'Buenas noches! Como estuvo tu jornada?', '🌙'),
(1, 'Hola! Me da mucho gusto saludarte, en que te puedo ayudar?', '😊'),
(1, 'Que tal, ranchero! Todo tranquilo en el corral?', '🐎'),
(1, 'Saludos! Que te trae por estos lares?', '🏜️'),

-- Jaripeo
(2, 'Ah, el jaripeo... Esa si es tradicion pura! Ya fuiste a alguno este ano?', '🐂'),
(2, 'El rodeo es pasion pura, hermano! Me platicas, montas a caballo?', '🤠'),
(2, 'Los vaqueros de verdad saben que no es solo montar, es entender al animal', '🐎'),
(2, 'Los toros bravos son otro nivel! Has visto alguna competencia profesional?', '💪'),

-- Cultura
(3, 'Mexico lindo y querido! Nuestra cultura es tan rica...', '🇲🇽'),
(3, 'Los mariachis son el alma de las fiestas, no crees?', '🎵'),
(3, 'Las tradiciones mexicanas tienen siglos de historia', '🎭'),

-- Emociones positivas
(4, 'Eso suena padrisimo! Me alegra saber que estas contento', '😄'),
(4, 'Que buena onda! Asi me gusta, con energia positiva', '🎉'),
(4, 'Excelente! Esa actitud es la que necesitamos', '👍'),

-- Despedidas
(5, 'Hasta la vista, compadre! Que tengas un excelente dia', '👋'),
(5, 'Cuidate mucho! Ya sabes, aqui estaremos cuando gustes platicar', '🤝'),
(5, 'Nos vemos despues, amigo. Que la pases increible!', '😊'),

-- Ayuda
(6, 'Claro que te ayudo, para eso estamos. Que necesitas saber?', '🤝'),
(6, 'Dime en que te puedo echar la mano', '💬'),
(6, 'Estoy aqui para lo que necesites, hermano', '🎯'),

-- Comida
(7, 'Los tacos! Hay algo mas mexicano? De que los prefieres?', '🌮'),
(7, 'La comida mexicana es patrimonio de la humanidad', '🍽️'),
(7, 'Los tamales en las fiestas navidenas... No hay nada como el sabor de casa!', '🫔'),

-- Música
(8, 'La musica ranchera llega al alma! Que cantante te gusta mas?', '🎤'),
(8, 'Los corridos cuentan historias reales, son como cronicas cantadas', '🎸'),
(8, 'La banda sinaloense tiene un sonido unico, no te parece?', '🎺'),

-- Deportes
(9, 'El futbol mueve pasiones! De que equipo eres?', '⚽'),
(9, 'La lucha libre mexicana es espectaculo puro', '🤼'),
(9, 'El boxeo mexicano tiene mucha tradicion', '🥊');

-- Respuestas por defecto
INSERT INTO bot_default_responses (response_text, emoji) VALUES 
('Eso esta interesante. Cuentame mas, me gusta escuchar buenas platicas', '🤔'),
('No estoy seguro de haber entendido bien, me lo explicas de otra manera?', '😅'),
('Eso no me queda muy claro, compadre. Podrias darme mas detalles?', '🧐'),
('Mejor platicame de jaripeo, musica ranchera o tradiciones mexicanas', '🎯'),
('No tengo mucha informacion sobre eso, pero puedo contarte cosas de la cultura mexicana', '🌟'),
('Esa esta dificil, hermano. Que tal si hablamos de algo que me emocione mas?', '🤠');
