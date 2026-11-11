<?php
// Puerta de entrada: todos los pedidos pasan por acá
date_default_timezone_set('America/Argentina/Buenos_Aires');
session_start();

// Carga automática de clases (core, modelos y controladores)
spl_autoload_register(function ($clase) {
    foreach (['core', 'app/models', 'app/controllers'] as $carpeta) {
        $archivo = __DIR__ . "/../$carpeta/$clase.php";
        if (file_exists($archivo)) {
            require $archivo;
            return;
        }
    }
});

// Tabla de rutas: "MÉTODO /ruta" => [Controlador, acción]
$rutas = [
    'GET /login'        => ['AuthController', 'formulario'],
    'POST /login'       => ['AuthController', 'ingresar'],
    'POST /logout'      => ['AuthController', 'salir'],
    'GET /'             => ['InicioController', 'index'],
    'GET /productos'    => ['ProductoController', 'index'],
    'GET /ventas'       => ['VentaController', 'index'],
    'GET /ventas/nueva' => ['VentaController', 'nueva'],
    'POST /ventas'      => ['VentaController', 'guardar'],
    'GET /ventas/ver'   => ['VentaController', 'ver'],
    'GET /precios'              => ['PrecioController', 'index'],
    'POST /precios/revisar'     => ['PrecioController', 'revisar'],
    'POST /precios/aplicar'     => ['PrecioController', 'aplicar'],
    'GET /api/precios/cambios'  => ['ApiController', 'cambiosDePrecios'],
    'GET /asistente'            => ['AsistenteController', 'index'],
    'POST /asistente/preguntar' => ['AsistenteController', 'preguntar'],
    // API de consulta para el asistente de IA (solo lectura)
    'GET /api/productos'           => ['ApiController', 'productos'],
    'GET /api/productos/ver'       => ['ApiController', 'producto'],
    'GET /api/productos/reponer'   => ['ApiController', 'reponer'],
    'GET /api/ventas/resumen'      => ['ApiController', 'ventasResumen'],
    'GET /api/ventas/mas-vendidos' => ['ApiController', 'masVendidos'],
];

$metodo = $_SERVER['REQUEST_METHOD'];
$ruta = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';
$clave = "$metodo $ruta";

if (!isset($rutas[$clave])) {
    http_response_code(404);
    echo 'Página no encontrada';
    exit;
}

[$controlador, $accion] = $rutas[$clave];
(new $controlador())->$accion();
