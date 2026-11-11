-- Guarda cada cambio de precio: cuánto valía antes, cuánto vale ahora y de qué lista vino.
-- Solo agrega una tabla nueva. No modifica ni borra datos existentes.
CREATE TABLE IF NOT EXISTS historial_precios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  producto_id INT NOT NULL,
  precio_sin_iva_anterior DECIMAL(12,2) NOT NULL,
  precio_con_iva_anterior DECIMAL(12,2) NOT NULL,
  precio_sin_iva_nuevo DECIMAL(12,2) NOT NULL,
  precio_con_iva_nuevo DECIMAL(12,2) NOT NULL,
  lista VARCHAR(150) NOT NULL,
  fecha DATETIME NOT NULL,
  FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB;
