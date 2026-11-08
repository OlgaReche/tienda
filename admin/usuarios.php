<?php
/**
 * RF01 - LISTADO Y BORRADO DE USUARIOS (solo administrador)
 * El borrado se hace por POST (nunca con un enlace GET) y se protege:
 *  - no se puede borrar uno mismo
 *  - no se puede borrar el último administrador
 *  - no se puede borrar un cliente que tiene pedidos (FK en la BD)
 */
require_once __DIR__ . '/../negocio/sesion.php';
require_once __DIR__ . '/../negocio/validacion.php';
require_once __DIR__ . '/../datos/usuarios_dao.php';

requerir_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    comprobar_csrf();
    $id      = leer_entero(INPUT_POST, 'id');
    $usuario = $id ? usuario_buscar_por_id($id) : null;

    if (!$usuario) {
        poner_mensaje('Usuario no encontrado.', 'error');
    } elseif ($id === usuario_actual()['id']) {
        poner_mensaje('No puedes eliminar tu propia cuenta.', 'error');
    } elseif ($usuario['rol'] === 'admin' && usuario_contar_admins() <= 1) {
        poner_mensaje('Debe quedar al menos un administrador.', 'error');
    } elseif (usuario_contar_pedidos($id) > 0) {
        poner_mensaje('No se puede eliminar: el cliente tiene pedidos registrados.', 'error');
    } else {
        usuario_eliminar($id);
        poner_mensaje('Usuario "' . $usuario['email'] . '" eliminado.');
    }
    redirigir('/admin/usuarios.php');
}

$usuarios = usuario_listar();
$titulo   = 'Usuarios';
require __DIR__ . '/../vistas/cabecera.php';
?>
<div class="titulo-con-boton">
    <h1>Usuarios</h1>
    <a class="boton" href="usuario_form.php">+ Nuevo usuario</a>
</div>

<table class="tabla">
    <thead>
        <tr><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Rol</th><th>Alta</th><th>Acciones</th></tr>
    </thead>
    <tbody>
    <?php foreach ($usuarios as $u): ?>
        <tr>
            <td><?= e($u['nombre'] . ' ' . $u['apellidos']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td><?= e($u['telefono'] ?? '—') ?></td>
            <td><span class="etiqueta etiqueta-<?= e($u['rol']) ?>"><?= e($u['rol']) ?></span></td>
            <td><?= date('d/m/Y', strtotime($u['fecha_alta'])) ?></td>
            <td class="celda-acciones">
                <a href="usuario_form.php?id=<?= $u['id'] ?>">Editar</a>
                <?php if ($u['id'] != usuario_actual()['id']): ?>
                    <!-- data-confirmar: validacion.js pide confirmación antes de enviar -->
                    <form method="post" action="usuarios.php" data-confirmar="¿Eliminar a <?= e($u['email']) ?>?">
                        <?= campo_csrf() ?>
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <button class="boton-enlace peligro" type="submit">Eliminar</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../vistas/pie.php'; ?>
