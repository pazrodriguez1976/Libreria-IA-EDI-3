<?php
class PrecioController extends Controller
{
    public function index(string $error = ''): void
    {
        $this->requerirLogin();
        $aplicados = $_SESSION['precios_aplicados'] ?? null;
        unset($_SESSION['precios_aplicados']);

        $this->vista('precios/index', [
            'titulo'    => 'Precios',
            'seccion'   => 'precios',
            'error'     => $error,
            'aplicados' => $aplicados,
            'ultima'    => Precio::ultimosCambios(),
        ]);
    }

    // Paso 1: lee el archivo y muestra la vista previa. Todavía no cambia nada.
    public function revisar(): void
    {
        $this->requerirLogin();
        $archivo = $_FILES['lista'] ?? null;
        if (!$archivo || $archivo['error'] !== UPLOAD_ERR_OK) {
            $this->index('Elegí el archivo de la lista de precios para subir.');
            return;
        }

        try {
            $lista = LectorLista::leer($archivo['tmp_name'], $archivo['name']);
        } catch (Exception $e) {
            $this->index($e->getMessage());
            return;
        }

        $comparacion = Precio::comparar($lista);
        // Se guarda en la sesión para aplicarlo en el paso 2
        $_SESSION['precios_pendientes'] = ['lista' => $archivo['name'], 'cambios' => $comparacion['cambian']];

        $this->vista('precios/revisar', [
            'titulo'      => 'Revisar precios',
            'seccion'     => 'precios',
            'nombreLista' => $archivo['name'],
            'comparacion' => $comparacion,
        ]);
    }

    // Paso 2: aplica los cambios que se revisaron
    public function aplicar(): void
    {
        $this->requerirLogin();
        $pendientes = $_SESSION['precios_pendientes'] ?? null;
        unset($_SESSION['precios_pendientes']);

        if (!$pendientes || count($pendientes['cambios']) === 0) {
            $this->redirigir('/precios');
        }

        $cantidad = Precio::aplicar($pendientes['cambios'], $pendientes['lista']);
        $_SESSION['precios_aplicados'] = $cantidad;
        $this->redirigir('/precios');
    }
}
