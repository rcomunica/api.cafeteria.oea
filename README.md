# API de cafeteria

## Puesta en marcha

1. Ejecuta `database/database.sql` en MySQL/MariaDB.
2. Configura `DB_HOST`, `DB_NAME`, `DB_USER` y `DB_PASSWORD` como variables de entorno, o usa los valores locales por defecto.
3. Sirve la carpeta con Apache/Laragon.

Cada recurso expone `create.php`, `read.php`, `update.php` y `delete.php`. Las operaciones reciben y devuelven JSON. Para consultar un registro usa `read.php?id=1`; para listar, omite el parámetro.

Recursos: `users`, `products`, `orders`, `order_items`, `payments` y `payment_notifications`.

## Autenticacion

El registro es publico y crea siempre usuarios con rol `student`:

```text
POST /users/create.php
```

Para iniciar sesion:

```text
POST /auth/login.php
Content-Type: application/json

{"email":"ana.rodriguez@colegio.test","password":"tu contraseña"}
```

La respuesta contiene un token valido durante 8 horas. En las demas rutas se debe enviar:

```text
Authorization: Bearer TOKEN
```

Rutas de sesion: `auth/login.php`, `auth/me.php` y `auth/logout.php`.

Permisos: `student` puede consultar menu y crear ordenes; `employee` puede gestionar productos, pedidos y pagos; `admin` puede gestionar todos los recursos. La tabla `auth_tokens` guarda solamente el hash del token.
