#!/bin/bash
# Prende el sistema de la librería y el asistente de IA. Se frena con Ctrl + C.
cd "$(dirname "$0")"

# El chat hace que PHP reciba dos pedidos a la vez (el del navegador y el de
# la IA consultando la API), por eso se levanta con 4 procesos.
PHP_CLI_SERVER_WORKERS=4 php -S localhost:8080 -t public &
PHP_PID=$!

(cd asistente-ia && ./venv/bin/uvicorn main:app --port 8001) &
IA_PID=$!

trap "kill $PHP_PID $IA_PID 2>/dev/null" EXIT
echo "Sistema:   http://localhost:8080"
echo "Asistente: http://localhost:8001/salud"
wait
