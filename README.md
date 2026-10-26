# API de cafeteria

## Puesta en marcha

1. Ejecuta `database/database.sql` en MySQL/MariaDB.
2. Configura `DB_HOST`, `DB_NAME`, `DB_USER` y `DB_PASSWORD` como variables de entorno, o usa los valores locales por defecto.
3. Sirve la carpeta con Apache/Laragon.

Cada recurso expone `create.php`, `read.php`, `update.php` y `delete.php`. Las operaciones reciben y devuelven JSON. Para consultar un registro usa `read.php?id=1`; para listar, omite el parámetro.

Recursos: `users`, `products`, `orders`, `order_items`, `payments` y `payment_notifications`.

### Imagenes de productos

`products/create.php` y `products/update.php` aceptan `multipart/form-data`. Envia la imagen en el campo `image` junto con los campos del producto. Se aceptan JPG, PNG, WEBP y GIF de hasta 5 MB; la respuesta y el registro usan `image_url` con la ruta publica de la imagen.

```text
curl -X POST /products/create.php \
  -H "Authorization: Bearer TOKEN" \
  -F "name=Cafe" \
  -F "category=Bebidas" \
  -F "price=5000" \
  -F "qty=10" \
  -F "image=@/ruta/cafe.jpg"
```

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
