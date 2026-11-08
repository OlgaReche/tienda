<?php
/**
 * CAPA DE PRESENTACIÓN - Campos del formulario de usuario
 * Se reutiliza en registro, perfil y en el formulario del administrador.
 * Necesita: $datos (valores), $errores (mensajes), $pass_obligatoria (bool).
 * Los campos con * son obligatorios; el resto son opcionales.
 */
$campo_error = function (string $c) use ($errores) {
    return isset($errores[$c]) ? '<span class="error-campo">' . e($errores[$c]) . '</span>' : '';
};
?>
<div class="dos-columnas">
    <label>Nombre *
        <input type="text" name="nombre" value="<?= e($datos['nombre']) ?>" maxlength="50" required>
        <?= $campo_error('nombre') ?>
    </label>
    <label>Apellidos *
        <input type="text" name="apellidos" value="<?= e($datos['apellidos']) ?>" maxlength="100" required>
        <?= $campo_error('apellidos') ?>
    </label>
</div>

<label>Email *
    <input type="email" name="email" value="<?= e($datos['email']) ?>" maxlength="120" required>
    <?= $campo_error('email') ?>
</label>

<div class="dos-columnas">
    <label>Contraseña <?= $pass_obligatoria ? '*' : '(déjala vacía para no cambiarla)' ?>
        <input type="password" name="password" minlength="8" <?= $pass_obligatoria ? 'required' : '' ?>
               autocomplete="new-password">
        <?= $campo_error('password') ?>
    </label>
    <label>Repetir contraseña <?= $pass_obligatoria ? '*' : '' ?>
        <input type="password" name="password2" minlength="8" <?= $pass_obligatoria ? 'required' : '' ?>
               autocomplete="new-password">
        <?= $campo_error('password2') ?>
    </label>
</div>

<div class="dos-columnas">
    <label>Teléfono (opcional)
        <input type="tel" name="telefono" value="<?= e($datos['telefono']) ?>" pattern="\+?\d{9,15}">
        <?= $campo_error('telefono') ?>
    </label>
    <label>Dirección (opcional)
        <input type="text" name="direccion" value="<?= e($datos['direccion']) ?>" maxlength="200">
        <?= $campo_error('direccion') ?>
    </label>
</div>
