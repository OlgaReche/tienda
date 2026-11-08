<?php
/**
 * CAPA DE ACCESO A DATOS - Usuarios
 * Todas las consultas SQL sobre la tabla "usuarios" están aquí.
 * (DAO = Data Access Object: objeto/archivo de acceso a datos)
 */
require_once __DIR__ . '/conexion.php';

function usuario_buscar_por_email(string $email): ?array
{
    return obtener_fila('SELECT * FROM usuarios WHERE email = ?', 's', [$email]);
}

function usuario_buscar_por_id(int $id): ?array
{
    return obtener_fila('SELECT * FROM usuarios WHERE id = ?', 'i', [$id]);
}

function usuario_listar(): array
{
    return obtener_filas('SELECT id, nombre, apellidos, email, telefono, rol, fecha_alta
                          FROM usuarios ORDER BY rol, apellidos');
}

/** Inserta un usuario. La contraseña llega ya cifrada desde la capa de negocio. */
function usuario_insertar(array $u): int
{
    ejecutar('INSERT INTO usuarios (nombre, apellidos, email, password, telefono, direccion, rol)
              VALUES (?, ?, ?, ?, ?, ?, ?)',
             'sssssss',
             [$u['nombre'], $u['apellidos'], $u['email'], $u['password'],
              $u['telefono'], $u['direccion'], $u['rol']]);
    return mysqli_insert_id(conectar());
}

/** Actualiza los datos. Si $u['password'] viene vacío, se mantiene la anterior. */
function usuario_actualizar(int $id, array $u): void
{
    ejecutar('UPDATE usuarios SET nombre = ?, apellidos = ?, email = ?, telefono = ?,
              direccion = ?, rol = ? WHERE id = ?',
             'ssssssi',
             [$u['nombre'], $u['apellidos'], $u['email'], $u['telefono'],
              $u['direccion'], $u['rol'], $id]);

    if (!empty($u['password'])) {
        ejecutar('UPDATE usuarios SET password = ? WHERE id = ?', 'si', [$u['password'], $id]);
    }
}

function usuario_eliminar(int $id): void
{
    ejecutar('DELETE FROM usuarios WHERE id = ?', 'i', [$id]);
}

/** Cuenta los pedidos de un usuario (no se puede borrar si tiene alguno). */
function usuario_contar_pedidos(int $id): int
{
    $fila = obtener_fila('SELECT COUNT(*) AS total FROM pedidos WHERE usuario_id = ?', 'i', [$id]);
    return (int) $fila['total'];
}

function usuario_contar_admins(): int
{
    $fila = obtener_fila("SELECT COUNT(*) AS total FROM usuarios WHERE rol = 'admin'");
    return (int) $fila['total'];
}
