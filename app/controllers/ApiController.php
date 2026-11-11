<?php
/*
 * API de consulta del sistema. La usa el asistente de IA para leer datos.
 * Solo tiene métodos GET: la IA puede consultar, pero no modificar nada.
 * Cada pedido tiene que traer el token secreto en la cabecera X-Api-Token.
 */
class ApiController extends Controller
{
    public function __construct()
    {
        $config = require __DIR__ . '/../../config/config.php';
        $recibido = $_SERVER['HTTP_X_API_TOKEN'] ?? '';
        if (!hash_equals($config['api_token'], $recibido)) {
            $this->json(['error' => 'Token inválido o ausente'], 401);
        }
    }

    // GET /api/productos?q=texto&rubro=id
    public function productos(): void
    {
        $texto = trim($_GET['q'] ?? '');
        $rubroId = (int) ($_GET['rubro'] ?? 0);
        $this->json(array_map([$this, 'formato'], Producto::buscar($texto, $rubroId)));
    }

    // GET /api/productos/ver?id=12
    public function producto(): void
    {
        $producto = Producto::buscarPorId((int) ($_GET['id'] ?? 0));
        if (!$producto) {
            $this->json(['error' => 'Producto no encontrado'], 404);
        }
        $this->json($this->formato($producto));
    }

    // GET /api/productos/reponer
    public function reponer(): void
    {
        $this->json(array_map([$this, 'formato'], Producto::paraReponer()));
    }

    // GET /api/ventas/resumen?desde=2026-11-01&hasta=2026-11-10
    public function ventasResumen(): void
    {
        [$desde, $hasta] = $this->periodo();
        $resumen = Venta::resumenEntre($desde, $hasta);
        $this->json([
            'desde'           => $desde,
            'hasta'           => $hasta,
            'cantidad_ventas' => (int) $resumen['cantidad'],
            'monto_total'     => round((float) $resumen['monto'], 2),
        ]);
    }

    // GET /api/ventas/mas-vendidos?desde=...&hasta=...&limite=5
    public function masVendidos(): void
    {
        [$desde, $hasta] = $this->periodo();
        $limite = max(1, min(20, (int) ($_GET['limite'] ?? 5)));
        $filas = Venta::masVendidos($desde, $hasta, $limite);
        $this->json([
            'desde'     => $desde,
            'hasta'     => $hasta,
            'productos' => array_map(fn($f) => [
                'id'        => (int) $f['id'],
                'nombre'    => $f['nombre'],
                'unidades'  => (int) $f['unidades'],
                'facturado' => round((float) $f['facturado'], 2),
            ], $filas),
        ]);
    }

    // GET /api/precios/cambios?limite=10
    public function cambiosDePrecios(): void
    {
        $limite = max(1, min(60, (int) ($_GET['limite'] ?? 10)));
        $ultima = Precio::ultimosCambios($limite);
        $this->json([
            'lista'   => $ultima['lista'],
            'fecha'   => $ultima['fecha'],
            'cambios' => array_map(fn($c) => [
                'nombre'               => $c['nombre'],
                'precio_anterior'      => (float) $c['precio_con_iva_anterior'],
                'precio_nuevo'         => (float) $c['precio_con_iva_nuevo'],
                'variacion_porcentual' => (float) $c['variacion'],
            ], $ultima['cambios']),
        ]);
    }

    // Lee desde/hasta de la URL. Por defecto: del 1° del mes hasta hoy.
    private function periodo(): array
    {
        $desde = $_GET['desde'] ?? date('Y-m-01');
        $hasta = $_GET['hasta'] ?? date('Y-m-d');
        $formato = '/^\d{4}-\d{2}-\d{2}$/';
        if (!preg_match($formato, $desde) || !preg_match($formato, $hasta)) {
            $this->json(['error' => 'Las fechas van en formato AAAA-MM-DD'], 400);
        }
        return [$desde, $hasta];
    }

    // Deja cada producto con los mismos campos y tipos
    private function formato(array $p): array
    {
        return [
            'id'             => (int) $p['id'],
            'codigo'         => $p['codigo'],
            'nombre'         => $p['nombre'],
            'rubro'          => $p['rubro'],
            'precio_sin_iva' => (float) $p['precio_sin_iva'],
            'precio_con_iva' => (float) $p['precio_con_iva'],
            'stock'          => (int) $p['stock'],
            'stock_minimo'   => (int) $p['stock_minimo'],
        ];
    }

    private function json($datos, int $codigo = 200): void
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
