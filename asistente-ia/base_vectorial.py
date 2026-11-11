"""
Base de datos de la IA (ChromaDB).
Guarda un embedding por producto para poder buscarlos por significado.
Acá NO se guardan stock ni precios: esos datos se piden siempre al sistema.
"""
import unicodedata
from datetime import date

import chromadb

import config

# Descripción por tipo de producto. Se suma al nombre antes de generar el
# embedding, para que la búsqueda entienda sinónimos y usos.
DESCRIPCIONES = {
    "boligrafo": "Birome o lapicera para escribir con tinta.",
    "lapiz": "Lápiz de grafito para escribir y dibujar.",
    "marcador": "Marcador, fibra o fibrón para escribir, pintar y colorear.",
    "resaltador": "Resaltador fluorescente para marcar y subrayar texto.",
    "cuaderno": "Cuaderno con hojas para escribir y tomar apuntes en la escuela.",
    "carpeta": "Carpeta para guardar y archivar hojas y documentos.",
    "block": "Block de hojas para dibujar, anotar o hacer lettering.",
    "repuesto": "Repuesto de hojas para carpeta.",
    "folios": "Folios plásticos transparentes para proteger hojas en la carpeta.",
    "goma": "Goma de borrar para lápiz.",
    "sacapunta": "Sacapuntas para afilar lápices.",
    "regla": "Regla para medir y trazar líneas rectas.",
    "tijera": "Tijera para cortar papel y cartulina.",
    "adhesivo": "Pegamento, cola o plasticola para pegar papel y manualidades.",
    "cartuchera": "Cartuchera o estuche para guardar lápices y útiles.",
    "compas": "Compás para dibujar círculos en geometría.",
    "mochila": "Mochila para llevar los útiles a la escuela.",
    "pinturitas": "Lápices de colores para pintar y colorear dibujos.",
    "tempera": "Témpera, pintura al agua para pintar con pincel.",
    "acuarelas": "Acuarelas, pintura que se diluye con agua para pintar con pincel.",
    "pincel": "Pincel para pintar con témpera, acuarela, óleo o acrílico.",
    "crayones": "Crayones o pasteles al óleo para dibujar y colorear.",
    "abrochadora": "Abrochadora o engrapadora para unir hojas con broches.",
    "perforadora": "Perforadora para hacer agujeros en las hojas.",
    "calculadora": "Calculadora para hacer cuentas.",
    "cinta": "Cinta adhesiva de papel para pegar, enmascarar y embalar.",
    "clips": "Clips para sujetar hojas sueltas.",
}

_modelo = None


def _sin_acentos(texto: str) -> str:
    normal = unicodedata.normalize("NFD", texto)
    return "".join(c for c in normal if unicodedata.category(c) != "Mn")


def generar_embeddings(textos: list[str]) -> list[list[float]]:
    """Convierte textos en vectores con el modelo de embeddings."""
    global _modelo
    if _modelo is None:
        from sentence_transformers import SentenceTransformer
        _modelo = SentenceTransformer(config.MODELO_EMBEDDINGS)
    return _modelo.encode(textos).tolist()


def _coleccion():
    cliente = chromadb.PersistentClient(path=config.CARPETA_BASE)
    # "cosine": compara los vectores por similitud del coseno
    return cliente.get_or_create_collection("productos", metadata={"hnsw:space": "cosine"})


def texto_del_producto(producto: dict) -> str:
    """Arma el texto que se convierte en embedding (el 'chunk' de cada producto)."""
    tipo = _sin_acentos(producto["nombre"].split()[0].lower())
    descripcion = DESCRIPCIONES.get(tipo, "")
    return f"{producto['nombre']}. Rubro: {producto['rubro']}. {descripcion}".strip()


def indexar(productos: list[dict]) -> int:
    """Vuelve a cargar todos los productos en la base vectorial."""
    cliente = chromadb.PersistentClient(path=config.CARPETA_BASE)
    try:
        cliente.delete_collection("productos")
    except Exception:
        pass
    coleccion = _coleccion()

    textos = [texto_del_producto(p) for p in productos]
    coleccion.add(
        ids=[str(p["id"]) for p in productos],
        documents=textos,
        embeddings=generar_embeddings(textos),
        metadatas=[
            {
                "producto_id": p["id"],
                "codigo": p["codigo"],
                "nombre": p["nombre"],
                "rubro": p["rubro"],
                "modelo": config.MODELO_EMBEDDINGS,
                "fecha": date.today().isoformat(),
            }
            for p in productos
        ],
    )
    return coleccion.count()


def buscar(consulta: str, k: int = config.TOP_K) -> list[dict]:
    """Devuelve los productos más parecidos a la consulta, con su similitud."""
    coleccion = _coleccion()
    if coleccion.count() == 0:
        return []
    resultado = coleccion.query(query_embeddings=generar_embeddings([consulta]), n_results=k)

    encontrados = []
    for metadatos, distancia in zip(resultado["metadatas"][0], resultado["distances"][0]):
        similitud = round(1 - distancia, 3)   # Chroma devuelve distancia: menor = más parecido
        if similitud >= config.UMBRAL:
            encontrados.append({"producto_id": metadatos["producto_id"], "similitud": similitud})
    return encontrados


def cantidad() -> int:
    return _coleccion().count()
