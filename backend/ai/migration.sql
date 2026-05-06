-- ============================================================
-- Migración BIN: Integración IA
-- Ejecutar en bin_db (VM Backend: 10.0.2.8)
-- ============================================================

CREATE TABLE IF NOT EXISTS ResultadosIA (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    id_contenedor       INT NOT NULL,
    prioridad           ENUM('alta', 'media', 'baja') NOT NULL,
    score               FLOAT          DEFAULT NULL COMMENT 'Confianza del modelo (0.0 - 1.0)',
    volumen_pct         FLOAT          DEFAULT NULL COMMENT 'Porcentaje de llenado calculado',
    temperatura         FLOAT          DEFAULT NULL,
    humedad             FLOAT          DEFAULT NULL,
    peso_kg             FLOAT          DEFAULT NULL,
    fecha_clasificacion DATETIME       DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ria_contenedor
        FOREIGN KEY (id_contenedor)
        REFERENCES Contenedores(id_contenedor)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Índice para consultas rápidas por prioridad
CREATE INDEX idx_ria_prioridad ON ResultadosIA (prioridad, fecha_clasificacion DESC);
