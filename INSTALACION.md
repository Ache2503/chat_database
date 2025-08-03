# 🚀 Guía Rápida de Instalación - Chat Bot Jaripeo

## ⚡ Instalación en 5 Minutos

### **Paso 1: Descargar XAMPP**
1. Ve a https://www.apachefriends.org/
2. Descarga XAMPP para Windows
3. Instala con configuración por defecto

### **Paso 2: Iniciar Servicios**
1. Abre el Panel de Control de XAMPP
2. Inicia **Apache** ✅
3. Inicia **MySQL** ✅

### **Paso 3: Crear Base de Datos**
1. Ve a http://localhost/phpmyadmin
2. Clic en **"Nueva"** en la barra lateral
3. Nombra la base de datos: `chat_db`
4. Clic en **"Crear"**
5. Ve a la pestaña **"Importar"**
6. Selecciona el archivo `chat.sql`
7. Clic en **"Continuar"**

### **Paso 4: Copiar Archivos**
1. Copia toda la carpeta del proyecto a:
   ```
   C:\xampp\htdocs\chat_database\
   ```

### **Paso 5: ¡Listo!**
Abre en tu navegador:
- **Sitio principal:** http://localhost/chat_database/
- **Panel admin:** http://localhost/chat_database/admin_chat.html

---

## 🔧 Configuración Opcional

### **Cambiar URL de API**
Si tu carpeta tiene otro nombre, edita en los archivos HTML:
```javascript
// Cambia esto:
const API_URL = 'http://localhost/chat%20database/chat_api_v2.php';

// Por esto (ejemplo):
const API_URL = 'http://localhost/mi_proyecto/chat_api_v2.php';
```

### **Configurar Base de Datos**
Si usas otros datos de conexión, edita `chat_api_v2.php`:
```php
$host = 'localhost';        // Tu servidor
$dbname = 'chat_db';        // Tu base de datos  
$username = 'root';         // Tu usuario
$password = '';             // Tu contraseña
```

---

## ✅ Verificar Instalación

### **Test 1: Base de Datos**
- Ve a http://localhost/phpmyadmin
- Verifica que `chat_db` tenga 6 tablas

### **Test 2: API**
- Ve a http://localhost/chat_database/chat_api_v2.php?action=get_categories
- Debe mostrar JSON con categorías

### **Test 3: Chat**
- Ve a http://localhost/chat_database/
- Clic en el botón del chat (🤠)
- Escribe "hola" y verifica respuesta

### **Test 4: Admin**
- Ve a http://localhost/chat_database/admin_chat.html
- Revisa que las estadísticas se carguen

---

## 🆘 Problemas Comunes

### **"Access denied" en phpMyAdmin**
```
Solución: Usar usuario 'root' sin contraseña
```

### **"No such file or directory"**
```
Solución: Verificar que Apache esté iniciado
```

### **Chat no funciona**
```
Solución: Revisar URL de API en archivos HTML
```

### **Panel admin en blanco**
```
Solución: Verificar que chat_api_v2.php sea accesible
```

---

## 📱 Para Usar el Sistema

### **Como Usuario:**
1. Abre el sitio web
2. Clic en el botón de chat (🤠)
3. ¡Conversa con el bot!

### **Como Administrador:**
1. Ve al panel admin
2. Agrega categorías en la pestaña "Categorías"
3. Define palabras clave en "Palabras Clave"
4. Crea respuestas en "Respuestas"
5. Monitorea estadísticas en "Estadísticas"

---

## 🎯 Primeros Pasos Recomendados

1. **Prueba el chat** con palabras como: "hola", "jaripeo", "gracias"
2. **Explora el admin** para entender cómo funciona
3. **Agrega tu contenido** personalizado
4. **Personaliza colores** en CSS si deseas

---

*¿Necesitas ayuda? Revisa la documentación completa en `documentacion.md`*
