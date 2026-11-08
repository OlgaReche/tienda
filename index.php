<?php
/**
 * CATÁLOGO (página de inicio) - RF03
 * Cualquiera puede ver los artículos. Visitantes y clientes pueden añadirlos al carrito.
 * La búsqueda se envía por GET para que la URL se pueda compartir (?q=nike).
 */
require_once __DIR__ . '/negocio/sesion.php';
require_once __DIR__ . '/negocio/validacion.php';
require_once __DIR__ . '/datos/articulos_dao.php';

$busqueda  = trim(filter_input(INPUT_GET, 'q') ?? '');
$articulos = articulo_listar_activos($busqueda);

$titulo = 'Catálogo';
require __DIR__ . '/vistas/cabecera.php';
?>
<section class="portada">
    <h1>Zapatillas para correr, entrenar y vivir</h1>
    <form class="buscador" method="get" action="index.php">
        <input type="search" name="q" value="<?= e($busqueda) ?>" placeholder="Buscar por modelo o marca…">
        <button class="boton" type="submit">Buscar</button>
        <?php if ($busqueda !== ''): ?><a href="index.php">Quitar filtro</a><?php endif; ?>
    </form>
</section>

<?php if (!$articulos): ?>
    <p class="vacio">No hay artículos que coincidan con la búsqueda.</p>
<?php endif; ?>

<div class="rejilla">
    <?php foreach ($articulos as $art): ?>
        <article class="tarjeta">
            <a href="articulo.php?id=<?= $art['id'] ?>">
                <?php require __DIR__ . '/vistas/imagen_articulo.php'; ?>
            </a>
            <div class="tarjeta-cuerpo">
                <p class="marca"><?= e($art['marca']) ?></p>
                <h2><a href="articulo.php?id=<?= $art['id'] ?>"><?= e($art['nombre']) ?></a></h2>
                <p class="detalle">Talla <?= e((string)(float)$art['talla']) ?></p>
                <p class="precio"><?= precio($art['precio']) ?></p>

                <?php if ($art['stock'] > 0 && $art['stock'] < 3): ?>
                    <p class="ultimas">¡Últimas unidades!</p>
                <?php endif; ?>

                <?php if ($art['stock'] == 0): ?>
                    <p class="agotado">Agotado</p>
                <?php elseif (!es_admin()): ?>
                    <!-- Añadir al carrito: se envía por POST a carrito.php -->
                    <form method="post" action="carrito.php">
                        <?= campo_csrf() ?>
                        <input type="hidden" name="accion" value="agregar">
                        <input type="hidden" name="id" value="<?= $art['id'] ?>">
                        <button class="boton boton-ancho" type="submit">Añadir al carrito</button>
                    </form>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</div>
<?php require __DIR__ . '/vistas/pie.php'; ?>
