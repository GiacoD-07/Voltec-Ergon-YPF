-- Guarda cada medición recibida desde el ESP32.
CREATE TABLE IF NOT EXISTS historial_consumo (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dispositivo_id VARCHAR(50) NOT NULL DEFAULT 'EcoSmart_01',
  voltaje DECIMAL(10, 2) NOT NULL,
  corriente DECIMAL(10, 3) NOT NULL,
  potencia_activa DECIMAL(10, 2) NOT NULL,
  energia_total_kwh DECIMAL(12, 4) NOT NULL,
  consumo_fantasma TINYINT(1) NOT NULL DEFAULT 0,
  corriente_rele_1 DECIMAL(10, 3) NULL,
  potencia_rele_1 DECIMAL(10, 2) NULL,
  corriente_rele_2 DECIMAL(10, 3) NULL,
  potencia_rele_2 DECIMAL(10, 2) NULL,
  corriente_rele_3 DECIMAL(10, 3) NULL,
  potencia_rele_3 DECIMAL(10, 2) NULL,
  fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_historial_dispositivo_fecha (dispositivo_id, fecha_registro)
) ENGINE=InnoDB;

-- Conserva el último estado solicitado para cada dispositivo.
CREATE TABLE IF NOT EXISTS control_reles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dispositivo_id VARCHAR(50) UNIQUE NOT NULL DEFAULT 'EcoSmart_01',
  rele_1 TINYINT(1) NOT NULL DEFAULT 0,
  rele_2 TINYINT(1) NOT NULL DEFAULT 0,
  rele_3 TINYINT(1) NOT NULL DEFAULT 0,
  ultima_modificacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Crea el registro inicial sin duplicarlo si ya existe.
INSERT INTO control_reles (dispositivo_id, rele_1, rele_2, rele_3)
VALUES ('EcoSmart_01', 0, 0, 0)
ON DUPLICATE KEY UPDATE dispositivo_id = dispositivo_id;
