<?php
/**
 * CARRITO DE LA COMPRA - RF03
 * GET  -> muestra el carrito guardado en la sesión.
 * POST -> acción sobre el carrito: agregar, actualizar, quitar o vaciar.
 * Patrón PRG (Post/Redirect/Get): tras cada POST se redirige, así al
 * recargar la página no se vuelve a enviar el formulario.
 */
require_once __DIR__ . '/negocio/sesion.php';
require_once __DIR__ . '/negocio/validacion.php';
require_once __DIR__ . '/negocio/carrito.php';

if (es_admin()) {
    poner_mensaje('El administrador no puede realizar compras.', 'aviso');
    redirigir('/admin/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    comprobar_csrf();
    $accion   = post_texto('accion');
    $id       = leer_entero(INPUT_POST, 'id');
    $cantidad = leer_entero(INPUT_POST, 'cantidad') ?? 1;

    switch ($accion) {
        case 'agregar':
            $error = $id ? carrito_agregar($id, $cantidad) : 'Artículo no válido.';
            $error ? poner_mensaje($error, 'error') : poner_mensaje('Artículo añadido al carrito.');
            // Vuelve a la página desde la que se añadió (catálogo o ficha)
            $origen = $_SERVER['HTTP_REFERER'] ?? '';
            if ($origen && !str_contains($origen, 'carrito.php') && parse_url($origen, PHP_URL_HOST) === $_SERVER['HTTP_HOST']) {
                header('Location: ' . $origen);
                exit;
            }
            break;
        case 'actualizar':
            if ($id) carrito_actualizar($id, $cantidad);
            break;
        case 'quitar':
            if ($id) carrito_actualizar($id, 0);
            poner_mensaje('Artículo eliminado del carrito.');
            break;
        case 'vaciar':
            carrito_vaciar();
            poner_mensaje('Carrito vaciado.');
            break;
    }
    redirigir('/carrito.php');
}

$carrito = carrito_detalle();
$titulo  = 'Carrito';
require __DIR__ . '/vistas/cabecera.php';
?>
<h1>Tu carrito</h1>

<?php if (!$carrito['lineas']): ?>
    <p class="vacio">El carrito está vacío. <a href="index.php">Ver el catálogo</a></p>
<?php else: ?>
    <table class="tabla">
        <thead>
            <tr><th>Artículo</th><th>Precio</th><th>Cantidad</th><th>Subtotal</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($carrito['lineas'] as $l): ?>
            <tr>
                <td><strong><?= e($l['nombre']) ?></strong><br>
                    <small><?= e($l['marca']) ?> · Talla <?= e((string)(float)$l['talla']) ?></small></td>
                <td><?= precio($l['precio']) ?></td>
                <td>
                    <!-- Cambiar cantidad: se envía al cambiar el número (ver validacion.js) -->
                    <form method="post" action="carrito.php" class="auto-enviar">
                        <?= campo_csrf() ?>
                        <input type="hidden" name="accion" value="actualizar">
                        <input type="hidden" name="id" value="<?= $l['id'] ?>">
                        <input class="cantidad" type="number" name="cantidad" value="<?= $l['cantidad'] ?>"
                               min="1" max="<?= $l['stock'] ?>">
                        <noscript><button type="submit">OK</button></noscript>
                    </form>
                </td>
                <td><?= precio($l['subtotal']) ?></td>
                <td>
                    <form method="post" action="carrito.php">
                        <?= campo_csrf() ?>
                        <input type="hidden" name="accion" value="quitar">
                        <input type="hidden" name="id" value="<?= $l['id'] ?>">
                        <button class="boton-enlace" type="submit">Quitar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr><td colspan="3" class="derecha"><strong>Total</strong></td>
                <td colspan="2"><strong><?= precio($carrito['total']) ?></strong></td></tr>
        </tfoot>
    </table>

    <div class="acciones">
        <form method="post" action="carrito.php">
            <?= campo_csrf() ?>
            <input type="hidden" name="accion" value="vaciar">
            <button class="boton boton-secundario" type="submit">Vaciar carrito</button>
        </form>
        <a class="boton" href="index.php">Seguir comprando</a>
        <a class="boton boton-destacado" href="confirmar.php">Tramitar pedido</a>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/vistas/pie.php'; ?>
