<?php
/**
 * CAPA DE NEGOCIO - Control de sesiones 
 * Se incluye al principio de TODAS las páginas.
 *
 * En $_SESSION guardamos:
 *   - 'usuario'  => datos del usuario que ha iniciado sesión (id, nombre, rol)
 *   - 'carrito'  => [id_articulo => cantidad]
 *   - 'ultimo_acceso' => marca de tiempo para cerrar por inactividad
 *   - 'mensaje'  => aviso de un solo uso para mostrar en la siguiente página
 */
require_once __DIR__ . '/../config/config.php';

// Cookie de sesión más segura: no accesible desde JavaScript
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

// ---- Cierre automático por inactividad ----
if (isset($_SESSION['usuario'], $_SESSION['ultimo_acceso'])
    && time() - $_SESSION['ultimo_acceso'] > MINUTOS_SESION * 60) {
    cerrar_sesion();
    poner_mensaje('Tu sesión ha caducado por inactividad. Vuelve a iniciar sesión.', 'aviso');
}
$_SESSION['ultimo_acceso'] = time();

// ---- Funciones de sesión ----

/** Guarda en la sesión al usuario autenticado. */
function iniciar_sesion(array $usuario): void
{
    session_regenerate_id(true);       // nuevo id de sesión: evita robo de sesión
    $_SESSION['usuario'] = [
        'id'     => (int) $usuario['id'],
        'nombre' => $usuario['nombre'],
        'email'  => $usuario['email'],
        'rol'    => $usuario['rol'],
    ];
}

/**
 * Cierra la sesión: borra todos sus datos (usuario y carrito) y cambia el
 * identificador de sesión. session_regenerate_id(true) elimina la sesión
 * antigua del servidor, así que el id anterior ya no sirve para nada.
 * La sesión nueva queda vacía y solo se usa para mostrar el aviso.
 */
function cerrar_sesion(): void
{
    $_SESSION = [];
    session_regenerate_id(true);
}

function usuario_actual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function es_admin(): bool
{
    return (usuario_actual()['rol'] ?? '') === 'admin';
}

function es_cliente(): bool
{
    return (usuario_actual()['rol'] ?? '') === 'cliente';
}

/** Si no hay sesión iniciada, manda al login y recuerda a qué página quería ir. */
function requerir_login(): void
{
    if (!usuario_actual()) {
        $_SESSION['volver_a'] = $_SERVER['REQUEST_URI'];
        poner_mensaje('Inicia sesión para continuar.', 'aviso');
        redirigir('/login.php');
    }
}

/** Protege las páginas de administración. */
function requerir_admin(): void
{
    requerir_login();
    if (!es_admin()) {
        poner_mensaje('No tienes permiso para acceder a esa página.', 'error');
        redirigir('/index.php');
    }
}

/** Protege las páginas de compra (el administrador no compra). */
function requerir_cliente(): void
{
    requerir_login();
    if (!es_cliente()) {
        poner_mensaje('Esa sección es solo para clientes.', 'error');
        redirigir('/admin/index.php');
    }
}

// ---- Mensajes "flash": se muestran una vez y se borran ----

function poner_mensaje(string $texto, string $tipo = 'ok'): void
{
    $_SESSION['mensaje'] = ['texto' => $texto, 'tipo' => $tipo];
}

function sacar_mensaje(): ?array
{
    $m = $_SESSION['mensaje'] ?? null;
    unset($_SESSION['mensaje']);
    return $m;
}

// ---- Utilidades ----

/** Redirección con header Location. Siempre seguida de exit. */
function redirigir(string $ruta): void
{
    header('Location: ' . URL_BASE . $ruta);
    exit;
}

/** Escapa texto antes de imprimirlo en HTML (evita XSS). */
function e(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

function precio(float $valor): string
{
    return number_format($valor, 2, ',', '.') . ' €';
}

// ---- Token CSRF: comprueba que los formularios POST vienen de nuestra web ----

function token_csrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function campo_csrf(): string
{
    return '<input type="hidden" name="csrf" value="' . token_csrf() . '">';
}

function comprobar_csrf(): void
{
    $token = filter_input(INPUT_POST, 'csrf') ?? '';
    if (!hash_equals(token_csrf(), $token)) {
        http_response_code(400);
        exit('Petición no válida. Vuelve atrás y recarga la página.');
    }
}
