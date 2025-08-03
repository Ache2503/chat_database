# 🤠 Chat Bot Jaripeo Ranchero

> Sistema de chat bot inteligente con temática de jaripeo y cultura mexicana

[![PHP](https://img.shields.io/badge/PHP-7.4+-blue.svg)](https://php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)](https://mysql.com/)
[![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-yellow.svg)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

## 🎯 ¿Qué es este proyecto?

Un sistema completo de chat bot que incluye:
- **🤖 Bot conversacional** inteligente con respuestas contextuales
- **⚙️ Panel de administración** web para gestionar respuestas
- **🌐 Sitio web** temático de jaripeo con chat integrado
- **📊 Base de datos** MySQL para persistencia de datos

## ✨ Características Principales

- ✅ **Respuestas inteligentes** basadas en palabras clave
- ✅ **Sistema de categorías** y prioridades
- ✅ **Panel de administración** completo
- ✅ **Historial de conversaciones** persistente
- ✅ **Diseño responsive** y moderno
- ✅ **Tema mexicano** auténtico
- ✅ **API RESTful** completa

## 🚀 Instalación Rápida

### Requisitos
- XAMPP/LAMP (Apache + MySQL + PHP)
- Navegador web moderno

### Pasos
1. **Instala XAMPP** desde https://www.apachefriends.org/
2. **Inicia Apache y MySQL** desde el panel de XAMPP
3. **Crea la base de datos:**
   - Ve a http://localhost/phpmyadmin
   - Importa el archivo `chat.sql`
4. **Copia los archivos** a `C:\xampp\htdocs\chat_database\`
5. **¡Listo!** Ve a http://localhost/chat_database/

> 📖 **Guía detallada:** Ver `INSTALACION.md`

## 🖥️ Capturas de Pantalla

### Sitio Principal con Chat
![Chat integrado en el sitio principal](https://via.placeholder.com/600x400/8B4513/FFFFFF?text=Chat+Integrado)

### Panel de Administración
![Panel de administración del bot](https://via.placeholder.com/600x400/667eea/FFFFFF?text=Panel+Admin)

## 📁 Estructura del Proyecto

```
chat_database/
├── 🌐 index.html              # Sitio principal con chat
├── ⚙️ admin_chat.html         # Panel de administración
├── 💬 chat_widget.html        # Chat independiente
├── 🔌 chat_api_v2.php         # API principal (recomendada)
├── 🔌 chat_api.php            # API básica
├── 🗄️ chat.sql               # Base de datos
├── 📖 documentacion.md        # Documentación completa
├── 🚀 INSTALACION.md          # Guía de instalación
└── 📝 README.md              # Este archivo
```

## 🎮 Uso del Sistema

### Para Usuarios
1. Abre el sitio web
2. Haz clic en el botón de chat (🤠)
3. ¡Conversa sobre jaripeo y cultura mexicana!

### Para Administradores
1. Ve a `/admin_chat.html`
2. Gestiona categorías, palabras clave y respuestas
3. Monitorea estadísticas de uso

## 🔧 Configuración

### URLs de Desarrollo
```javascript
const API_URL = 'http://localhost/chat_database/chat_api_v2.php';
```

### URLs de Producción
```javascript
const API_URL = 'https://tudominio.com/api/chat_api_v2.php';
```

## 📊 Funcionalidades del Bot

### Categorías Predefinidas
- **Saludos:** "hola", "buenos días", "qué tal"
- **Jaripeo:** "jaripeo", "rodeo", "charreada", "toro"
- **Cultura:** "méxico", "mexicano", "tradición"
- **Emociones:** "gracias", "excelente", "genial"
- **Y más...**

### Ejemplos de Conversación
```
Usuario: ¡Hola!
Bot: ¡Hola! ¿Cómo estás? 🤠

Usuario: ¿Qué tal el jaripeo?
Bot: ¡Viva el jaripeo! 🐂 Una tradición que nos llena de orgullo

Usuario: Gracias
Bot: ¡De nada, para eso estamos! 😊
```

## 🛠️ API Endpoints

| Endpoint | Descripción |
|----------|-------------|
| `sendMessage` | Enviar mensaje al bot |
| `getHistory` | Obtener historial de chat |
| `get_categories` | Listar categorías |
| `add_response` | Crear nueva respuesta |
| `get_stats` | Obtener estadísticas |

> 📖 **Documentación completa de la API:** Ver `documentacion.md`

## 🎨 Personalización

### Colores del Tema
```css
:root {
    --leather-brown: #8B4513;  /* Marrón cuero */
    --old-gold: #DAA520;       /* Oro viejo */
    --deep-black: #1a1a1a;     /* Negro profundo */
    --cream: #F5F5DC;          /* Crema */
    --dark-brown: #654321;     /* Marrón oscuro */
}
```

### Agregar Nuevas Respuestas
1. Ve al panel de administración
2. Crea una categoría
3. Define palabras clave
4. Agrega respuestas con emojis

## 🔍 Monitoreo y Estadísticas

El panel de administración incluye:
- 📊 **Usuarios totales** y activos
- 💬 **Mensajes enviados**
- ⏱️ **Tiempo de respuesta promedio**
- 📈 **Respuestas más utilizadas**

## 🛡️ Seguridad

### Medidas Implementadas
- Validación de entrada en formularios
- Escape de datos en consultas SQL (PDO)
- Headers CORS configurados
- Sanitización de datos de usuario

### Recomendaciones Adicionales
- Implementar autenticación para admin
- Usar HTTPS en producción
- Configurar rate limiting
- Realizar backups regulares

## 🐛 Resolución de Problemas

### Error: "No se puede conectar a la base de datos"
- Verifica que MySQL esté ejecutándose
- Confirma las credenciales en `chat_api_v2.php`

### Error: "Chat no aparece"
- Revisa la consola del navegador
- Verifica la URL de la API

### Error: "Panel admin en blanco"
- Confirma que `chat_api_v2.php` sea accesible
- Verifica que las tablas de BD existan

> 🆘 **Más soluciones:** Ver `documentacion.md`

## 🚀 Roadmap

### Próximas Versiones
- [ ] 🔐 Sistema de autenticación
- [ ] 🤖 Integración con IA (OpenAI/Dialogflow)
- [ ] 📱 App móvil
- [ ] 🌍 Multi-idioma
- [ ] 📊 Analytics avanzados

## 🤝 Contribuir

¡Las contribuciones son bienvenidas!

1. Haz fork del proyecto
2. Crea una rama para tu funcionalidad
3. Haz commit de tus cambios
4. Envía un pull request

### Estándares de Código
- **PHP:** PSR-12
- **JavaScript:** ES6+
- **CSS:** Metodología BEM
- **Commits:** Conventional Commits

## 📞 Soporte

### Documentación
- 📖 **Completa:** `documentacion.md`
- 🚀 **Instalación:** `INSTALACION.md`
- 📝 **Este archivo:** `README.md`

### Contacto
- 📧 Email: [tu-email@ejemplo.com]
- 💬 Issues: [GitHub Issues]
- 📱 WhatsApp: [Tu número]

## 📄 Licencia

Este proyecto está bajo la licencia MIT. Ver el archivo [LICENSE](LICENSE) para más detalles.

## 🙏 Agradecimientos

- **Inspiración:** La rica tradición del jaripeo mexicano
- **Tecnologías:** PHP, MySQL, JavaScript
- **Diseño:** Font Awesome, Google Fonts
- **Comunidad:** Desarrolladores que mantienen las herramientas open source

---

<div align="center">

**¡Hecho con ❤️ para preservar nuestras tradiciones mexicanas!**

🤠 **¡Viva el jaripeo!** 🐂

[⭐ Dale una estrella](../../stargazers) | [🐛 Reportar bug](../../issues) | [💡 Solicitar función](../../issues)

</div>

---

*Última actualización: Agosto 2025*
