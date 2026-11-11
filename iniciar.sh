#!/bin/bash
# Prende el sistema de la libreria y el asistente de IA. Se frena con Ctrl + C.
cd "$(dirname "$0")"


PHP_CLI_SERVER_WORKERS=4 php -S localhost:8080 -t public &
PHP_PID=$!

(cd asistente-ia && ./venv/bin/uvicorn main:app --port 8001) &
IA_PID=$!

trap "kill $PHP_PID $IA_PID 2>/dev/null" EXIT
echo "Sistema:   http://localhost:8080"
echo "Asistente: http://localhost:8001/salud"
wait
