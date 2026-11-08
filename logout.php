<?php
/**
 * CERRAR SESIÓN: destruye la sesión (y con ella el carrito) y vuelve al inicio.
 */
require_once __DIR__ . '/negocio/sesion.php';

cerrar_sesion();
poner_mensaje('Has cerrado la sesión.');
redirigir('/index.php');
