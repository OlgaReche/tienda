# Zolgada · Tienda online de zapatillas

Trabajo de enfoque de **Desarrollo Web en Entorno Servidor** (DAW).
Aplicación en PHP + MySQL con arquitectura de 3 capas.

## Instalación en XAMPP

1. Copiar la carpeta `tienda` dentro de `C:\xampp\htdocs\`.
2. Arrancar **Apache** y **MySQL** desde el panel de XAMPP.
3. Abrir http://localhost/phpmyadmin → pestaña **Importar** → elegir `sql/tienda.sql` → **Importar**.
4. Abrir http://localhost/tienda

## Usuarios de prueba

| Tipo usuario | Usuario (email)    | Contraseña   |
|--------------|--------------------|--------------|
| Admin        | admin@tienda.com   | Admin1234!   |
| Cliente      | cliente@tienda.com | Cliente1234! |

## Estructura (3 capas)

```
tienda/
├── config/config.php        Configuración (BD, nombre, rutas)
├── datos/                   CAPA DE ACCESO A DATOS (PHP + MySQL)
│   ├── conexion.php           Conexión mysqli y consultas preparadas
│   ├── usuarios_dao.php       SQL de la tabla usuarios
│   ├── articulos_dao.php      SQL de la tabla articulos
│   └── pedidos_dao.php        SQL de pedidos (con transacción)
├── negocio/                 CAPA DE LÓGICA DE NEGOCIO (PHP)
│   ├── sesion.php             Sesiones, roles, permisos, CSRF
│   ├── validacion.php         filter_input y validación de formularios
│   ├── usuarios.php           Registro, login (password_hash/verify)
│   └── carrito.php            Carrito guardado en $_SESSION
├── vistas/                  Plantillas comunes (cabecera, pie, campos)
├── *.php                    CAPA CLIENTE: páginas públicas y del cliente
├── admin/*.php              CAPA CLIENTE: páginas del administrador
├── assets/css, assets/js    CAPA CLIENTE: estilos y validación JavaScript
├── img/articulos/           Fotos subidas de los artículos
└── sql/tienda.sql           Script de creación de la BD con datos de prueba
```

## Funcionalidades

- **RF01** (admin): crear, modificar y eliminar usuarios cliente y administrador → `admin/usuarios.php`
- **RF02** (admin): crear, modificar y eliminar artículos, con foto → `admin/articulos.php`
- **RF03** (cliente): catálogo con búsqueda, ficha, carrito en sesión y confirmar compra → `index.php`, `carrito.php`, `confirmar.php`
- Extra: historial "Mis pedidos", perfil del cliente, gestión de estados de pedidos, cierre de sesión por inactividad (30 min), cookie "recordar email".

## Publicar en un hosting (InfinityFree)

1. Crear la base de datos en el panel del hosting e importar `sql/tienda.sql`
   (antes, borrar las líneas `DROP DATABASE`, `CREATE DATABASE` y `USE`, porque el hosting crea la BD con su propio nombre).
2. Cambiar en `config/config.php` los datos `BD_HOST`, `BD_USUARIO`, `BD_PASSWORD`, `BD_NOMBRE`
   y poner `URL_BASE` a `''` si la tienda va en la raíz del dominio.
3. Subir los archivos a la carpeta `htdocs` del hosting.
