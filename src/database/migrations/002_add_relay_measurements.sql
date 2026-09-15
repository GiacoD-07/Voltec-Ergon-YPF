ALTER TABLE historial_consumo
  ADD COLUMN corriente_rele_1 DECIMAL(10, 3) NULL,
  ADD COLUMN potencia_rele_1 DECIMAL(10, 2) NULL,
  ADD COLUMN corriente_rele_2 DECIMAL(10, 3) NULL,
  ADD COLUMN potencia_rele_2 DECIMAL(10, 2) NULL,
  ADD COLUMN corriente_rele_3 DECIMAL(10, 3) NULL,
  ADD COLUMN potencia_rele_3 DECIMAL(10, 2) NULL;
