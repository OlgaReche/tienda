<?php
/**
 * CAPA DE PRESENTACIÓN - Imagen de un artículo
 * Si el artículo no tiene foto, muestra un recuadro con la marca.
 * Necesita la variable $art (fila de la tabla articulos).
 */
if (!empty($art['imagen']) && is_file(RUTA_IMAGENES . $art['imagen'])): ?>
    <img class="foto" src="<?= URL_IMAGENES . e($art['imagen']) ?>" alt="<?= e($art['nombre']) ?>">
<?php else: ?>
    <div class="foto foto-vacia"><span><?= e($art['marca']) ?></span></div>
<?php endif; ?>
