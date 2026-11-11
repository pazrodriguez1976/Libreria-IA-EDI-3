<?php $cambian = $comparacion['cambian']; ?>
<header class="encabezado">
    <h1>Revisar precios</h1>
    <p><?= htmlspecialchars($nombreLista) ?></p>
</header>

<ul class="conteo">
    <li><b><?= count($cambian) ?></b> cambian de precio</li>
    <li><b><?= $comparacion['iguales'] ?></b> quedan igual</li>
    <li><b><?= count($comparacion['faltantes']) ?></b> no vinieron en la lista</li>
    <li><b><?= number_format($comparacion['ignorados'], 0, ',', '.') ?></b> artículos de la lista que no trabajás (se ignoran)</li>
</ul>

<?php if (count($comparacion['faltantes']) > 0): ?>
    <p class="login-error">No vinieron en la lista y conservan su precio: <?= htmlspecialchars(implode(', ', $comparacion['faltantes'])) ?>.</p>
<?php endif; ?>

<?php if (count($cambian) === 0): ?>
    <p class="vacio">Esta lista no cambia ningún precio de tus productos.</p>
    <p class="acciones"><a href="/precios">Volver</a></p>
<?php else: ?>
    <div class="tabla-marco angosta">
    <table class="tabla">
        <thead>
            <tr><th>Artículo</th><th class="num">Precio actual</th><th class="num">Precio nuevo</th><th class="num">Variación</th></tr>
        </thead>
        <tbody>
            <?php foreach ($cambian as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['nombre']) ?></td>
                    <td class="num">$<?= number_format($c['con_iva_anterior'], 2, ',', '.') ?></td>
                    <td class="num">$<?= number_format($c['con_iva_nuevo'], 2, ',', '.') ?></td>
                    <td class="num <?= $c['variacion'] > 0 ? 'sube' : 'baja' ?>"><?= $c['variacion'] > 0 ? '+' : '' ?><?= number_format($c['variacion'], 1, ',', '.') ?>%</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>

    <form class="acciones" method="post" action="/precios/aplicar">
        <a href="/precios">Cancelar</a>
        <button type="submit" class="boton">Aplicar <?= count($cambian) ?> cambios</button>
    </form>
<?php endif; ?>
