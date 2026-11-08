<?php
/**
 * RF01 - CREAR O MODIFICAR UN USUARIO (solo administrador)
 * usuario_form.php        -> alta de un usuario nuevo (cliente o admin)
 * usuario_form.php?id=5   -> modificación del usuario 5
 */
require_once __DIR__ . '/../negocio/sesion.php';
require_once __DIR__ . '/../negocio/usuarios.php';

requerir_admin();

$id      = leer_entero(INPUT_GET, 'id');
$es_alta = $id === null;
$errores = [];

if ($es_alta) {
    $datos = ['nombre' => '', 'apellidos' => '', 'email' => '', 'telefono' => '',
              'direccion' => '', 'rol' => 'cliente'];
} else {
    $datos = usuario_buscar_por_id($id);
    if (!$datos) {
        poner_mensaje('Usuario no encontrado.', 'error');
        redirigir('/admin/usuarios.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    comprobar_csrf();
    $datos = recoger_datos_usuario();     // aquí el admin SÍ puede elegir el rol

    // Un admin no puede quitarse a sí mismo el rol de administrador
    if (!$es_alta && $id === usuario_actual()['id']) {
        $datos['rol'] = 'admin';
    }

    $errores = guardar_usuario($datos, $id);
    if (!$errores) {
        poner_mensaje($es_alta ? 'Usuario creado.' : 'Usuario modificado.');
        redirigir('/admin/usuarios.php');
    }
}

$titulo           = $es_alta ? 'Nuevo usuario' : 'Editar usuario';
$pass_obligatoria = $es_alta;
require __DIR__ . '/../vistas/cabecera.php';
?>
<p><a href="usuarios.php">&larr; Volver a usuarios</a></p>
<section class="caja-form">
    <h1><?= e($titulo) ?></h1>
    <form method="post" class="validar" novalidate>
        <?= campo_csrf() ?>
        <?php require __DIR__ . '/../vistas/campos_usuario.php'; ?>

        <label>Tipo de usuario *
            <select name="rol" <?= (!$es_alta && $id === usuario_actual()['id']) ? 'disabled' : '' ?>>
                <option value="cliente" <?= $datos['rol'] === 'cliente' ? 'selected' : '' ?>>Cliente</option>
                <option value="admin"   <?= $datos['rol'] === 'admin'   ? 'selected' : '' ?>>Administrador</option>
            </select>
        </label>

        <button class="boton" type="submit"><?= $es_alta ? 'Crear usuario' : 'Guardar cambios' ?></button>
    </form>
</section>
<?php require __DIR__ . '/../vistas/pie.php'; ?>
