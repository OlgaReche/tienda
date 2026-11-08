<?php
/**
 * DETALLE DE UN ARTÍCULO - RF03
 * Recibe el id por GET (articulo.php?id=3) y permite elegir cantidad.
 */
require_once __DIR__ . '/negocio/sesion.php';
require_once __DIR__ . '/negocio/validacion.php';
require_once __DIR__ . '/datos/articulos_dao.php';

$id  = leer_entero(INPUT_GET, 'id');
$art = $id ? articulo_buscar_por_id($id) : null;

if (!$art || !$art['activo']) {
    poner_mensaje('El artículo no existe o ya no está disponible.', 'error');
    redirigir('/index.php');
}

$titulo = $art['nombre'];
require __DIR__ . '/vistas/cabecera.php';
?>
<p><a href="index.php">&larr; Volver al catálogo</a></p>

<div class="ficha">
    <?php require __DIR__ . '/vistas/imagen_articulo.php'; ?>
    <div>
        <p class="marca"><?= e($art['marca']) ?></p>
        <h1><?= e($art['nombre']) ?></h1>
        <p class="precio grande"><?= precio($art['precio']) ?></p>
        <p><?= nl2br(e($art['descripcion'])) ?></p>
        <ul class="datos">
            <li><strong>Talla:</strong> <?= e((string)(float)$art['talla']) ?></li>
            <li><strong>Disponibles:</strong> <?= (int)$art['stock'] ?> unidades</li>
        </ul>

        <?php if ($art['stock'] == 0): ?>
            <p class="agotado">Agotado</p>
        <?php elseif (!es_admin()): ?>
            <form class="fila-form" method="post" action="carrito.php">
                <?= campo_csrf() ?>
                <input type="hidden" name="accion" value="agregar">
                <input type="hidden" name="id" value="<?= $art['id'] ?>">
                <label>Cantidad
                    <input type="number" name="cantidad" value="1" min="1" max="<?= (int)$art['stock'] ?>">
                </label>
                <button class="boton" type="submit">Añadir al carrito</button>
            </form>
        <?php else: ?>
            <a class="boton" href="admin/articulo_form.php?id=<?= $art['id'] ?>">Editar artículo</a>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/vistas/pie.php'; ?>
