<header class="encabezado">
    <h1>Ventas</h1>
    <p>Últimas <?= count($ventas) ?></p>
    <a class="boton" href="/ventas/nueva">Registrar venta</a>
</header>

<?php if (count($ventas) === 0): ?>
    <p class="vacio">Todavía no hay ventas. Registrá la primera.</p>
<?php else: ?>
<div class="tabla-marco">
<table class="tabla">
    <thead>
        <tr>
            <th>N°</th>
            <th>Fecha</th>
            <th class="num">Unidades</th>
            <th class="num">Total</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($ventas as $v): ?>
            <tr>
                <td><?= $v['id'] ?></td>
                <td><?= date('d/m/Y H:i', strtotime($v['fecha'])) ?></td>
                <td class="num"><?= $v['unidades'] ?></td>
                <td class="num">$<?= number_format($v['total'], 2, ',', '.') ?></td>
                <td class="num"><a href="/ventas/ver?id=<?= $v['id'] ?>">Ver detalle</a></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>
