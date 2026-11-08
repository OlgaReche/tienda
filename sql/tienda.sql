-- =====================================================================
-- Base de datos de la tienda online de zapatillas
-- Trabajo de enfoque - Desarrollo Web en Entorno Servidor (DAW)
-- Importar desde phpMyAdmin: pestaña "Importar" > seleccionar este archivo
-- =====================================================================

SET NAMES utf8mb4;   -- el archivo está en UTF-8 (tildes y ñ correctas)

DROP DATABASE IF EXISTS tienda_zapatillas;
CREATE DATABASE tienda_zapatillas CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci;
USE tienda_zapatillas;

-- ---------------------------------------------------------------------
-- USUARIOS: clientes y administradores de la tienda.
-- El campo "rol" decide a qué partes de la web puede entrar cada uno.
-- ---------------------------------------------------------------------
CREATE TABLE usuarios (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(50)  NOT NULL,
    apellidos   VARCHAR(100) NOT NULL,
    email       VARCHAR(120) NOT NULL UNIQUE,           -- se usa para iniciar sesión
    password    VARCHAR(255) NOT NULL,                  -- guardada cifrada con password_hash()
    telefono    VARCHAR(15)  NULL,                      -- opcional
    direccion   VARCHAR(200) NULL,                      -- opcional (se pide al confirmar compra)
    rol         ENUM('admin','cliente') NOT NULL DEFAULT 'cliente',
    fecha_alta  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- ARTICULOS: zapatillas a la venta.
-- "activo" permite ocultar un artículo sin borrarlo (si ya tiene pedidos).
-- ---------------------------------------------------------------------
CREATE TABLE articulos (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre       VARCHAR(100) NOT NULL,
    marca        VARCHAR(50)  NOT NULL,
    descripcion  TEXT NULL,
    talla        DECIMAL(3,1) NOT NULL,                 -- p. ej. 42 o 42.5
    color        VARCHAR(30)  NULL,
    precio       DECIMAL(8,2) NOT NULL CHECK (precio >= 0),
    stock        INT UNSIGNED NOT NULL DEFAULT 0,
    imagen       VARCHAR(255) NULL,                     -- nombre del archivo en img/articulos/
    activo       TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- PEDIDOS: cabecera de cada compra confirmada por un cliente.
-- Relación: un usuario (1) puede tener muchos pedidos (N).
-- ---------------------------------------------------------------------
CREATE TABLE pedidos (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id        INT UNSIGNED NOT NULL,
    fecha             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    direccion_envio   VARCHAR(200) NOT NULL,
    total             DECIMAL(10,2) NOT NULL,
    estado            ENUM('pendiente','enviado','entregado','cancelado') NOT NULL DEFAULT 'pendiente',
    CONSTRAINT fk_pedidos_usuario FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- LINEAS_PEDIDO: cada artículo dentro de un pedido.
-- Resuelve la relación N:M entre pedidos y artículos.
-- Se guarda precio_unitario porque el precio del artículo puede cambiar.
-- ---------------------------------------------------------------------
CREATE TABLE lineas_pedido (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedido_id        INT UNSIGNED NOT NULL,
    articulo_id      INT UNSIGNED NOT NULL,
    cantidad         INT UNSIGNED NOT NULL CHECK (cantidad > 0),
    precio_unitario  DECIMAL(8,2) NOT NULL,
    CONSTRAINT fk_lineas_pedido   FOREIGN KEY (pedido_id)
        REFERENCES pedidos(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_lineas_articulo FOREIGN KEY (articulo_id)
        REFERENCES articulos(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- DATOS DE PRUEBA
-- admin@tienda.com   / Admin1234!    (administrador)
-- cliente@tienda.com / Cliente1234!  (cliente)
-- =====================================================================
INSERT INTO usuarios (nombre, apellidos, email, password, telefono, direccion, rol) VALUES
('Admin', 'Tienda', 'admin@tienda.com',
 '$2y$10$G6QvyjMX3ly/VLc0xz0SAeCz1GmKLqA6s/s11ZFPoo33XMomvYtmq', NULL, NULL, 'admin'),
('Laura', 'García Pérez', 'cliente@tienda.com',
 '$2y$10$paKw1G8KxjmJEVeIwSw9p.K6fXtA/x7ipOlm/Pr7KEidOMX50WaWq', '600123456', 'C/ Mayor 10, 28013 Madrid', 'cliente');

INSERT INTO articulos (nombre, marca, descripcion, talla, precio, stock) VALUES
('Air Runner 3',     'Nike',        'Zapatilla de running ligera con amortiguación de espuma.', 42.0, 119.99, 10),
('Ultraboost Light', 'Adidas',      'Máximo retorno de energía para entrenamientos largos.',    41.0, 179.95,  6),
('Gel-Nimbus 26',    'Asics',       'Amortiguación de gel para corredores de larga distancia.', 43.0, 159.90,  8),
('Fresh Foam 1080',  'New Balance', 'Comodidad diaria con espuma Fresh Foam.',                  40.5, 149.99,  5),
('Suede Classic',    'Puma',        'Clásica de ante para uso urbano.',                         39.0,  74.95, 12),
('Pegasus 41',       'Nike',        'Versátil para entrenar a diario.',                         44.0, 129.99,  0),
('Clifton 9',        'Hoka',        'Suela gruesa y ligera para máximo confort.',               42.5, 144.90,  7),
('Old Skool',        'Vans',        'Icono del skate con la franja lateral.',                   38.0,  79.00, 15);
