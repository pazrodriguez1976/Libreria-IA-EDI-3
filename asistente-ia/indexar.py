"""
Carga los productos del sistema en la base vectorial.
Se ejecuta una vez al principio y cada vez que se agregan productos:
    python indexar.py
"""
import base_vectorial
import sistema

productos = sistema.consultar("/api/productos")
total = base_vectorial.indexar(productos)
print(f"Listo: {total} productos indexados en la base vectorial.")
