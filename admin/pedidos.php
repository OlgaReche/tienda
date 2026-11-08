<?php
/**
 * PEDIDOS - el administrador ve todas las compras y cambia su estado.
 */
require_once __DIR__ . '/../negocio/sesion.php';
require_once __DIR__ . '/../negocio/validacion.php';
require_once __DIR__ . '/../datos/pedidos_dao.php';

requerir_admin();

$estados = ['pendiente', 'enviado', 'entregado', 'cancelado'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    comprobar_csrf();
    $id     = leer_entero(INPUT_POST, 'id');
    $estado = post_texto('estado');
    if ($id && in_array($estado, $estados, true)) {     // solo valores permitidos
        pedido_cambiar_estado($id, $estado);
        poner_mensaje('Pedido nº ' . $id . ' marcado como ' . $estado . '.');
    }
    redirigir('/admin/pedidos.php');
}

$pedidos = pedido_listar_todos();
$titulo  = 'Pedidos';
require __DIR__ . '/../vistas/cabecera.php';
?>
<h1>Pedidos</h1>
<?php if (!$pedidos): ?><p class="vacio">Aún no hay pedidos.</p><?php endif; ?>

<?php foreach ($pedidos as $p): ?>
    <details class="pedido">
        <summary>
            <span>Nº <?= $p['id'] ?></span>
            <span><?= date('d/m/Y H:i', strtotime($p['fecha'])) ?></span>
            <span><?= e($p['cliente']) ?></span>
            <span class="estado estado-<?= e($p['estado']) ?>"><?= e($p['estado']) ?></span>
            <strong><?= precio($p['total']) ?></strong>
        </summary>
        <p><strong>Email:</strong> <?= e($p['email']) ?> · <strong>Envío:</strong> <?= e($p['direccion_envio']) ?></p>
        <table class="tabla">
            <tr><th>Artículo</th><th>Cantidad</th><th>Precio</th></tr>
            <?php foreach (pedido_lineas($p['id']) as $l): ?>
                <tr><td><?= e($l['marca'] . ' ' . $l['nombre']) ?> (T. <?= e((string)(float)$l['talla']) ?>)</td>
                    <td><?= $l['cantidad'] ?></td><td><?= precio($l['precio_unitario']) ?></td></tr>
            <?php endforeach; ?>
        </table>
        <form method="post" action="pedidos.php" class="fila-form">
            <?= campo_csrf() ?>
            <input type="hidden" name="id" value="<?= $p['id'] ?>">
            <label>Estado
                <select name="estado">
                    <?php foreach ($estados as $est): ?>
                        <option <?= $est === $p['estado'] ? 'selected' : '' ?>><?= $est ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="boton" type="submit">Actualizar</button>
        </form>
    </details>
<?php endforeach; ?>
<?php require __DIR__ . '/../vistas/pie.php'; ?>
