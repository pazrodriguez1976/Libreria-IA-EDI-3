<header class="encabezado">
    <h1>Asistente IA</h1>
    <p>Consultá stock, precios y ventas</p>
</header>

<section class="chat">
    <div id="mensajes" class="chat-mensajes" aria-live="polite">
        <div class="chat-inicio" id="inicio">
            <p>Preguntame lo que necesites saber del local. Por ejemplo:</p>
            <ul>
                <li><button type="button" class="sugerencia">¿Qué productos tengo que reponer?</button></li>
                <li><button type="button" class="sugerencia">¿Tengo algo para pintar con agua?</button></li>
                <li><button type="button" class="sugerencia">¿Cuál fue el producto más vendido este mes?</button></li>
                <li><button type="button" class="sugerencia">¿Me alcanza el stock para vender 20 carpetas A4?</button></li>
            </ul>
        </div>
    </div>

    <form id="formulario" class="chat-form">
        <label class="oculto" for="pregunta">Tu pregunta</label>
        <input type="text" id="pregunta" maxlength="500" autocomplete="off" placeholder="Escribí tu pregunta" required autofocus>
        <button type="submit" class="boton" id="enviar">Preguntar</button>
    </form>
    <p class="chat-pie">
        <button type="button" class="secundario" id="nueva">Empezar una conversación nueva</button>
    </p>
</section>

<script>
// La API de la IA no tiene memoria: la conversación se guarda acá,
// en el navegador, y se manda completa en cada pregunta.
let historial = [];

const caja = document.getElementById('mensajes');
const formulario = document.getElementById('formulario');
const campo = document.getElementById('pregunta');
const boton = document.getElementById('enviar');

const NOMBRES = {
    buscar_productos: 'buscó productos',
    productos_para_reponer: 'revisó qué hay que reponer',
    resumen_ventas: 'consultó el resumen de ventas',
    productos_mas_vendidos: 'consultó los más vendidos',
};

// Muestra el texto de forma segura: sin interpretar HTML, solo negritas y saltos de línea
function formatear(texto) {
    const seguro = texto.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    return seguro.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');
}

function agregar(clase, html) {
    const inicio = document.getElementById('inicio');
    if (inicio) inicio.remove();
    const div = document.createElement('div');
    div.className = 'mensaje ' + clase;
    div.innerHTML = html;
    caja.appendChild(div);
    div.scrollIntoView({ block: 'end' });
    return div;
}

async function preguntar(texto) {
    agregar('mio', formatear(texto));
    historial.push({ role: 'user', content: texto });

    const espera = agregar('ia espera', 'Consultando el sistema…');
    boton.disabled = true;

    try {
        const respuesta = await fetch('/asistente/preguntar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ mensajes: historial }),
        });
        const datos = await respuesta.json();
        espera.remove();

        if (!respuesta.ok) {
            historial.pop();
            agregar('ia error', formatear(datos.error || 'No se pudo consultar al asistente.'));
        } else {
            historial.push({ role: 'assistant', content: datos.respuesta });
            let html = formatear(datos.respuesta);
            if (datos.pasos.length > 0) {
                const usados = datos.pasos.map(p => NOMBRES[p.herramienta] || p.herramienta);
                html += '<small>Para responder ' + [...new Set(usados)].join(' y ') + '. Usó ' + datos.tokens + ' tokens.</small>';
            }
            agregar('ia', html);
        }
    } catch (error) {
        espera.remove();
        historial.pop();
        agregar('ia error', 'No hay conexión con el sistema. Revisá que el servidor esté prendido.');
    }

    boton.disabled = false;
    campo.focus();
}

formulario.addEventListener('submit', function (evento) {
    evento.preventDefault();
    const texto = campo.value.trim();
    if (texto === '' || boton.disabled) return;
    campo.value = '';
    preguntar(texto);
});

caja.addEventListener('click', function (evento) {
    if (evento.target.classList.contains('sugerencia') && !boton.disabled) {
        preguntar(evento.target.textContent);
    }
});

document.getElementById('nueva').addEventListener('click', function () {
    location.reload();
});
</script>
