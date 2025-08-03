# 🔧 Guía de Solución de Problemas - Chat Bot

## ❌ Error: "Unexpected token '<'" 

Este error significa que la API está devolviendo HTML en lugar de JSON. Sigue estos pasos:

### **Paso 1: Verificar XAMPP**
1. Abre el Panel de Control de XAMPP
2. Asegúrate de que **Apache** esté en verde ✅
3. Asegúrate de que **MySQL** esté en verde ✅

### **Paso 2: Verificar la Base de Datos**
1. Ve a http://localhost/phpmyadmin
2. Verifica que la base de datos `chat_db` existe
3. Verifica que tiene 6 tablas (chat_messages, chat_users, bot_categories, etc.)

### **Paso 3: Probar la API**
1. Ve a: http://localhost/chat_database/test_api.php
2. Ejecuta el script de prueba
3. Si hay errores, sigue las instrucciones que aparecen

### **Paso 4: Verificar Rutas**
Asegúrate de que tus archivos estén en la ruta correcta:
```
C:\xampp\htdocs\chat_database\
├── index.html
├── admin_chat.html
├── chat_api_v2.php
├── chat.sql
└── test_api.php
```

### **Paso 5: Verificar URLs**
En el navegador, verifica estas URLs:
- ✅ http://localhost/chat_database/ (debe mostrar el sitio)
- ✅ http://localhost/chat_database/chat_api_v2.php?action=get_categories (debe mostrar JSON)

## ❌ Error: "No se puede conectar"

### **Soluciones:**
1. **Reinicia XAMPP** completamente
2. **Cambia el puerto** si hay conflictos:
   - En XAMPP Config > Apache > Config > httpd.conf
   - Cambia `Listen 80` por `Listen 8080`
   - Usa http://localhost:8080/chat_database/

3. **Verifica el firewall** - Permite Apache en Windows Firewall

## ❌ Error: "Access denied" en Base de Datos

### **Soluciones:**
1. En `chat_api_v2.php`, verifica:
   ```php
   $host = 'localhost';
   $dbname = 'chat_db';
   $username = 'root';
   $password = ''; // Debe estar vacío en XAMPP
   ```

2. **Recrea la base de datos:**
   - Ve a phpMyAdmin
   - Elimina `chat_db` si existe
   - Importa `chat.sql` nuevamente

## ❌ Chat no aparece en el sitio

### **Soluciones:**
1. **Abre la consola del navegador** (F12)
2. **Revisa errores** en la pestaña Console
3. **Verifica que JavaScript esté habilitado**

## 🛠️ Herramientas de Debugging

### **1. Console del Navegador**
- Presiona F12
- Ve a la pestaña "Console"
- Busca mensajes en rojo (errores)

### **2. Network Tab**
- En F12, ve a "Network"
- Recarga la página
- Busca requests fallidos (en rojo)

### **3. Test API Script**
- Ejecuta: http://localhost/chat_database/test_api.php
- Te dirá exactamente qué está fallando

## 📞 Pasos de Emergencia

### **Si nada funciona:**

1. **Reinstala XAMPP:**
   - Desinstala XAMPP completamente
   - Descarga la versión más reciente
   - Reinstala con permisos de administrador

2. **Usa una carpeta sin espacios:**
   ```
   Mal:  C:\xampp\htdocs\chat database\
   Bien: C:\xampp\htdocs\chat_database\
   ```

3. **Verifica permisos:**
   - Clic derecho en la carpeta del proyecto
   - Propiedades > Seguridad
   - Asegúrate de que "Todos" tenga permisos de lectura

## 🔍 URLs de Verificación

Prueba estas URLs en tu navegador:

| URL | Qué debe mostrar |
|-----|------------------|
| http://localhost/ | Página de inicio de XAMPP |
| http://localhost/phpmyadmin | phpMyAdmin |
| http://localhost/chat_database/ | Tu sitio web |
| http://localhost/chat_database/chat_api_v2.php | Error de "No se especificó acción" |
| http://localhost/chat_database/test_api.php | Script de pruebas |

## 📱 Contacto

Si sigues teniendo problemas:
1. **Ejecuta el script de prueba** primero
2. **Toma captura** del error en consola
3. **Anota** qué pasos seguiste
4. **Describe** qué esperabas vs qué obtuviste

---

*Recuerda: La mayoría de errores se solucionan reiniciando XAMPP y verificando que las rutas sean correctas.*
