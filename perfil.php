<?php
/**
 * MI PERFIL - el cliente modifica sus propios datos.
 * El id se toma de la sesión, así nadie puede editar el perfil de otro.
 */
require_once __DIR__ . '/negocio/sesion.php';
require_once __DIR__ . '/negocio/usuarios.php';

requerir_cliente();
$id      = usuario_actual()['id'];
$datos   = usuario_buscar_por_id($id);
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    comprobar_csrf();
    $datos        = recoger_datos_usuario();
    $datos['rol'] = 'cliente';              // el cliente no puede cambiarse el rol
    $errores      = guardar_usuario($datos, $id);

    if (!$errores) {
        $_SESSION['usuario']['nombre'] = $datos['nombre'];   // actualiza el saludo
        $_SESSION['usuario']['email']  = $datos['email'];
        poner_mensaje('Datos actualizados.');
        redirigir('/perfil.php');
    }
}

$titulo           = 'Mi perfil';
$pass_obligatoria = false;
require __DIR__ . '/vistas/cabecera.php';
?>
<section class="caja-form">
    <h1>Mi perfil</h1>
    <form method="post" action="perfil.php" class="validar" novalidate>
        <?= campo_csrf() ?>
        <?php require __DIR__ . '/vistas/campos_usuario.php'; ?>
        <button class="boton" type="submit">Guardar cambios</button>
    </form>
</section>
<?php require __DIR__ . '/vistas/pie.php'; ?>
