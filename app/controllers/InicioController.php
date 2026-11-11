<?php
class InicioController extends Controller
{
    public function index(): void
    {
        $this->requerirLogin();

        $this->vista('inicio/index', [
            'titulo'      => 'Inicio',
            'seccion'     => 'inicio',
            'paraReponer' => Producto::paraReponer(),
            'totalProductos' => Producto::contar(),
            'ventasMes'   => Venta::resumenDesde(date('Y-m-01')),
            'ventasHoy'   => Venta::resumenDesde(date('Y-m-d')),
        ]);
    }
}
