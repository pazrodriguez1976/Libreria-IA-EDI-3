"""
API del asistente de IA. No guarda memoria: cada pedido trae la conversación completa.
    GET  /salud  -> estado del servicio
    POST /chat   -> recibe los mensajes y devuelve la respuesta
"""
import hmac
from typing import Literal

from fastapi import FastAPI, Header, HTTPException
from pydantic import BaseModel, Field

import agente
import base_vectorial
import config

app = FastAPI(title="Asistente de la Librería NaSer")


class Mensaje(BaseModel):
    # Solo se aceptan estos dos roles: el front no puede mandar mensajes "system"
    role: Literal["user", "assistant"]
    content: str = Field(min_length=1, max_length=1000)


class Pedido(BaseModel):
    mensajes: list[Mensaje] = Field(min_length=1)


def verificar_token(token: str | None) -> None:
    if not config.TOKEN_ASISTENTE or not hmac.compare_digest(token or "", config.TOKEN_ASISTENTE):
        raise HTTPException(status_code=401, detail="Token inválido o ausente")


@app.get("/salud")
def salud():
    return {"estado": "ok", "modelo": config.MODELO, "productos_indexados": base_vectorial.cantidad()}


@app.post("/chat")
def chat(pedido: Pedido, x_token: str | None = Header(default=None)):
    verificar_token(x_token)
    historial = [m.model_dump() for m in pedido.mensajes[-config.MAX_HISTORIAL:]]
    if historial[-1]["role"] != "user":
        raise HTTPException(status_code=400, detail="El último mensaje tiene que ser del usuario")
    try:
        return agente.responder(historial)
    except Exception as error:
        raise HTTPException(status_code=502, detail=f"No se pudo consultar al modelo: {error}")
