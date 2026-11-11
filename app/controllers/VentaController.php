<?php
class VentaController extends Controller
{
    public function index(): void
    {
        $this->requerirLogin();
        $this->vista('ventas/index', [
            'titulo'  => 'Ventas',
            'seccion' => 'ventas',
            'ventas'  => Venta::ultimas(),
        ]);
    }

    public function nueva(string $error = '', array $elegidos = []): void
    {
        $this->requerirLogin();
        $this->vista('ventas/nueva', [
            'titulo'    => 'Nueva venta',
            'seccion'   => 'ventas',
            'productos' => Producto::buscar(),
            'error'     => $error,
            'elegidos'  => $elegidos,
        ]);
    }

    public function guardar(): void
    {
        $this->requerirLogin();

        // Arma [producto_id => cantidad] sumando los renglones repetidos
        $ids = $_POST['producto_id'] ?? [];
        $cantidades = $_POST['cantidad'] ?? [];
        $items = [];
        foreach ($ids as $i => $id) {
            $id = (int) $id;
            $cantidad = (int) ($cantidades[$i] ?? 0);
            if ($id > 0 && $cantidad > 0) {
                $items[$id] = ($items[$id] ?? 0) + $cantidad;
            }
        }

        if (count($items) === 0) {
            $this->nueva('Elegí al menos un producto y una cantidad mayor a cero.');
            return;
        }

        try {
            $ventaId = Venta::crear((int) $_SESSION['usuario']['id'], $items);
        } catch (Exception $e) {
            $this->nueva($e->getMessage() . ' La venta no se registró.', $items);
            return;
        }

        $this->redirigir("/ventas/ver?id=$ventaId&nueva=1");
    }

    public function ver(): void
    {
        $this->requerirLogin();
        $venta = Venta::buscar((int) ($_GET['id'] ?? 0));
        if (!$venta) {
            $this->redirigir('/ventas');
        }
        $this->vista('ventas/ver', [
            'titulo'  => 'Venta N° ' . $venta['id'],
            'seccion' => 'ventas',
            'venta'   => $venta,
            'detalle' => Venta::detalle((int) $venta['id']),
            'recien'  => isset($_GET['nueva']),
        ]);
    }
}
