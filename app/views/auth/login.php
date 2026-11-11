<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingresar | Librería NaSer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700&family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/estilos.css">
</head>
<body class="pagina-login">
    <section class="login-marca">
        <p class="marca-nombre">NaSer</p>
        <p class="marca-rubro">Librería</p>
        <p class="login-lema">Stock, precios y ventas del local, en un solo lugar.</p>
    </section>
    <main class="login-caja">
        <h1>Ingresar al sistema</h1>
        <p class="login-nota">Acceso solo para el propietario.</p>

        <?php if ($error !== ''): ?>
            <p class="login-error" role="alert"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post" action="/login">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required autofocus>

            <label for="clave">Contraseña</label>
            <input type="password" id="clave" name="clave" required>

            <button type="submit">Ingresar</button>
        </form>
    </main>
</body>
</html>
