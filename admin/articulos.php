<?php
/**
 * RF02 - LISTADO Y BORRADO DE ARTÍCULOS (solo administrador)
 * Si un artículo ya aparece en algún pedido, no se borra (se perdería el
 * historial): en su lugar se desactiva y deja de verse en el catálogo.
 */
require_once __DIR__ . '/../negocio/sesion.php';
require_once __DIR__ . '/../negocio/validacion.php';
require_once __DIR__ . '/../datos/articulos_dao.php';

requerir_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    comprobar_csrf();
    $id  = leer_entero(INPUT_POST, 'id');
    $art = $id ? articulo_buscar_por_id($id) : null;

    if (!$art) {
        poner_mensaje('Artículo no encontrado.', 'error');
    } elseif (articulo_tiene_pedidos($id)) {
        $art['activo'] = 0;
        articulo_actualizar($id, $art);
        poner_mensaje('"' . $art['nombre'] . '" tiene pedidos: se ha desactivado en lugar de borrarlo.', 'aviso');
    } else {
        articulo_eliminar($id);
        if ($art['imagen'] && is_file(RUTA_IMAGENES . $art['imagen'])) {
            unlink(RUTA_IMAGENES . $art['imagen']);      // borra también la foto
        }
        poner_mensaje('Artículo "' . $art['nombre'] . '" eliminado.');
    }
    redirigir('/admin/articulos.php');
    <?php if ($art['color']): ?><li><strong>C
        <!-- -->olor:</strong> <?= e($art['color']) ?></li><?php endif; ?>
}

$articulos = articulo_listar_todos();
$titulo    = 'Artículos';
require __DIR__ . '/../vistas/cabecera.php';
?>
<div class="titulo-con-boton">
    <h1>Artículos</h1>
    <a class="boton" href="articulo_form.php">+ Nuevo artículo</a>
</div>

<table class="tabla">
    <thead>
        <tr><th></th><th>Artículo</th><th>Talla</th><th>Precio</th><th>Color</th><th>Stock</th><th>Estado</th><th>Acciones</th></tr>
    </thead>
    <tbody>
    <?php foreach ($articulos as $art): ?>
        <tr class="<?= $art['activo'] ? '' : 'inactivo' ?>">
            <td class="miniatura"><?php require __DIR__ . '/../vistas/imagen_articulo.php'; ?></td>
            <td><strong><?= e($art['nombre']) ?></strong><br><small><?= e($art['marca']) ?></small></td>
            <td><?= e((string)(float)$art['talla']) ?></td>
            <td><?= precio($art['precio']) ?></td>
            <td><?= e($art['color']) ?></td>
            <td class="<?= $art['stock'] == 0 ? 'agotado' : '' ?>"><?= (int)$art['stock'] ?></td>
            <td><?= $art['activo'] ? 'A la venta' : 'Oculto' ?></td>
            <td class="celda-acciones">
                <a href="articulo_form.php?id=<?= $art['id'] ?>">Editar</a>
                <form method="post" action="articulos.php" data-confirmar="¿Eliminar &quot;<?= e($art['nombre']) ?>&quot;?">
                    <?= campo_csrf() ?>
                    <input type="hidden" name="id" value="<?= $art['id'] ?>">
                    <button class="boton-enlace peligro" type="submit">Eliminar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../vistas/pie.php'; ?>
