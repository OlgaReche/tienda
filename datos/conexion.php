<?php
/**
 * CAPA DE ACCESO A DATOS - Conexión
 * Abre una única conexión mysqli por petición y la reutiliza.
 * (Las funciones mysql_*  se eliminaron en PHP 7; mysqli es su sustituta.)
 */
require_once __DIR__ . '/../config/config.php';

function conectar(): mysqli
{
    static $conexion = null;              // static: se conserva entre llamadas

    if ($conexion === null) {
        // Hace que los errores de MySQL lancen excepciones en lugar de avisos
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conexion = mysqli_connect(BD_HOST, BD_USUARIO, BD_PASSWORD, BD_NOMBRE);
        mysqli_set_charset($conexion, 'utf8mb4');   // tildes y ñ correctas
    }
    return $conexion;
}

/**
 * Ejecuta una consulta preparada y devuelve el objeto sentencia.
 * Las consultas preparadas evitan la inyección SQL: los datos del usuario
 * nunca se mezclan con el texto de la consulta.
 *
 * @param string $sql    Consulta con ? en lugar de los valores
 * @param string $tipos  Un carácter por parámetro: s=texto, i=entero, d=decimal
 * @param array  $params Valores que sustituyen a los ?
 */
function ejecutar(string $sql, string $tipos = '', array $params = []): mysqli_stmt
{
    // Consulta preparada: los datos van aparte para evitar la inyección SQL (entrar como admin, ver o borrar datos)
    $sentencia = mysqli_prepare(conectar(), $sql);
    if ($tipos !== '') {
        mysqli_stmt_bind_param($sentencia, $tipos, ...$params);
    }
    mysqli_stmt_execute($sentencia);
    return $sentencia;
}

/** Devuelve todas las filas de un SELECT como array asociativo. */
function obtener_filas(string $sql, string $tipos = '', array $params = []): array
{
    $resultado = mysqli_stmt_get_result(ejecutar($sql, $tipos, $params));
    return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
}

/** Devuelve una sola fila (o null si no hay ninguna). */
function obtener_fila(string $sql, string $tipos = '', array $params = []): ?array
{
    $resultado = mysqli_stmt_get_result(ejecutar($sql, $tipos, $params));
    return mysqli_fetch_assoc($resultado) ?: null;
}
