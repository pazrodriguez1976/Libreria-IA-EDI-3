<?php
class Venta
{
    // Cantidad de ventas y monto total desde una fecha (formato AAAA-MM-DD)
    public static function resumenDesde(string $fecha): array
    {
        $sql = 'SELECT COUNT(*) AS cantidad, COALESCE(SUM(total), 0) AS monto
                FROM ventas
                WHERE fecha >= :fecha';
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute(['fecha' => $fecha]);
        return $consulta->fetch();
    }

    // Últimas ventas, con la cantidad de unidades de cada una
    public static function ultimas(int $limite = 100): array
    {
        $sql = 'SELECT v.id, v.fecha, v.total, COALESCE(SUM(d.cantidad), 0) AS unidades
                FROM ventas v
                LEFT JOIN detalle_venta d ON d.venta_id = v.id
                GROUP BY v.id, v.fecha, v.total
                ORDER BY v.fecha DESC, v.id DESC
                LIMIT ' . (int) $limite;
        return Database::conectar()->query($sql)->fetchAll();
    }

    public static function buscar(int $id): ?array
    {
        $consulta = Database::conectar()->prepare('SELECT * FROM ventas WHERE id = :id');
        $consulta->execute(['id' => $id]);
        return $consulta->fetch() ?: null;
    }

    public static function detalle(int $ventaId): array
    {
        $sql = 'SELECT d.cantidad, d.precio_unitario, p.nombre, p.codigo
                FROM detalle_venta d
                JOIN productos p ON p.id = d.producto_id
                WHERE d.venta_id = :id
                ORDER BY p.nombre';
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute(['id' => $ventaId]);
        return $consulta->fetchAll();
    }

    /*
     * Registra una venta. $items es [producto_id => cantidad].
     * Es una TRANSACCIÓN: se guarda la venta, el detalle y se descuenta
     * el stock, todo junto. Si algo falla, no se guarda nada.
     */
    public static function crear(int $usuarioId, array $items): int
    {
        $pdo = Database::conectar();
        $pdo->beginTransaction();

        try {
            $buscar = $pdo->prepare('SELECT nombre, precio_con_iva FROM productos WHERE id = :id');
            // Descuenta solo si alcanza el stock
            $descontar = $pdo->prepare(
                'UPDATE productos SET stock = stock - :cantidad
                 WHERE id = :id AND stock >= :cantidad2'
            );

            $total = 0;
            $lineas = [];
            foreach ($items as $productoId => $cantidad) {
                $buscar->execute(['id' => $productoId]);
                $producto = $buscar->fetch();
                if (!$producto) {
                    throw new Exception('Uno de los productos ya no existe.');
                }

                $descontar->execute(['cantidad' => $cantidad, 'id' => $productoId, 'cantidad2' => $cantidad]);
                if ($descontar->rowCount() === 0) {
                    throw new Exception("No alcanza el stock de \"{$producto['nombre']}\".");
                }

                $lineas[] = [$productoId, $cantidad, $producto['precio_con_iva']];
                $total += $cantidad * $producto['precio_con_iva'];
            }

            $venta = $pdo->prepare('INSERT INTO ventas (fecha, total, usuario_id) VALUES (:fecha, :total, :usuario)');
            $venta->execute(['fecha' => date('Y-m-d H:i:s'), 'total' => round($total, 2), 'usuario' => $usuarioId]);
            $ventaId = (int) $pdo->lastInsertId();

            $detalle = $pdo->prepare(
                'INSERT INTO detalle_venta (venta_id, producto_id, cantidad, precio_unitario)
                 VALUES (:venta, :producto, :cantidad, :precio)'
            );
            foreach ($lineas as [$productoId, $cantidad, $precio]) {
                $detalle->execute(['venta' => $ventaId, 'producto' => $productoId, 'cantidad' => $cantidad, 'precio' => $precio]);
            }

            $pdo->commit();
            return $ventaId;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    // Cantidad de ventas y monto entre dos fechas (las dos incluidas)
    public static function resumenEntre(string $desde, string $hasta): array
    {
        $sql = 'SELECT COUNT(*) AS cantidad, COALESCE(SUM(total), 0) AS monto
                FROM ventas
                WHERE fecha >= :desde AND fecha <= :hasta';
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute(['desde' => "$desde 00:00:00", 'hasta' => "$hasta 23:59:59"]);
        return $consulta->fetch();
    }

    // Productos más vendidos (por unidades) entre dos fechas
    public static function masVendidos(string $desde, string $hasta, int $limite = 5): array
    {
        $sql = 'SELECT p.id, p.nombre,
                       SUM(d.cantidad) AS unidades,
                       SUM(d.cantidad * d.precio_unitario) AS facturado
                FROM detalle_venta d
                JOIN ventas v ON v.id = d.venta_id
                JOIN productos p ON p.id = d.producto_id
                WHERE v.fecha >= :desde AND v.fecha <= :hasta
                GROUP BY p.id, p.nombre
                ORDER BY unidades DESC, p.nombre
                LIMIT ' . (int) $limite;
        $consulta = Database::conectar()->prepare($sql);
        $consulta->execute(['desde' => "$desde 00:00:00", 'hasta' => "$hasta 23:59:59"]);
        return $consulta->fetchAll();
    }
}
