"""
Herramientas (function calling) que el agente puede usar.
Cada una tiene: la descripción que lee el modelo y la función que se ejecuta.
"""
import json

import base_vectorial
import sistema

# Lo que ve el modelo: nombre, para qué sirve y qué parámetros recibe
TOOLS = [
    {
        "type": "function",
        "function": {
            "name": "buscar_productos",
            "description": (
                "Busca productos de la librería por significado (nombre, tipo o uso) "
                "y devuelve su precio y stock actuales. Usala siempre que pregunten "
                "por un producto, su stock o su precio."
            ),
            "parameters": {
                "type": "object",
                "properties": {
                    "consulta": {
                        "type": "string",
                        "description": "Qué producto se busca. Ej.: 'abrochadora Olami', 'algo para pintar con agua'",
                    }
                },
                "required": ["consulta"],
            },
        },
    },
    {
        "type": "function",
        "function": {
            "name": "productos_para_reponer",
            "description": "Lista los productos cuyo stock está por debajo del mínimo.",
            "parameters": {"type": "object", "properties": {}},
        },
    },
    {
        "type": "function",
        "function": {
            "name": "resumen_ventas",
            "description": "Cantidad de ventas y monto total vendido entre dos fechas.",
            "parameters": {
                "type": "object",
                "properties": {
                    "desde": {"type": "string", "description": "Fecha inicial, formato AAAA-MM-DD"},
                    "hasta": {"type": "string", "description": "Fecha final, formato AAAA-MM-DD"},
                },
                "required": ["desde", "hasta"],
            },
        },
    },
    {
        "type": "function",
        "function": {
            "name": "productos_mas_vendidos",
            "description": "Ranking de los productos más vendidos (por unidades) entre dos fechas.",
            "parameters": {
                "type": "object",
                "properties": {
                    "desde": {"type": "string", "description": "Fecha inicial, formato AAAA-MM-DD"},
                    "hasta": {"type": "string", "description": "Fecha final, formato AAAA-MM-DD"},
                    "limite": {"type": "integer", "description": "Cuántos productos mostrar (por defecto 5)"},
                },
                "required": ["desde", "hasta"],
            },
        },
    },
    {
        "type": "function",
        "function": {
            "name": "cambios_de_precios",
            "description": (
                "Cambios de precio de la última lista del proveedor que se aplicó: precio anterior, "
                "precio nuevo y variación porcentual, de mayor a menor aumento. Usala cuando "
                "pregunten qué aumentó, cuánto subió un producto o por la última actualización de precios."
            ),
            "parameters": {
                "type": "object",
                "properties": {
                    "limite": {"type": "integer", "description": "Cuántos productos traer (por defecto 10, máximo 60)"}
                },
            },
        },
    },
]


def buscar_productos(consulta: str):
    # 1) RAG: la base vectorial encuentra los productos parecidos
    parecidos = base_vectorial.buscar(consulta)
    # 2) Los datos actuales (stock y precio) se piden al sistema
    productos = []
    for item in parecidos:
        producto = sistema.consultar("/api/productos/ver", {"id": item["producto_id"]})
        if producto:
            producto["similitud"] = item["similitud"]
            productos.append(producto)
    if not productos:
        return {"resultado": "No se encontró ningún producto que coincida."}
    return productos


def productos_para_reponer():
    return sistema.consultar("/api/productos/reponer")


def resumen_ventas(desde: str, hasta: str):
    return sistema.consultar("/api/ventas/resumen", {"desde": desde, "hasta": hasta})


def productos_mas_vendidos(desde: str, hasta: str, limite: int = 5):
    return sistema.consultar("/api/ventas/mas-vendidos", {"desde": desde, "hasta": hasta, "limite": limite})


def cambios_de_precios(limite: int = 10):
    return sistema.consultar("/api/precios/cambios", {"limite": limite})


FUNCIONES = {
    "cambios_de_precios": cambios_de_precios,
    "buscar_productos": buscar_productos,
    "productos_para_reponer": productos_para_reponer,
    "resumen_ventas": resumen_ventas,
    "productos_mas_vendidos": productos_mas_vendidos,
}


def ejecutar(nombre: str, argumentos: dict) -> str:
    """Ejecuta la herramienta que pidió el modelo y devuelve el resultado como texto."""
    if nombre not in FUNCIONES:
        return f"Error: la herramienta {nombre} no existe"
    try:
        return json.dumps(FUNCIONES[nombre](**argumentos), ensure_ascii=False)
    except Exception as error:
        return f"Error en {nombre}: {error}"
