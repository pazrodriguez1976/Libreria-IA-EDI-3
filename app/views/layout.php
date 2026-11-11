<?php
$seccion = $seccion ?? '';
$menu = [
    'inicio'    => ['/', 'Inicio'],
    'productos' => ['/productos', 'Productos'],
    'ventas'    => ['/ventas', 'Ventas'],
    'precios'   => ['/precios', 'Precios'],
    'asistente' => ['/asistente', 'Asistente IA'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Inicio') ?> | Librería NaSer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700&family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/estilos.css">
</head>
<body>
    <aside class="lateral">
        <a class="marca" href="/">
            <span class="marca-nombre">NaSer</span>
            <span class="marca-rubro">Librería</span>
        </a>
        <nav class="menu">
            <?php foreach ($menu as $clave => [$menuEnlace, $menuTexto]): ?>
                <a href="<?= $menuEnlace ?>" class="<?= $seccion === $clave ? 'activo' : '' ?>"><?= $menuTexto ?></a>
            <?php endforeach; ?>
        </nav>
        <form class="sesion" method="post" action="/logout">
            <span><?= htmlspecialchars($_SESSION['usuario']['nombre'] ?? '') ?></span>
            <button type="submit">Cerrar sesión</button>
        </form>
    </aside>
    <main class="contenido">
        <?php require $contenido; ?>
    </main>
</body>
</html>
