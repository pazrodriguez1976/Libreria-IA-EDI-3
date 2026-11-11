<?php
class ProductoController extends Controller
{
    public function index(): void
    {
        $this->requerirLogin();

        $texto = trim($_GET['q'] ?? '');
        $rubroId = (int) ($_GET['rubro'] ?? 0);

        $this->vista('productos/index', [
            'titulo'    => 'Productos',
            'seccion'   => 'productos',
            'productos' => Producto::buscar($texto, $rubroId),
            'rubros'    => Rubro::todos(),
            'texto'     => $texto,
            'rubroId'   => $rubroId,
        ]);
    }
}
