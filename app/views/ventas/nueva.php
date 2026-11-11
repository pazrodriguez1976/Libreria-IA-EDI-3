<?php
// Si la venta falló, se vuelven a mostrar los renglones que se habían elegido
$renglones = count($elegidos) > 0 ? $elegidos : [0 => 1];
?>
<header class="encabezado">
    <h1>Nueva venta</h1>
</header>

<?php if ($error !== ''): ?>
    <p class="login-error" role="alert"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form class="venta-form" method="post" action="/ventas">
    <div id="renglones">
        <?php foreach ($renglones as $elegidoId => $elegidaCantidad): ?>
        <div class="renglon">
            <label>
                <span>Producto</span>
                <select name="producto_id[]">
                    <option value="0" data-precio="0">Elegí un producto</option>
                    <?php foreach ($productos as $p): ?>
                        <option value="<?= $p['id'] ?>" data-precio="<?= $p['precio_con_iva'] ?>"
                            <?= $p['stock'] == 0 ? 'disabled' : '' ?>
                            <?= $elegidoId == $p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nombre']) ?> (<?= $p['stock'] == 0 ? 'sin stock' : 'quedan ' . $p['stock'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="cantidad">
                <span>Cantidad</span>
                <input type="number" name="cantidad[]" min="1" value="<?= (int) $elegidaCantidad ?>">
            </label>
            <button type="button" class="quitar" aria-label="Quitar este producto">Quitar</button>
        </div>
        <?php endforeach; ?>
    </div>

    <button type="button" id="agregar" class="secundario">Agregar otro producto</button>

    <p class="venta-total">Total <b id="total">$0,00</b></p>

    <p class="acciones">
        <a href="/ventas">Cancelar</a>
        <button type="submit" class="boton">Registrar venta</button>
    </p>
</form>

<script>
// Agrega y quita renglones, y calcula el total a medida que se elige
const caja = document.getElementById('renglones');
const total = document.getElementById('total');

function calcular() {
    let suma = 0;
    caja.querySelectorAll('.renglon').forEach(function (renglon) {
        const opcion = renglon.querySelector('select').selectedOptions[0];
        const cantidad = parseInt(renglon.querySelector('input').value) || 0;
        suma += parseFloat(opcion.dataset.precio) * cantidad;
    });
    total.textContent = '$' + suma.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

document.getElementById('agregar').addEventListener('click', function () {
    const nuevo = caja.querySelector('.renglon').cloneNode(true);
    nuevo.querySelector('select').value = '0';
    nuevo.querySelector('input').value = 1;
    caja.appendChild(nuevo);
    calcular();
});

caja.addEventListener('click', function (evento) {
    if (evento.target.classList.contains('quitar') && caja.querySelectorAll('.renglon').length > 1) {
        evento.target.closest('.renglon').remove();
        calcular();
    }
});

caja.addEventListener('input', calcular);
calcular();
</script>
