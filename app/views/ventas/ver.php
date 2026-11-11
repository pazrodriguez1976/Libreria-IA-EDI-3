<?php if ($recien): ?>
    <p class="exito" role="status">Venta registrada. El stock ya se descontó.</p>
<?php endif; ?>

<header class="encabezado">
    <h1>Venta N° <?= $venta['id'] ?></h1>
    <p><?= date('d/m/Y H:i', strtotime($venta['fecha'])) ?></p>
</header>

<div class="tabla-marco angosta">
<table class="tabla">
    <thead>
        <tr>
            <th>Artículo</th>
            <th class="num">Cantidad</th>
            <th class="num">Precio</th>
            <th class="num">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($detalle as $d): ?>
            <tr>
                <td>
                    <strong><?= htmlspecialchars($d['nombre']) ?></strong>
                    <span class="codigo"><?= htmlspecialchars($d['codigo']) ?></span>
                </td>
                <td class="num"><?= $d['cantidad'] ?></td>
                <td class="num">$<?= number_format($d['precio_unitario'], 2, ',', '.') ?></td>
                <td class="num">$<?= number_format($d['cantidad'] * $d['precio_unitario'], 2, ',', '.') ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3">Total</td>
            <td class="num">$<?= number_format($venta['total'], 2, ',', '.') ?></td>
        </tr>
    </tfoot>
</table>
</div>

<p class="acciones"><a href="/ventas">Volver a ventas</a> <a class="boton" href="/ventas/nueva">Registrar otra venta</a></p>
