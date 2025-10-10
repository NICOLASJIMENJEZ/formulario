Registro de Titular e Invitados

Archivos:
- index.php: formulario principal (HTML + Bootstrap)
- assets/css/styles.css: estilos personalizados (gris/blanco)
- assets/js/form.js: comportamiento dinámico y envío AJAX
- db.php: conexión a MySQL (ajusta credenciales)
- register.php: recibe el POST y guarda en la tabla `registros` (o en archivo si la DB no está disponible)
- create_table.sql: script para crear la base de datos y la tabla

Instalación y prueba con XAMPP (Windows):
1. Copia la carpeta `formulario` a `C:\xampp\htdocs\` (ya está ahí si usas este proyecto).
2. Inicia Apache y MySQL desde el Panel de Control de XAMPP.
3. Crear la base de datos y tabla: abre phpMyAdmin o usa la terminal y ejecuta el SQL en `create_table.sql`.
   - En phpMyAdmin: importar `create_table.sql` o ejecutar su contenido en SQL.
4. Ajusta credenciales si tu MySQL no usa `root` sin contraseña: edita `db.php`.
5. Abre en el navegador: http://localhost/formulario/

Notas:
- El formulario es responsive y usa Bootstrap 5. Puedes agregar hasta 3 invitados con los botones.
- Campos obligatorios: `titular_nombre` y `titular_apellidos`.
- El backend usa consultas preparadas. Los invitados no proporcionados serán insertados como NULL.
- Si quieres, puedo añadir validaciones adicionales, envío por correo o exportación CSV.
