CREATE DATABASE IF NOT EXISTS licitaciones
    CHARACTER SET utf8 COLLATE utf8_unicode_ci;

USE licitaciones;

CREATE TABLE actividades (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    codigo_segmento     INT,
    segmento            VARCHAR(200),
    codigo_familia      INT,
    familia             VARCHAR(200),
    codigo_clase        INT,
    clase               VARCHAR(200),
    codigo_producto     INT,
    producto            VARCHAR(200)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE ofertas (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    consecutivo         VARCHAR(200) UNIQUE,
    objeto              VARCHAR(200) NOT NULL,
    descripcion         VARCHAR(400) NOT NULL,
    moneda              VARCHAR(3) NOT NULL,
    presupuesto         DECIMAL(15, 2) NOT NULL,
    actividad_id        INT NOT NULL,
    fecha_inicio        DATE NOT NULL,
    hora_inicio         TIME NOT NULL,
    fecha_cierre        DATE NOT NULL,
    hora_cierre         TIME NOT NULL,
    estado              VARCHAR(20) DEFAULT 'activo',
    creado_en           DATETIME,
    actualizado_en      DATETIME,
    FOREIGN KEY (actividad_id) REFERENCES actividades(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE ofertas_documentos (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    licitacion_id      INT NOT NULL,
    titulo             VARCHAR(100) NOT NULL,
    descripcion        VARCHAR(200) NOT NULL,
    archivo            VARCHAR(255) NOT NULL,
    creado_en          DATETIME,
    FOREIGN KEY (licitacion_id) REFERENCES ofertas(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- Índices para optimizar búsquedas frecuentes
-- Se indexan los campos usados en filtros y WHERE
-- -----------------------------------------------------
CREATE INDEX idx_producto     ON actividades (producto);
CREATE INDEX idx_consecutivo  ON ofertas (consecutivo);
CREATE INDEX idx_objeto       ON ofertas (objeto);