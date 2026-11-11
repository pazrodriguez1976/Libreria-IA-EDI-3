<?php
/*
 * Lee la lista de precios del proveedor (.xlsx o .csv) y devuelve
 * [codigo => ['nombre' => ..., 'sin_iva' => ..., 'con_iva' => ...]].
 * Un .xlsx es un archivo comprimido con XML adentro, por eso se lee
 * con ZipArchive y SimpleXML, sin librerías externas.
 */
class LectorLista
{
    public static function leer(string $ruta, string $nombreOriginal): array
    {
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        if ($extension === 'xlsx') {
            $filas = self::filasDeExcel($ruta);
        } elseif ($extension === 'csv') {
            $filas = self::filasDeCsv($ruta);
        } else {
            throw new Exception('El archivo tiene que ser un Excel (.xlsx) o un CSV.');
        }
        return self::armarLista($filas);
    }

    // Busca la fila de títulos y, a partir de ahí, arma la lista por código
    private static function armarLista(array $filas): array
    {
        $columnas = null;
        $lista = [];

        foreach ($filas as $fila) {
            if ($columnas === null) {
                $columnas = self::detectarColumnas($fila);
                continue;
            }
            $codigo = self::limpiarCodigo($fila[$columnas['codigo']] ?? '');
            $sinIva = self::numero($fila[$columnas['sin_iva']] ?? '');
            if ($codigo === '' || $sinIva <= 0) {
                continue;
            }
            $conIva = $columnas['con_iva'] !== null ? self::numero($fila[$columnas['con_iva']] ?? '') : 0;
            if ($conIva <= 0) {
                $conIva = $sinIva * 1.21;
            }
            $lista[$codigo] = [
                'nombre'  => trim((string) ($fila[$columnas['nombre']] ?? '')),
                'sin_iva' => round($sinIva, 2),
                'con_iva' => round($conIva, 2),
            ];
        }

        if ($columnas === null || count($lista) === 0) {
            throw new Exception('No se encontraron las columnas de código y precio en el archivo.');
        }
        return $lista;
    }

    // Devuelve en qué columna está cada dato, o null si la fila no es la de títulos
    private static function detectarColumnas(array $fila): ?array
    {
        $columnas = ['codigo' => null, 'nombre' => null, 'sin_iva' => null, 'con_iva' => null];
        foreach ($fila as $indice => $celda) {
            $texto = strtolower(trim((string) $celda));
            if (str_contains($texto, 'digo')) {
                $columnas['codigo'] = $indice;
            } elseif (str_contains($texto, 'art') || str_contains($texto, 'descrip')) {
                $columnas['nombre'] = $indice;
            } elseif (str_contains($texto, 's/iva') || str_contains($texto, 'sin iva')) {
                $columnas['sin_iva'] = $indice;
            } elseif (str_contains($texto, 'c/iva') || str_contains($texto, 'con iva')) {
                $columnas['con_iva'] = $indice;
            }
        }
        if ($columnas['codigo'] === null || $columnas['sin_iva'] === null) {
            return null;
        }
        return $columnas;
    }

    private static function limpiarCodigo($valor): string
    {
        $valor = trim((string) $valor);
        // Excel a veces guarda los códigos como número (ej. 7.79E+12)
        if (is_numeric($valor) && (stripos($valor, 'e') !== false || str_contains($valor, '.'))) {
            $valor = number_format((float) $valor, 0, '', '');
        }
        return $valor;
    }

    private static function numero($valor): float
    {
        $valor = trim((string) $valor);
        if (str_contains($valor, ',')) {
            // Formato argentino: 1.234,56
            $valor = str_replace(',', '.', str_replace('.', '', $valor));
        }
        return is_numeric($valor) ? (float) $valor : 0.0;
    }

    private static function filasDeCsv(string $ruta): array
    {
        $primera = (string) fgets(fopen($ruta, 'r'));
        $separador = substr_count($primera, ';') > substr_count($primera, ',') ? ';' : ',';
        $filas = [];
        $archivo = fopen($ruta, 'r');
        while (($fila = fgetcsv($archivo, 0, $separador, '"', '\\')) !== false) {
            $filas[] = $fila;
        }
        fclose($archivo);
        return $filas;
    }

    private static function filasDeExcel(string $ruta): array
    {
        if (!class_exists('ZipArchive')) {
            throw new Exception('Falta la extensión zip de PHP. Instalala con: sudo apt install php-zip');
        }
        $zip = new ZipArchive();
        if ($zip->open($ruta) !== true) {
            throw new Exception('No se pudo abrir el Excel. Revisá que no esté dañado.');
        }

        // Los textos del Excel están aparte, en una tabla de textos compartidos
        $textos = [];
        $xmlTextos = $zip->getFromName('xl/sharedStrings.xml');
        if ($xmlTextos !== false) {
            foreach (simplexml_load_string($xmlTextos)->si as $si) {
                $texto = (string) $si->t;
                foreach ($si->r as $parte) {
                    $texto .= (string) $parte->t;
                }
                $textos[] = $texto;
            }
        }

        $xmlHoja = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if ($xmlHoja === false) {
            throw new Exception('El Excel no tiene una primera hoja con datos.');
        }

        $filas = [];
        foreach (simplexml_load_string($xmlHoja)->sheetData->row as $row) {
            $fila = [];
            foreach ($row->c as $celda) {
                // La referencia "C12" indica la columna: A=0, B=1, C=2...
                $letras = preg_replace('/\d/', '', (string) $celda['r']);
                $indice = 0;
                foreach (str_split($letras) as $letra) {
                    $indice = $indice * 26 + (ord($letra) - 64);
                }
                $tipo = (string) $celda['t'];
                if ($tipo === 's') {
                    $valor = $textos[(int) $celda->v] ?? '';
                } elseif ($tipo === 'inlineStr') {
                    $valor = (string) $celda->is->t;
                } else {
                    $valor = (string) $celda->v;
                }
                $fila[$indice - 1] = $valor;
            }
            $filas[] = $fila;
        }
        return $filas;
    }
}
