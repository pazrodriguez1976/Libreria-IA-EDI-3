<?php
class Usuario
{
    public static function buscarPorEmail(string $email): ?array
    {
        $consulta = Database::conectar()->prepare('SELECT * FROM usuarios WHERE email = :email');
        $consulta->execute(['email' => $email]);
        return $consulta->fetch() ?: null;
    }
}
