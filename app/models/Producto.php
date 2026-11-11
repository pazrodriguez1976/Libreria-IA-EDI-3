<?php
class Producto
{
    // Lista productos. Se puede filtrar por texto y por rubro.
    public static function buscar(string $texto = '', int $rubroId = 0): array
    {
        $sql = 'SELECT p.*, r.nombre AS rubro
                FROM productos p
                JOIN rubros r ON r.id = p.rubro_id
                WHERE 1 = 1';
        $parametros = [];

        if ($texto !== '') {
            $sql .= ' AND (p.nombre LIKE :texto OR p.codigo LIKE :texto2)';
            $parametros['texto'] = "%$texto%";
            $parametros['texto2'] = "%$texto%";
        }
        if ($rubroId > 0) {
            $sql .= ' AND p.rubro_id = :rubro';
            $parametros['rubro'] = $rubroId;
        }
        $sql .= ' ORDER BY r.nombre, p.nombre';

        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute($parametros);
        return $consulta->fetchAll();
    }

    // Productos con stock por debajo del mínimo (los más urgentes primero)
    public static function paraReponer(): array
    {
        $sql = 'SELECT p.*, r.nombre AS rubro
                FROM productos p
                JOIN rubros r ON r.id = p.rubro_id
                WHERE p.stock < p.stock_minimo
                ORDER BY p.stock, p.nombre';
        return Database::conectar()->query($sql)->fetchAll();
    }

    public static function contar(): int
    {
        return (int) Database::conectar()->query('SELECT COUNT(*) FROM productos')->fetchColumn();
    }

    public static function buscarPorId(int $id): ?array
    {
        $sql = 'SELECT p.*, r.nombre AS rubro
                FROM productos p
                JOIN rubros r ON r.id = p.rubro_id
                WHERE p.id = :id';
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute(['id' => $id]);
        return $consulta->fetch() ?: null;
    }
}
