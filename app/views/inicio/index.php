<?php
$cantidad = count($paraReponer);
$pesos = fn($n) => '$' . number_format((float) $n, 0, ',', '.');
?>
<section class="aviso">
    <?php if ($cantidad === 0): ?>
        <h1>Todo el stock está en orden</h1>
        <p>Ningún producto está por debajo de su mínimo.</p>
    <?php else: ?>
        <h1>Hay <?= $cantidad ?> <?= $cantidad === 1 ? 'producto' : 'productos' ?> para reponer</h1>
        <p>Están por debajo del stock mínimo que definiste.</p>
    <?php endif; ?>
</section>

<div class="dos-columnas">
    <?php if ($cantidad > 0): ?>
    <section>
        <ul class="reponer">
            <?php foreach ($paraReponer as $p): ?>
                <li class="<?= $p['stock'] == 0 ? 'agotado' : '' ?>">
                    <div>
                        <strong><?= htmlspecialchars($p['nombre']) ?></strong>
                        <span><?= htmlspecialchars($p['rubro']) ?></span>
                    </div>
                    <div class="faltante">
                        <b><?= $p['stock'] == 0 ? 'Sin stock' : 'Quedan ' . $p['stock'] ?></b>
                        <span>mínimo <?= $p['stock_minimo'] ?></span>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
    <?php endif; ?>

    <section class="cifras">
        <dl>
            <div>
                <dt>Vendido hoy</dt>
                <dd><?= $pesos($ventasHoy['monto']) ?></dd>
                <small><?= $ventasHoy['cantidad'] ?> <?= $ventasHoy['cantidad'] == 1 ? 'venta' : 'ventas' ?></small>
            </div>
            <div>
                <dt>Vendido este mes</dt>
                <dd><?= $pesos($ventasMes['monto']) ?></dd>
                <small><?= $ventasMes['cantidad'] ?> ventas</small>
            </div>
            <div>
                <dt>Artículos cargados</dt>
                <dd><?= $totalProductos ?></dd>
                <small><a href="/productos">Ver todos</a></small>
            </div>
        </dl>
    </section>
</div>
