<?php
/**
 * FORMULARIO DE REGISTRO para clientes nuevos.
 * Obligatorios: nombre, apellidos, email y contraseña.
 * Opcionales: teléfono y dirección.
 * El rol siempre es "cliente": un visitante no puede registrarse como admin.
 */
require_once __DIR__ . '/negocio/sesion.php';
require_once __DIR__ . '/negocio/usuarios.php';

if (usuario_actual()) {
    redirigir('/index.php');
}

$datos   = ['nombre' => '', 'apellidos' => '', 'email' => '', 'telefono' => '', 'direccion' => ''];
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    comprobar_csrf();
    $datos        = recoger_datos_usuario();
    $datos['rol'] = 'cliente';                 // se fuerza el rol por seguridad
    $errores      = guardar_usuario($datos);

    if (!$errores) {
        // Tras registrarse, se inicia la sesión directamente
        iniciar_sesion(usuario_buscar_por_email($datos['email']));
        poner_mensaje('¡Cuenta creada! Ya puedes comprar.');
        redirigir('/index.php');
    }
}

$titulo           = 'Registro';
$pass_obligatoria = true;
require __DIR__ . '/vistas/cabecera.php';
?>
<section class="caja-form">
    <h1>Crear cuenta</h1>
    <p class="nota">Los campos marcados con * son obligatorios.</p>
    <form method="post" action="registro.php" class="validar" novalidate>
        <?= campo_csrf() ?>
        <?php require __DIR__ . '/vistas/campos_usuario.php'; ?>
        <button class="boton boton-ancho" type="submit">Registrarme</button>
    </form>
    <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
</section>
<?php require __DIR__ . '/vistas/pie.php'; ?>
