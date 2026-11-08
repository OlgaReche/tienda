<?php
/**
 * CAPA DE ACCESO A DATOS - Artículos
 * Consultas SQL sobre la tabla "articulos".
 */
require_once __DIR__ . '/conexion.php';

/** Catálogo público: solo artículos activos, con filtro opcional por texto. */
function articulo_listar_activos(string $busqueda = ''): array
{
    if ($busqueda === '') {
        return obtener_filas('SELECT * FROM articulos WHERE activo = 1 ORDER BY marca, nombre');
    }
    $patron = '%' . $busqueda . '%';
    return obtener_filas('SELECT * FROM articulos
                          WHERE activo = 1 AND (nombre LIKE ? OR marca LIKE ?)
                          ORDER BY marca, nombre', 'ss', [$patron, $patron]);
}

/** Listado completo para el administrador (incluye los inactivos). */
function articulo_listar_todos(): array
{
    return obtener_filas('SELECT * FROM articulos ORDER BY id DESC');
}

function articulo_buscar_por_id(int $id): ?array
{
    return obtener_fila('SELECT * FROM articulos WHERE id = ?', 'i', [$id]);
}

function articulo_insertar(array $a): int
{
    ejecutar('INSERT INTO articulos (nombre, marca, descripcion, talla, precio, stock, imagen, activo)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
             'sssddisi',
             [$a['nombre'], $a['marca'], $a['descripcion'], $a['talla'],
              $a['precio'], $a['stock'], $a['imagen'], $a['activo']]);
    return mysqli_insert_id(conectar());
}

function articulo_actualizar(int $id, array $a): void
{
    ejecutar('UPDATE articulos SET nombre = ?, marca = ?, descripcion = ?, talla = ?,
              precio = ?, stock = ?, imagen = ?, activo = ? WHERE id = ?',
             'sssddisii',
             [$a['nombre'], $a['marca'], $a['descripcion'], $a['talla'],
              $a['precio'], $a['stock'], $a['imagen'], $a['activo'], $id]);
}

function articulo_eliminar(int $id): void
{
    ejecutar('DELETE FROM articulos WHERE id = ?', 'i', [$id]);
}

/** Si el artículo aparece en algún pedido no se puede borrar (FK), solo desactivar. */
function articulo_tiene_pedidos(int $id): bool
{
    $fila = obtener_fila('SELECT COUNT(*) AS total FROM lineas_pedido WHERE articulo_id = ?', 'i', [$id]);
    return $fila['total'] > 0;
}
