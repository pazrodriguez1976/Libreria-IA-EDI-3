"""Configuración del asistente. Los datos secretos se leen del archivo .env"""
import os
from dotenv import load_dotenv

load_dotenv()

# Modelo de lenguaje (Groq usa el mismo formato de API que OpenAI)
GROQ_API_KEY = os.getenv("GROQ_API_KEY", "")
GROQ_URL = "https://api.groq.com/openai/v1"
MODELO = os.getenv("MODELO", "openai/gpt-oss-120b")

# API del sistema de la librería (PHP)
API_SISTEMA_URL = os.getenv("API_SISTEMA_URL", "http://localhost:8080")
API_SISTEMA_TOKEN = os.getenv("API_SISTEMA_TOKEN", "")

# Token que tiene que mandar quien quiera usar el chat
TOKEN_ASISTENTE = os.getenv("TOKEN_ASISTENTE", "")

# Base vectorial (la base de datos propia de la IA)
MODELO_EMBEDDINGS = "all-MiniLM-L6-v2"
CARPETA_BASE = os.path.join(os.path.dirname(__file__), "base_vectorial")
TOP_K = 5            # cuántos productos parecidos se recuperan
UMBRAL = 0.25        # similitud mínima para considerar que un producto coincide

# Límites del agente
MAX_PASOS = 5        # freno de emergencia del loop
MAX_HISTORIAL = 10   # mensajes anteriores que se aceptan del front

if not GROQ_API_KEY:
    raise SystemExit("Falta GROQ_API_KEY en el archivo .env")
