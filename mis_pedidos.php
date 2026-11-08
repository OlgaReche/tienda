<?php
/**
 * MIS PEDIDOS - historial de compras del cliente que ha iniciado sesión.
 * Solo muestra SUS pedidos: el id se toma de la sesión, nunca de la URL.
 */
require_once __DIR__ . '/negocio/sesion.php';
require_once __DIR__ . '/datos/pedidos_dao.php';

requerir_cliente();
$pedidos = pedido_listar_por_usuario(usuario_actual()['id']);

$titulo = 'Mis pedidos';
require __DIR__ . '/vistas/cabecera.php';
?>
<h1>Mis pedidos</h1>

<?php if (!$pedidos): ?>
    <p class="vacio">Todavía no has hecho ningún pedido. <a href="index.php">Ir al catálogo</a></p>
<?php endif; ?>

<?php foreach ($pedidos as $p): ?>
    <details class="pedido">
        <summary>
            <span>Pedido nº <?= $p['id'] ?></span>
            <span><?= date('d/m/Y H:i', strtotime($p['fecha'])) ?></span>
            <span class="estado estado-<?= e($p['estado']) ?>"><?= e($p['estado']) ?></span>
            <strong><?= precio($p['total']) ?></strong>
        </summary>
        <p><strong>Envío:</strong> <?= e($p['direccion_envio']) ?></p>
        <table class="tabla">
            <tr><th>Artículo</th><th>Cantidad</th><th>Precio</th></tr>
            <?php foreach (pedido_lineas($p['id']) as $l): ?>
                <tr><td><?= e($l['marca'] . ' ' . $l['nombre']) ?> (T. <?= e((string)(float)$l['talla']) ?>)</td>
                    <td><?= $l['cantidad'] ?></td><td><?= precio($l['precio_unitario']) ?></td></tr>
            <?php endforeach; ?>
        </table>
    </details>
<?php endforeach; ?>
<?php require __DIR__ . '/vistas/pie.php'; ?>
