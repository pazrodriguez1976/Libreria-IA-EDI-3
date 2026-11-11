"""Cliente de la API del sistema de la librería (PHP). Solo hace pedidos GET."""
import requests

import config


def consultar(ruta: str, parametros: dict | None = None):
    respuesta = requests.get(
        config.API_SISTEMA_URL + ruta,
        params=parametros or {},
        headers={"X-Api-Token": config.API_SISTEMA_TOKEN},
        timeout=10,
    )
    if respuesta.status_code == 404:
        return None
    respuesta.raise_for_status()
    return respuesta.json()
