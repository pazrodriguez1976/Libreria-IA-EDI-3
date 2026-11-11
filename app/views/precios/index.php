<header class="encabezado">
    <h1>Precios</h1>
    <p>Actualizá los precios con la lista del proveedor</p>
</header>

<?php if ($aplicados !== null): ?>
    <p class="exito" role="status">Se actualizaron <?= $aplicados ?> precios. Los cambios quedaron guardados en el historial.</p>
<?php endif; ?>
<?php if ($error !== ''): ?>
    <p class="login-error" role="alert"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form class="subir" method="post" action="/precios/revisar" enctype="multipart/form-data">
    <label for="lista">Lista de precios del proveedor (Excel o CSV)</label>
    <input type="file" id="lista" name="lista" accept=".xlsx,.csv" required>
    <p class="ayuda">Primero vas a ver qué precios cambian. No se modifica nada hasta que lo confirmes.</p>
    <button type="submit" class="boton">Revisar cambios</button>
</form>

<?php if ($ultima['lista'] !== null): ?>
    <h2>Última actualización</h2>
    <p class="resumen"><?= htmlspecialchars($ultima['lista']) ?>, aplicada el <?= date('d/m/Y H:i', strtotime($ultima['fecha'])) ?>. Cambiaron <?= count($ultima['cambios']) ?> precios.</p>
    <div class="tabla-marco angosta">
    <table class="tabla">
        <thead>
            <tr><th>Artículo</th><th class="num">Antes</th><th class="num">Ahora</th><th class="num">Variación</th></tr>
        </thead>
        <tbody>
            <?php foreach ($ultima['cambios'] as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['nombre']) ?></td>
                    <td class="num">$<?= number_format($c['precio_con_iva_anterior'], 2, ',', '.') ?></td>
                    <td class="num">$<?= number_format($c['precio_con_iva_nuevo'], 2, ',', '.') ?></td>
                    <td class="num <?= $c['variacion'] > 0 ? 'sube' : 'baja' ?>"><?= $c['variacion'] > 0 ? '+' : '' ?><?= number_format($c['variacion'], 1, ',', '.') ?>%</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php endif; ?>
