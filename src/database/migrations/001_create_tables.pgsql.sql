-- Guarda cada medición recibida desde el ESP32.
CREATE TABLE IF NOT EXISTS historial_consumo (
  id SERIAL PRIMARY KEY,
  dispositivo_id VARCHAR(50) NOT NULL DEFAULT 'EcoSmart_01',
  voltaje NUMERIC(10, 2) NOT NULL,
  corriente NUMERIC(10, 3) NOT NULL,
  potencia_activa NUMERIC(10, 2) NOT NULL,
  energia_total_kwh NUMERIC(12, 4) NOT NULL,
  consumo_fantasma SMALLINT NOT NULL DEFAULT 0,
  corriente_rele_1 NUMERIC(10, 3),
  potencia_rele_1 NUMERIC(10, 2),
  corriente_rele_2 NUMERIC(10, 3),
  potencia_rele_2 NUMERIC(10, 2),
  corriente_rele_3 NUMERIC(10, 3),
  potencia_rele_3 NUMERIC(10, 2),
  fecha_registro TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_historial_dispositivo_fecha
  ON historial_consumo (dispositivo_id, fecha_registro);

-- Conserva el último estado solicitado para cada dispositivo.
CREATE TABLE IF NOT EXISTS control_reles (
  id SERIAL PRIMARY KEY,
  dispositivo_id VARCHAR(50) UNIQUE NOT NULL DEFAULT 'EcoSmart_01',
  rele_1 SMALLINT NOT NULL DEFAULT 0,
  rele_2 SMALLINT NOT NULL DEFAULT 0,
  rele_3 SMALLINT NOT NULL DEFAULT 0,
  ultima_modificacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Crea el registro inicial sin duplicarlo si ya existe.
INSERT INTO control_reles (dispositivo_id, rele_1, rele_2, rele_3)
VALUES ('EcoSmart_01', 0, 0, 0)
ON CONFLICT (dispositivo_id) DO NOTHING;
