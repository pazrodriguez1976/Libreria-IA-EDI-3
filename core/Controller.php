<?php
// Clase base: todos los controladores heredan de esta
class Controller
{
    // Muestra una vista dentro de la plantilla general 
    protected function vista(string $nombre, array $datos = []): void
    {
        extract($datos);
        $contenido = __DIR__ . "/../app/views/$nombre.php";
        require __DIR__ . '/../app/views/layout.php';
    }

    // Muestra una vista sola, sin menu
    protected function vistaSola(string $nombre, array $datos = []): void
    {
        extract($datos);
        require __DIR__ . "/../app/views/$nombre.php";
    }

    protected function redirigir(string $ruta): void
    {
        header("Location: $ruta");
        exit;
    }

    // Si no hay sesión iniciada, manda al login
    protected function requerirLogin(): void
    {
        if (empty($_SESSION['usuario'])) {
            $this->redirigir('/login');
        }
    }
}
