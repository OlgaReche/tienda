<?php
/**
 * CAPA DE NEGOCIO - Recogida y validación de datos de formularios
 * Usa filter_input() y filtros de validación/saneamiento
 * para leer $_POST / $_GET de forma segura.
 */

/** Texto recogido por POST, sin espacios sobrantes. */
function post_texto(string $campo): string
{
    return trim(filter_input(INPUT_POST, $campo) ?? '');
}

/** Entero recogido por GET o POST; null si no es un número válido. */
function leer_entero(int $origen, string $campo): ?int
{
    $valor = filter_input($origen, $campo, FILTER_VALIDATE_INT);
    return ($valor === false || $valor === null) ? null : $valor;
}

/**
 * Valida los datos de un usuario.
 * @param array $d        Datos del formulario
 * @param bool  $con_pass true si la contraseña es obligatoria (alta)
 * @return array          Lista de errores (vacía si todo es correcto)
 */
function validar_usuario(array $d, bool $con_pass): array
{
    $errores = [];

    // Obligatorios: nombre, apellidos, email y contraseña (en el alta)
    if ($d['nombre'] === '' || mb_strlen($d['nombre']) > 50) {
        $errores['nombre'] = 'El nombre es obligatorio (máx. 50 caracteres).';
    }
    if ($d['apellidos'] === '' || mb_strlen($d['apellidos']) > 100) {
        $errores['apellidos'] = 'Los apellidos son obligatorios (máx. 100 caracteres).';
    }
    if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = 'Introduce un email válido.';
    }
    if ($con_pass || $d['password'] !== '') {
        // Mínimo 8 caracteres, con al menos una letra y un número
        if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d).{8,}$/', $d['password'])) {
            $errores['password'] = 'La contraseña debe tener al menos 8 caracteres, con letras y números.';
        } elseif (isset($d['password2']) && $d['password'] !== $d['password2']) {
            $errores['password2'] = 'Las contraseñas no coinciden.';
        }
    }

    // Opcionales: teléfono (9 a 15 dígitos) y dirección
    if ($d['telefono'] !== '' && !preg_match('/^\+?\d{9,15}$/', $d['telefono'])) {
        $errores['telefono'] = 'El teléfono debe tener entre 9 y 15 dígitos.';
    }
    if (mb_strlen($d['direccion']) > 200) {
        $errores['direccion'] = 'La dirección es demasiado larga.';
    }
    return $errores;
}

/** Valida los datos de un artículo. */
function validar_articulo(array $d): array
{
    $errores = [];
    if ($d['nombre'] === '') {
        $errores['nombre'] = 'El nombre es obligatorio.';
    }
    if ($d['marca'] === '') {
        $errores['marca'] = 'La marca es obligatoria.';
    }
    // FILTER_VALIDATE_FLOAT con rango: devuelve false si no es válido
    if (filter_var($d['talla'], FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 15, 'max_range' => 50]]) === false) {
        $errores['talla'] = 'La talla debe ser un número entre 15 y 50.';
    }
    if (filter_var($d['precio'], FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0]]) === false) {
        $errores['precio'] = 'El precio debe ser un número positivo.';
    }
    if (filter_var($d['stock'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) === false) {
        $errores['stock'] = 'El stock debe ser un número entero igual o mayor que 0.';
    }
    return $errores;
}

/**
 * Procesa la imagen subida en el campo "imagen" ($_FILES).
 * @return string|null Nombre del archivo guardado, o null si no se ha subido ninguna
 * @throws RuntimeException si el archivo no es una imagen válida
 */
function guardar_imagen_subida(): ?string
{
    if (empty($_FILES['imagen']) || $_FILES['imagen']['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $archivo = $_FILES['imagen'];
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Error al subir la imagen.');
    }
    if ($archivo['size'] > 2 * 1024 * 1024) {
        throw new RuntimeException('La imagen no puede superar 2 MB.');
    }
    // Se comprueba el tipo real del archivo, no la extensión que envía el navegador
    $tipos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = mime_content_type($archivo['tmp_name']);
    if (!isset($tipos[$mime])) {
        throw new RuntimeException('Formato no permitido. Usa JPG, PNG o WEBP.');
    }
    // Nombre aleatorio para que no se repita ni se pueda adivinar
    $nombre = bin2hex(random_bytes(8)) . '.' . $tipos[$mime];
    if (!move_uploaded_file($archivo['tmp_name'], RUTA_IMAGENES . $nombre)) {
        throw new RuntimeException('No se pudo guardar la imagen.');
    }
    return $nombre;
}
