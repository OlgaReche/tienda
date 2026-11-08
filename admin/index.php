<?php
/**
 * PANEL DEL ADMINISTRADOR
 * Todas las páginas de /admin empiezan con requerir_admin():
 * si no hay sesión o el usuario no es admin, no se puede entrar.
 */
require_once __DIR__ . '/../negocio/sesion.php';
require_once __DIR__ . '/../datos/conexion.php';

requerir_admin();

// Pequeño resumen con consultas de agregación
$resumen = obtener_fila("SELECT
    (SELECT COUNT(*) FROM usuarios WHERE rol = 'cliente') AS clientes,
    (SELECT COUNT(*) FROM articulos WHERE activo = 1)     AS articulos,
    (SELECT COUNT(*) FROM articulos WHERE stock = 0)      AS agotados,
    (SELECT COUNT(*) FROM pedidos)                        AS pedidos,
    (SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE estado <> 'cancelado') AS ventas");

$titulo = 'Panel de administración';
require __DIR__ . '/../vistas/cabecera.php';
?>
<h1>Panel de administración</h1>

<div class="indicadores">
    <a href="usuarios.php"  class="indicador"><strong><?= $resumen['clientes'] ?></strong> clientes</a>
    <a href="articulos.php" class="indicador"><strong><?= $resumen['articulos'] ?></strong> artículos a la venta</a>
    <a href="articulos.php" class="indicador"><strong><?= $resumen['agotados'] ?></strong> agotados</a>
    <a href="pedidos.php"   class="indicador"><strong><?= $resumen['pedidos'] ?></strong> pedidos</a>
    <div class="indicador"><strong><?= precio($resumen['ventas']) ?></strong> vendidos</div>
</div>

<div class="acciones">
    <a class="boton" href="usuario_form.php">+ Nuevo usuario</a>
    <a class="boton" href="articulo_form.php">+ Nuevo artículo</a>
</div>
<?php require __DIR__ . '/../vistas/pie.php'; ?>
