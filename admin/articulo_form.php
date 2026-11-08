<?php
/**
 * RF02 - CREAR O MODIFICAR UN ARTÍCULO (solo administrador)
 * articulo_form.php       -> alta
 * articulo_form.php?id=3  -> modificación
 * El formulario usa enctype="multipart/form-data" para poder subir la foto ($_FILES).
 */
require_once __DIR__ . '/../negocio/sesion.php';
require_once __DIR__ . '/../negocio/validacion.php';
require_once __DIR__ . '/../datos/articulos_dao.php';

requerir_admin();

$id      = leer_entero(INPUT_GET, 'id');
$es_alta = $id === null;
$errores = [];

if ($es_alta) {
    $datos = ['nombre' => '', 'marca' => '', 'descripcion' => '', 'talla' => '',
              'precio' => '', 'color' => '', 'stock' => '0', 'imagen' => null, 'activo' => 1];
} else {
    $datos = articulo_buscar_por_id($id);
    if (!$datos) {
        poner_mensaje('Artículo no encontrado.', 'error');
        redirigir('/admin/articulos.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    comprobar_csrf();
    $imagen_anterior = $datos['imagen'];

    // Recoger datos del formulario. La coma decimal se cambia por punto (42,5 -> 42.5)
    $datos = [
        'nombre'      => post_texto('nombre'),
        'marca'       => post_texto('marca'),
        'descripcion' => post_texto('descripcion'),
        'talla'       => str_replace(',', '.', post_texto('talla')),
        'precio'      => str_replace(',', '.', post_texto('precio')),
        'color'       => post_texto('color'),
        'stock'       => post_texto('stock'),
        'imagen'      => $imagen_anterior,
        'activo'      => filter_has_var(INPUT_POST, 'activo') ? 1 : 0,
    ];
    $errores = validar_articulo($datos);

    if (!$errores) {
        try {
            $nueva = guardar_imagen_subida();
            if ($nueva) {
                // Si se sube una foto nueva, se borra la anterior del disco
                if ($imagen_anterior && is_file(RUTA_IMAGENES . $imagen_anterior)) {
                    unlink(RUTA_IMAGENES . $imagen_anterior);
                }
                $datos['imagen'] = $nueva;
            }
        } catch (RuntimeException $ex) {
            $errores['imagen'] = $ex->getMessage();
        }
    }

    if (!$errores) {
        $datos['talla']  = (float) $datos['talla'];
        $datos['precio'] = (float) $datos['precio'];
        $color = trim($datos['color']);
        $datos['color']  = $color === '' ? null : $color;
        $datos['stock']  = (int) $datos['stock'];

        if ($es_alta) {
            articulo_insertar($datos);
        } else {
            articulo_actualizar($id, $datos);
        }
        poner_mensaje($es_alta ? 'Artículo creado.' : 'Artículo modificado.');
        redirigir('/admin/articulos.php');
    }
}

$err = fn(string $c) => isset($errores[$c]) ? '<span class="error-campo">' . e($errores[$c]) . '</span>' : '';

$titulo = $es_alta ? 'Nuevo artículo' : 'Editar artículo';
require __DIR__ . '/../vistas/cabecera.php';
?>
<p><a href="articulos.php">&larr; Volver a artículos</a></p>
<section class="caja-form">
    <h1><?= e($titulo) ?></h1>
    <form method="post" enctype="multipart/form-data" class="validar" novalidate>
        <?= campo_csrf() ?>
        <div class="dos-columnas">
            <label>Nombre *
                <input type="text" name="nombre" value="<?= e($datos['nombre']) ?>" maxlength="100" required>
                <?= $err('nombre') ?>
            </label>
            <label>Marca *
                <input type="text" name="marca" value="<?= e($datos['marca']) ?>" maxlength="50" required>
                <?= $err('marca') ?>
            </label>
        </div>

        <label>Descripción
            <textarea name="descripcion" rows="3"><?= e($datos['descripcion']) ?></textarea>
        </label>

        <label>Color
            <input type="text" name="color" value="<?= e($datos['color'] ?? '') ?>" maxlength="30" placeholder="Ej.: Azul marino">
            <?= $err('color') ?>
        </label>

        <div class="tres-columnas">
            <label>Talla *
                <input type="number" name="talla" value="<?= e((string)$datos['talla']) ?>" step="0.5" min="15" max="50" required>
                <?= $err('talla') ?>
            </label>
            <label>Precio (€) *
                <input type="number" name="precio" value="<?= e((string)$datos['precio']) ?>" step="0.01" min="0" required>
                <?= $err('precio') ?>
            </label>
            <label>Stock *
                <input type="number" name="stock" value="<?= e((string)$datos['stock']) ?>" min="0" step="1" required>
                <?= $err('stock') ?>
            </label>
        </div>

        <label>Foto (JPG, PNG o WEBP, máx. 2 MB)
            <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp">
            <?= $err('imagen') ?>
        </label>
        <?php if (!empty($datos['imagen'])): ?>
            <div class="miniatura"><?php $art = $datos; require __DIR__ . '/../vistas/imagen_articulo.php'; ?></div>
        <?php endif; ?>

        <label class="casilla">
            <input type="checkbox" name="activo" <?= $datos['activo'] ? 'checked' : '' ?>> A la venta (visible en el catálogo)
        </label>

        <button class="boton" type="submit"><?= $es_alta ? 'Crear artículo' : 'Guardar cambios' ?></button>
    </form>
</section>
<?php require __DIR__ . '/../vistas/pie.php'; ?>
