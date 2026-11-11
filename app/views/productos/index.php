<header class="encabezado">
    <h1>Productos</h1>
    <p><?= count($productos) ?> <?= count($productos) === 1 ? 'artículo' : 'artículos' ?></p>
</header>

<form class="filtros" method="get" action="/productos">
    <label>
        <span class="oculto">Buscar</span>
        <input type="search" name="q" value="<?= htmlspecialchars($texto) ?>" placeholder="Buscar por nombre o código">
    </label>
    <label>
        <span class="oculto">Rubro</span>
        <select name="rubro">
            <option value="0">Todos los rubros</option>
            <?php foreach ($rubros as $r): ?>
                <option value="<?= $r['id'] ?>" <?= $rubroId == $r['id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button type="submit">Buscar</button>
    <?php if ($texto !== '' || $rubroId > 0): ?>
        <a href="/productos">Quitar filtros</a>
    <?php endif; ?>
</form>

<?php if (count($productos) === 0): ?>
    <p class="vacio">No hay productos que coincidan. Probá con otra palabra o quitá los filtros.</p>
<?php else: ?>
<div class="tabla-marco">
<table class="tabla">
    <thead>
        <tr>
            <th>Artículo</th>
            <th>Rubro</th>
            <th class="num">Precio con IVA</th>
            <th>Stock</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($productos as $p): ?>
            <?php
            // La regla muestra el stock; la marca indica dónde está el mínimo
            $tope = max($p['stock_minimo'] * 3, 1);
            $ancho = min(100, round($p['stock'] / $tope * 100));
            if ($p['stock'] == 0) {
                $nivel = 'agotado';
                $nota = 'Sin stock';
            } elseif ($p['stock'] < $p['stock_minimo']) {
                $nivel = 'bajo';
                $nota = 'Reponer';
            } else {
                $nivel = 'bien';
                $nota = '';
            }
            ?>
            <tr>
                <td>
                    <strong><?= htmlspecialchars($p['nombre']) ?></strong>
                    <span class="codigo"><?= htmlspecialchars($p['codigo']) ?></span>
                </td>
                <td><?= htmlspecialchars($p['rubro']) ?></td>
                <td class="num">$<?= number_format($p['precio_con_iva'], 2, ',', '.') ?></td>
                <td>
                    <div class="stock <?= $nivel ?>">
                        <b><?= $p['stock'] ?></b>
                        <span class="regla" title="Mínimo: <?= $p['stock_minimo'] ?>"><i style="width: <?= $ancho ?>%"></i></span>
                        <?php if ($nota !== ''): ?><em><?= $nota ?></em><?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>
