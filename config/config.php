<?php
/**
 * Configuración general de la aplicación.
 * Es el único archivo que hay que cambiar al pasar de XAMPP al hosting.
 */

// Nombre que aparece en la cabecera y en el título de las páginas
define('NOMBRE_TIENDA', 'Zolgada');

// Datos de conexión a MySQL. Al subir la web a InfinityFree se modifican estos 4 valores.
define('BD_HOST', 'localhost');
define('BD_USUARIO', 'root');
define('BD_PASSWORD', '');
define('BD_NOMBRE', 'tienda_zapatillas');

// Ruta base de la web ("si va en la raiz del hosting)
define('URL_BASE', '/tienda');

// Carpeta con las imágenes
define('RUTA_IMAGENES', __DIR__ . '/../img/articulos/');
define('URL_IMAGENES', URL_BASE . '/img/articulos/');

// Minutos de inactividad antes de cerrar la sesión automáticamente
define('MINUTOS_SESION', 20);
date_default_timezone_set('Europe/Madrid');
