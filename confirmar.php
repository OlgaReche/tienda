<?php
/**
 * CONFIRMAR COMPRA - RF03
 * Solo para clientes con sesión iniciada (si no, requerir_cliente manda al login
 * y después vuelve aquí). Pide la dirección de envío y guarda el pedido en la BD
 * mediante una transacción (ver datos/pedidos_dao.php).
 */
require_once __DIR__ . '/negocio/sesion.php';
require_once __DIR__ . '/negocio/validacion.php';
require_once __DIR__ . '/negocio/carrito.php';
require_once __DIR__ . '/datos/usuarios_dao.php';
require_once __DIR__ . '/datos/pedidos_dao.php';

requerir_cliente();

$carrito = carrito_detalle();
if (!$carrito['lineas']) {
    poner_mensaje('Tu carrito está vacío.', 'aviso');
    redirigir('/carrito.php');
}

$cliente   = usuario_buscar_por_id(usuario_actual()['id']);
$direccion = $cliente['direccion'] ?? '';
$error     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    comprobar_csrf();
    $direccion = post_texto('direccion');

    if (mb_strlen($direccion) < 10) {
        $error = 'Escribe una dirección de envío completa.';
    } else {
        try {
            $pedido_id = pedido_crear($cliente['id'], $direccion, $carrito['lineas']);
            carrito_vaciar();
            poner_mensaje('¡Pedido nº ' . $pedido_id . ' realizado! Gracias por tu compra.');
            redirigir('/mis_pedidos.php');
        } catch (RuntimeException $ex) {
            $error = $ex->getMessage();        // p. ej. falta de stock
        }
    }
}

$titulo = 'Confirmar pedido';
require __DIR__ . '/vistas/cabecera.php';
?>
<h1>Confirmar pedido</h1>
<?php if ($error): ?><div class="mensaje mensaje-error"><?= e($error) ?></div><?php endif; ?>

<div class="dos-columnas">
    <section class="caja-form">
        <h2>Resumen</h2>
        <ul class="resumen">
            <?php foreach ($carrito['lineas'] as $l): ?>
                <li><span><?= $l['cantidad'] ?> × <?= e($l['nombre']) ?> (T. <?= e((string)(float)$l['talla']) ?>)</span>
                    <span><?= precio($l['subtotal']) ?></span></li>
            <?php endforeach; ?>
            <li class="total"><span>Total</span><span><?= precio($carrito['total']) ?></span></li>
        </ul>
        <a href="carrito.php">Modificar carrito</a>
    </section>

    <section class="caja-form">
        <h2>Datos de envío</h2>
        <p><?= e($cliente['nombre'] . ' ' . $cliente['apellidos']) ?><br><?= e($cliente['email']) ?></p>
        <form method="post" action="confirmar.php" class="validar" novalidate>
            <?= campo_csrf() ?>
            <label>Dirección de envío *
                <textarea name="direccion" rows="3" minlength="10" required><?= e($direccion) ?></textarea>
            </label>
            <button class="boton boton-destacado boton-ancho" type="submit">Confirmar compra</button>
        </form>
    </section>
</div>
<?php require __DIR__ . '/vistas/pie.php'; ?>
