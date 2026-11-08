<?php
/**
 * CAPA DE ACCESO A DATOS - Pedidos
 * Guarda una compra completa usando una TRANSACCIÓN:
 * o se guardan el pedido, todas sus líneas y el descuento de stock,
 * o no se guarda nada (ROLLBACK) si algo falla a mitad.
 */
require_once __DIR__ . '/conexion.php';

/**
 * @param int    $usuario_id
 * @param string $direccion
 * @param array  $lineas  [['id'=>..., 'cantidad'=>..., 'precio'=>...], ...]
 * @return int   id del pedido creado
 * @throws RuntimeException si no hay stock suficiente de algún artículo
 */
function pedido_crear(int $usuario_id, string $direccion, array $lineas): int
{
    $bd = conectar();
    mysqli_begin_transaction($bd);              // BEGIN

    try {
        $total = 0;
        foreach ($lineas as $l) {
            $total += $l['precio'] * $l['cantidad'];
        }

        ejecutar('INSERT INTO pedidos (usuario_id, direccion_envio, total) VALUES (?, ?, ?)',
                 'isd', [$usuario_id, $direccion, $total]);
        $pedido_id = mysqli_insert_id($bd);

        foreach ($lineas as $l) {
            // Descuenta stock solo si queda suficiente (evita stock negativo)
            $sentencia = ejecutar('UPDATE articulos SET stock = stock - ?
                                   WHERE id = ? AND stock >= ? AND activo = 1',
                                  'iii', [$l['cantidad'], $l['id'], $l['cantidad']]);
            if (mysqli_stmt_affected_rows($sentencia) === 0) {
                throw new RuntimeException('No hay stock suficiente de "' . $l['nombre'] . '".');
            }

            ejecutar('INSERT INTO lineas_pedido (pedido_id, articulo_id, cantidad, precio_unitario)
                      VALUES (?, ?, ?, ?)',
                     'iiid', [$pedido_id, $l['id'], $l['cantidad'], $l['precio']]);
        }

        mysqli_commit($bd);                     // COMMIT: todo correcto
        return $pedido_id;

    } catch (Throwable $e) {
        mysqli_rollback($bd);                   // ROLLBACK: se deshace todo
        throw $e;
    }
}

/** Pedidos de un cliente (para "Mis pedidos"). */
function pedido_listar_por_usuario(int $usuario_id): array
{
    return obtener_filas('SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY fecha DESC',
                         'i', [$usuario_id]);
}

/** Todos los pedidos con el nombre del cliente (para el administrador). */
function pedido_listar_todos(): array
{
    return obtener_filas('SELECT p.*, CONCAT(u.nombre, " ", u.apellidos) AS cliente, u.email
                          FROM pedidos p JOIN usuarios u ON u.id = p.usuario_id
                          ORDER BY p.fecha DESC');
}

/** Líneas de un pedido con el nombre de cada artículo. */
function pedido_lineas(int $pedido_id): array
{
    return obtener_filas('SELECT l.*, a.nombre, a.marca, a.talla
                          FROM lineas_pedido l JOIN articulos a ON a.id = l.articulo_id
                          WHERE l.pedido_id = ?', 'i', [$pedido_id]);
}

function pedido_cambiar_estado(int $id, string $estado): void
{
    ejecutar('UPDATE pedidos SET estado = ? WHERE id = ?', 'si', [$estado, $id]);
}
