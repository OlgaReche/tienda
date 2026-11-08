<?php
/**
 * CAPA DE NEGOCIO - Usuarios
 * Reglas de negocio de registro, login y gestión de usuarios.
 * Las páginas llaman a estas funciones; estas llaman a la capa de datos.
 */
require_once __DIR__ . '/validacion.php';
require_once __DIR__ . '/../datos/usuarios_dao.php';

/** Lee del POST todos los campos del formulario de usuario. */
function recoger_datos_usuario(): array
{
    return [
        'nombre'    => post_texto('nombre'),
        'apellidos' => post_texto('apellidos'),
        'email'     => strtolower(post_texto('email')),
        'password'  => filter_input(INPUT_POST, 'password') ?? '',   // sin trim: los espacios cuentan
        'password2' => filter_input(INPUT_POST, 'password2') ?? '',
        'telefono'  => str_replace(' ', '', post_texto('telefono')),
        'direccion' => post_texto('direccion'),
        // El rol solo lo puede elegir el admin; el resto de casos lo fuerzan después
        'rol'       => post_texto('rol') === 'admin' ? 'admin' : 'cliente',
    ];
}

/**
 * Da de alta o modifica un usuario tras validarlo.
 * @param array    $d  Datos recogidos con recoger_datos_usuario()
 * @param int|null $id null = alta nueva; número = modificación
 * @return array       Errores (vacío si se ha guardado)
 */
function guardar_usuario(array $d, ?int $id = null): array
{
    $errores = validar_usuario($d, $id === null);

    // El email no se puede repetir (también lo impide el UNIQUE de la BD)
    $existente = usuario_buscar_por_email($d['email']);
    if ($existente && (int)$existente['id'] !== $id) {
        $errores['email'] = 'Ya existe una cuenta con ese email.';
    }
    if ($errores) {
        return $errores;
    }

    // Nunca se guarda la contraseña en claro: se cifra con password_hash()
    $d['password']  = $d['password'] !== '' ? password_hash($d['password'], PASSWORD_DEFAULT) : '';
    $d['telefono']  = $d['telefono']  ?: null;
    $d['direccion'] = $d['direccion'] ?: null;

    if ($id === null) {
        usuario_insertar($d);
    } else {
        usuario_actualizar($id, $d);
    }
    return [];
}

/**
 * Comprueba email y contraseña contra la base de datos.
 * @return array|null El usuario si los datos son correctos
 */
function autenticar(string $email, string $password): ?array
{
    $usuario = usuario_buscar_por_email(strtolower(trim($email)));
    if ($usuario && password_verify($password, $usuario['password'])) {
        return $usuario;
    }
    return null;    // mismo mensaje si falla el email o la contraseña (más seguro)
}
