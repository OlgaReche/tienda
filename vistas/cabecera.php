<?php
/**
 * CAPA DE PRESENTACIÓN - Cabecera común
 * Muestra un menú distinto según el tipo de usuario (visitante, cliente o admin).
 * Antes de incluirla, cada página puede definir $titulo.
 */
require_once __DIR__ . '/../negocio/carrito.php';
$usuario = usuario_actual();
$mensaje = sacar_mensaje();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($titulo ?? 'Inicio') . ' · ' . NOMBRE_TIENDA) ?></title>
    <!-- ?v= cambia cada vez que se guarda el CSS, así el navegador no usa una copia antigua -->
    <link rel="stylesheet" href="<?= URL_BASE ?>/assets/css/estilos.css?v=<?= filemtime(__DIR__ . '/../assets/css/estilos.css') ?>">
</head>
<body>
<header class="cabecera">
    <a class="logo" href="<?= URL_BASE ?>/index.php"><?= e(NOMBRE_TIENDA) ?><span>sneakers</span></a>

    <nav class="menu">
        <?php if (es_admin()): ?>
            <!-- Menú del ADMINISTRADOR -->
            <a href="<?= URL_BASE ?>/admin/index.php">Panel</a>
            <a href="<?= URL_BASE ?>/admin/usuarios.php">Usuarios</a>
            <a href="<?= URL_BASE ?>/admin/articulos.php">Artículos</a>
            <a href="<?= URL_BASE ?>/admin/pedidos.php">Pedidos</a>
            <a href="<?= URL_BASE ?>/index.php">Ver tienda</a>
        <?php else: ?>
            <!-- Menú de VISITANTE y CLIENTE -->
            <a href="<?= URL_BASE ?>/index.php">Catálogo</a>
            <?php if (es_cliente()): ?>
                <a href="<?= URL_BASE ?>/mis_pedidos.php">Mis pedidos</a>
                <a href="<?= URL_BASE ?>/perfil.php">Mi perfil</a>
            <?php endif; ?>
            <a class="enlace-carrito" href="<?= URL_BASE ?>/carrito.php">
                Carrito <span class="contador"><?= carrito_num_unidades() ?></span>
            </a>
        <?php endif; ?>
    </nav>

    <div class="sesion">
        <?php if ($usuario): ?>
            <span>Hola, <strong><?= e($usuario['nombre']) ?></strong>
                <?= $usuario['rol'] === 'admin' ? '<em class="etiqueta">admin</em>' : '' ?></span>
            <a class="boton boton-secundario" href="<?= URL_BASE ?>/logout.php">Cerrar sesión</a>
        <?php else: ?>
            <a href="<?= URL_BASE ?>/login.php">Iniciar sesión</a>
            <a class="boton" href="<?= URL_BASE ?>/registro.php">Registrarse</a>
        <?php endif; ?>
    </div>
</header>

<main class="contenido">
<?php if ($mensaje): ?>
    <div class="mensaje mensaje-<?= e($mensaje['tipo']) ?>"><?= e($mensaje['texto']) ?></div>
<?php endif; ?>
