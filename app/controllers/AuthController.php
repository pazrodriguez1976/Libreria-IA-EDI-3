<?php
class AuthController extends Controller
{
    public function formulario(): void
    {
        if (!empty($_SESSION['usuario'])) {
            $this->redirigir('/');
        }
        $this->vistaSola('auth/login', ['error' => '', 'email' => '']);
    }

    public function ingresar(): void
    {
        $email = trim($_POST['email'] ?? '');
        $clave = $_POST['clave'] ?? '';

        $usuario = Usuario::buscarPorEmail($email);

        // password_verify compara la clave escrita con la encriptada de la base
        if ($usuario && password_verify($clave, $usuario['clave'])) {
            session_regenerate_id(true);
            $_SESSION['usuario'] = ['id' => $usuario['id'], 'nombre' => $usuario['nombre']];
            $this->redirigir('/');
        }

        $this->vistaSola('auth/login', [
            'error' => 'El email o la contraseña no coinciden. Revisalos y probá de nuevo.',
            'email' => $email,
        ]);
    }

    public function salir(): void
    {
        $_SESSION = [];
        session_destroy();
        $this->redirigir('/login');
    }
}
