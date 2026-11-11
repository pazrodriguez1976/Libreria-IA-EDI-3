<?php
class Precio
{
    /*
     * Compara la lista del proveedor con los productos del sistema.
     * Devuelve: los que cambian, cuántos quedan igual, los que no vinieron
     * en la lista y cuántos artículos de la lista se ignoran.
     */
    public static function comparar(array $lista): array
    {
        $cambian = [];
        $iguales = 0;
        $faltantes = [];
        $encontrados = 0;

        foreach (Producto::buscar() as $p) {
            if (!isset($lista[$p['codigo']])) {
                $faltantes[] = $p['nombre'];
                continue;
            }
            $encontrados++;
            $nuevo = $lista[$p['codigo']];
            if (abs($nuevo['con_iva'] - $p['precio_con_iva']) < 0.01) {
                $iguales++;
                continue;
            }
            $cambian[] = [
                'id'               => (int) $p['id'],
                'nombre'           => $p['nombre'],
                'sin_iva_anterior' => (float) $p['precio_sin_iva'],
                'con_iva_anterior' => (float) $p['precio_con_iva'],
                'sin_iva_nuevo'    => $nuevo['sin_iva'],
                'con_iva_nuevo'    => $nuevo['con_iva'],
                'variacion'        => round(($nuevo['con_iva'] - $p['precio_con_iva']) / $p['precio_con_iva'] * 100, 1),
            ];
        }

        // Los de mayor aumento primero
        usort($cambian, fn($a, $b) => $b['variacion'] <=> $a['variacion']);

        return [
            'cambian'   => $cambian,
            'iguales'   => $iguales,
            'faltantes' => $faltantes,
            'ignorados' => count($lista) - $encontrados,
        ];
    }

    // Aplica los cambios en una transacción: guarda el historial y actualiza el precio
    public static function aplicar(array $cambios, string $nombreLista): int
    {
        $pdo = Database::conectar();
        $pdo->beginTransaction();
        try {
            $historial = $pdo->prepare(
                'INSERT INTO historial_precios
                    (producto_id, precio_sin_iva_anterior, precio_con_iva_anterior,
                     precio_sin_iva_nuevo, precio_con_iva_nuevo, lista, fecha)
                 VALUES (:id, :sa, :ca, :sn, :cn, :lista, :fecha)'
            );
            $actualizar = $pdo->prepare(
                'UPDATE productos SET precio_sin_iva = :sn, precio_con_iva = :cn WHERE id = :id'
            );
            $fecha = date('Y-m-d H:i:s');

            foreach ($cambios as $c) {
                $historial->execute([
                    'id' => $c['id'], 'sa' => $c['sin_iva_anterior'], 'ca' => $c['con_iva_anterior'],
                    'sn' => $c['sin_iva_nuevo'], 'cn' => $c['con_iva_nuevo'],
                    'lista' => $nombreLista, 'fecha' => $fecha,
                ]);
                $actualizar->execute(['sn' => $c['sin_iva_nuevo'], 'cn' => $c['con_iva_nuevo'], 'id' => $c['id']]);
            }
            $pdo->commit();
            return count($cambios);
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    // Cambios de la última lista aplicada, de mayor a menor aumento
    public static function ultimosCambios(int $limite = 60): array
    {
        $pdo = Database::conectar();
        $ultima = $pdo->query('SELECT lista, fecha FROM historial_precios ORDER BY fecha DESC, id DESC LIMIT 1')->fetch();
        if (!$ultima) {
            return ['lista' => null, 'fecha' => null, 'cambios' => []];
        }
        $sql = 'SELECT p.nombre, h.precio_con_iva_anterior, h.precio_con_iva_nuevo,
                       ROUND((h.precio_con_iva_nuevo - h.precio_con_iva_anterior) / h.precio_con_iva_anterior * 100, 1) AS variacion
                FROM historial_precios h
                JOIN productos p ON p.id = h.producto_id
                WHERE h.fecha = :fecha
                ORDER BY variacion DESC, p.nombre
                LIMIT ' . (int) $limite;
        $consulta = $pdo->prepare($sql);
        $consulta->execute(['fecha' => $ultima['fecha']]);
        return ['lista' => $ultima['lista'], 'fecha' => $ultima['fecha'], 'cambios' => $consulta->fetchAll()];
    }
}
