<?php
/*
 * Chat con el asistente de IA. Solo entra el propietario (pide login).
 * El navegador le habla a este controlador, y este le habla al servicio
 * de IA en Python. Así el token nunca llega al navegador.
 */
class AsistenteController extends Controller
{
    public function index(): void
    {
        $this->requerirLogin();
        $this->vista('asistente/index', ['titulo' => 'Asistente IA', 'seccion' => 'asistente']);
    }

    // POST /asistente/preguntar  (recibe y devuelve JSON)
    public function preguntar(): void
    {
        if (empty($_SESSION['usuario'])) {
            $this->json(['error' => 'Tu sesión venció. Volvé a ingresar.'], 401);
        }

        $pedido = json_decode(file_get_contents('php://input'), true);
        $mensajes = $pedido['mensajes'] ?? [];
        if (!is_array($mensajes) || count($mensajes) === 0) {
            $this->json(['error' => 'Escribí una pregunta.'], 400);
        }

        $config = require __DIR__ . '/../../config/config.php';

        // Pedido POST al servicio de IA, con el token secreto
        $contexto = stream_context_create(['http' => [
            'method'        => 'POST',
            'header'        => "Content-Type: application/json\r\nX-Token: {$config['asistente_token']}\r\n",
            'content'       => json_encode(['mensajes' => $mensajes]),
            'timeout'       => 60,
            'ignore_errors' => true,
        ]]);
        $respuesta = @file_get_contents($config['asistente_url'] . '/chat', false, $contexto);

        if ($respuesta === false) {
            $this->json(['error' => 'El asistente no está disponible. Revisá que el servicio de IA esté prendido.'], 503);
        }

        $datos = json_decode($respuesta, true);
        if (!isset($datos['respuesta'])) {
            $this->json(['error' => 'El asistente no pudo responder. Probá de nuevo en un momento.'], 502);
        }

        $this->json([
            'respuesta' => $datos['respuesta'],
            'pasos'     => $datos['pasos'] ?? [],
            'tokens'    => $datos['tokens'] ?? 0,
        ]);
    }

    private function json(array $datos, int $codigo = 200): void
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
