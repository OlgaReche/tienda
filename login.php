<?php
/**
 * FORMULARIO DE ACCESO para usuarios ya registrados.
 * - Recoge email y contraseña por POST.
 * - Si son correctos, guarda el usuario en la sesión y redirige según su rol.
 * - Cookie opcional "recordar email".
 */
require_once __DIR__ . '/negocio/sesion.php';
require_once __DIR__ . '/negocio/usuarios.php';

if (usuario_actual()) {               // ya ha iniciado sesión
    redirigir(es_admin() ? '/admin/index.php' : '/index.php');
}

$error = '';
// Si hay cookie, se rellena el email automáticamente
$email = $_COOKIE['recordar_email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    comprobar_csrf();
    $email    = post_texto('email');
    $password = filter_input(INPUT_POST, 'password') ?? '';
    $recordar = filter_has_var(INPUT_POST, 'recordar');      // checkbox marcado

    $usuario = autenticar($email, $password);

    if ($usuario) {
        iniciar_sesion($usuario);

        // Cookie de 30 días con el email, o se borra si no se marcó la casilla
        if ($recordar) {
            setcookie('recordar_email', $usuario['email'], time() + 60 * 60 * 24 * 30, '/');
        } else {
            setcookie('recordar_email', '', time() - 3600, '/');
        }

        poner_mensaje('Bienvenido/a, ' . $usuario['nombre'] . '.');

        // Vuelve a la página que quería ver antes del login, si la había
        $volver = $_SESSION['volver_a'] ?? null;
        unset($_SESSION['volver_a']);
        if ($volver && str_starts_with($volver, URL_BASE . '/')) {
            header('Location: ' . $volver);
            exit;
        }
        redirigir($usuario['rol'] === 'admin' ? '/admin/index.php' : '/index.php');
    }
    $error = 'Email o contraseña incorrectos.';
}

$titulo = 'Iniciar sesión';
require __DIR__ . '/vistas/cabecera.php';
?>
<section class="caja-form estrecha">
    <h1>Iniciar sesión</h1>
    <?php if ($error): ?><div class="mensaje mensaje-error"><?= e($error) ?></div><?php endif; ?>

    <form method="post" action="login.php" class="validar" novalidate>
        <?= campo_csrf() ?>
        <label>Email
            <input type="email" name="email" value="<?= e($email) ?>" required autofocus>
        </label>
        <label>Contraseña
            <input type="password" name="password" required>
        </label>
        <label class="casilla">
            <input type="checkbox" name="recordar" <?= $email ? 'checked' : '' ?>> Recordar mi email
        </label>
        <button class="boton boton-ancho" type="submit">Entrar</button>
    </form>
    <p>¿Eres nuevo? <a href="registro.php">Crea tu cuenta</a></p>
</section>
<?php require __DIR__ . '/vistas/pie.php'; ?>
