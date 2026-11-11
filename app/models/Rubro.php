<?php
class Rubro
{
    public static function todos(): array
    {
        return Database::conectar()->query('SELECT * FROM rubros ORDER BY nombre')->fetchAll();
    }
}
