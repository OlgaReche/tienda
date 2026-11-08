<?php
/**
 * CAPA DE NEGOCIO - Carrito de la compra
 * El carrito se guarda en la SESIÓN, no en la base de datos:
 *   $_SESSION['carrito'] = [id_articulo => cantidad, ...]
 * Así se mantiene mientras el cliente navega entre páginas.
 * Solo al confirmar la compra se guarda en la BD (tabla pedidos).
 */
require_once __DIR__ . '/../datos/articulos_dao.php';

function carrito_obtener(): array
{
    return $_SESSION['carrito'] ?? [];
}

/** Suma unidades de un artículo, sin pasar del stock disponible. */
function carrito_agregar(int $id, int $cantidad = 1): string
{
    $articulo = articulo_buscar_por_id($id);
    if (!$articulo || !$articulo['activo']) {
        return 'El artículo no existe.';
    }
    $actual = $_SESSION['carrito'][$id] ?? 0;
    $nueva  = $actual + max(1, $cantidad);

    if ($nueva > $articulo['stock']) {
        return 'Solo quedan ' . $articulo['stock'] . ' unidades de "' . $articulo['nombre'] . '".';
    }
    $_SESSION['carrito'][$id] = $nueva;
    return '';
}

/** Cambia la cantidad (0 = quitar del carrito). */
function carrito_actualizar(int $id, int $cantidad): void
{
    if ($cantidad <= 0) {
        unset($_SESSION['carrito'][$id]);
        return;
    }
    $articulo = articulo_buscar_por_id($id);
    if ($articulo) {
        $_SESSION['carrito'][$id] = min($cantidad, (int) $articulo['stock']);
    }
}

function carrito_vaciar(): void
{
    unset($_SESSION['carrito']);
}

function carrito_num_unidades(): int
{
    return array_sum(carrito_obtener());
}

/**
 * Devuelve el detalle del carrito con datos actuales de la BD.
 * @return array ['lineas' => [...], 'total' => float]
 */
function carrito_detalle(): array
{
    $lineas = [];
    $total  = 0;
    foreach (carrito_obtener() as $id => $cantidad) {
        $a = articulo_buscar_por_id($id);
        if (!$a || !$a['activo']) {
            unset($_SESSION['carrito'][$id]);   // el artículo ya no está a la venta
            continue;
        }
        $subtotal = $a['precio'] * $cantidad;
        $total   += $subtotal;
        $lineas[] = [
            'id'       => (int) $a['id'],
            'nombre'   => $a['nombre'],
            'marca'    => $a['marca'],
            'talla'    => $a['talla'],
            'imagen'   => $a['imagen'],
            'precio'   => (float) $a['precio'],
            'stock'    => (int) $a['stock'],
            'cantidad' => $cantidad,
            'subtotal' => $subtotal,
        ];
    }
    return ['lineas' => $lineas, 'total' => $total];
}
