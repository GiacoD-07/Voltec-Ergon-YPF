ALTER TABLE historial_consumo
  ADD COLUMN corriente_rele_1 NUMERIC(10, 3),
  ADD COLUMN potencia_rele_1 NUMERIC(10, 2),
  ADD COLUMN corriente_rele_2 NUMERIC(10, 3),
  ADD COLUMN potencia_rele_2 NUMERIC(10, 2),
  ADD COLUMN corriente_rele_3 NUMERIC(10, 3),
  ADD COLUMN potencia_rele_3 NUMERIC(10, 2);
