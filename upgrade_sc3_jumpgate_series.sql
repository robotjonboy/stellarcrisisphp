-- Fill ship-type options for existing sc3 Jumpgate-era series.
-- Safe to re-run. Does not change Jumpgate rows or any type that already exists.
--
-- Target: sc3 series (and their games) that have Jumpgate but do not yet have
-- Carrier, Builder, or Morpher options. Those series were created when Jumpgate
-- was the only v3 ship, so they should keep Jumpgate and the twelve classic
-- ships, but not the other v3 ships.
--
-- Resulting access for missing types:
--   Developed:    Attack, Science, Colony
--   Unrestricted: remaining v2 types
--   Barred:       Carrier, Builder, Morpher
-- Jumpgate status, range, loss, and costs are left as-is.
--
-- Preview:
-- SELECT s.id, s.name
-- FROM series s
-- WHERE s.game_type = 'sc3'
--   AND EXISTS (
--     SELECT 1 FROM series_ship_type_options o
--     WHERE o.series_id = s.id AND o.ship_type = 'Jumpgate')
--   AND NOT EXISTS (
--     SELECT 1 FROM series_ship_type_options o
--     WHERE o.series_id = s.id AND o.ship_type IN ('Carrier','Builder','Morpher'));

INSERT INTO series_ship_type_options
  (series_id, ship_type, status, range_multiplier, loss, build_cost, maintenance_cost)
SELECT s.id, t.ship_type, t.status, t.range_multiplier, t.loss, t.build_cost, t.maintenance_cost
FROM series s
CROSS JOIN (
  SELECT 'Attack' AS ship_type, 'Developed' AS status, NULL AS range_multiplier, NULL AS loss, NULL AS build_cost, NULL AS maintenance_cost
  UNION ALL SELECT 'Science', 'Developed', NULL, NULL, NULL, NULL
  UNION ALL SELECT 'Colony', 'Developed', NULL, NULL, NULL, NULL
  UNION ALL SELECT 'Cloaker', 'Unrestricted', NULL, NULL, NULL, NULL
  UNION ALL SELECT 'Doomsday', 'Unrestricted', NULL, NULL, NULL, NULL
  UNION ALL SELECT 'Engineer', 'Unrestricted', NULL, 0.5, NULL, NULL
  UNION ALL SELECT 'Minefield', 'Unrestricted', NULL, NULL, NULL, NULL
  UNION ALL SELECT 'Minesweeper', 'Unrestricted', NULL, NULL, NULL, NULL
  UNION ALL SELECT 'Satellite', 'Unrestricted', NULL, NULL, NULL, NULL
  UNION ALL SELECT 'Stargate', 'Unrestricted', NULL, NULL, NULL, NULL
  UNION ALL SELECT 'Terraformer', 'Unrestricted', NULL, NULL, NULL, NULL
  UNION ALL SELECT 'Troopship', 'Unrestricted', NULL, NULL, NULL, NULL
  UNION ALL SELECT 'Carrier', 'Barred', NULL, 0.5, 50, 16
  UNION ALL SELECT 'Builder', 'Barred', NULL, NULL, 100, 32
  UNION ALL SELECT 'Morpher', 'Barred', NULL, 0.5, 75, 32
) t
WHERE s.game_type = 'sc3'
  AND EXISTS (
    SELECT 1 FROM series_ship_type_options o
    WHERE o.series_id = s.id AND o.ship_type = 'Jumpgate'
  )
  AND NOT EXISTS (
    SELECT 1 FROM series_ship_type_options o
    WHERE o.series_id = s.id AND o.ship_type IN ('Carrier', 'Builder', 'Morpher')
  )
  AND NOT EXISTS (
    SELECT 1 FROM series_ship_type_options o
    WHERE o.series_id = s.id AND o.ship_type = t.ship_type
  );

-- Same fill for games of those series (empty or in progress).
INSERT INTO game_ship_type_options
  (game_id, ship_type, status, range_multiplier, loss, build_cost, maintenance_cost)
SELECT g.id, t.ship_type, t.status, t.range_multiplier, t.loss, t.build_cost, t.maintenance_cost
FROM games g
INNER JOIN series s ON s.id = g.series_id
CROSS JOIN (
  SELECT 'Attack' AS ship_type, 'Developed' AS status, NULL AS range_multiplier, NULL AS loss, NULL AS build_cost, NULL AS maintenance_cost
  UNION ALL SELECT 'Science', 'Developed', NULL, NULL, NULL, NULL
  UNION ALL SELECT 'Colony', 'Developed', NULL, NULL, NULL, NULL
  UNION ALL SELECT 'Cloaker', 'Unrestricted', NULL, NULL, NULL, NULL
  UNION ALL SELECT 'Doomsday', 'Unrestricted', NULL, NULL, NULL, NULL
  UNION ALL SELECT 'Engineer', 'Unrestricted', NULL, 0.5, NULL, NULL
  UNION ALL SELECT 'Minefield', 'Unrestricted', NULL, NULL, NULL, NULL
  UNION ALL SELECT 'Minesweeper', 'Unrestricted', NULL, NULL, NULL, NULL
  UNION ALL SELECT 'Satellite', 'Unrestricted', NULL, NULL, NULL, NULL
  UNION ALL SELECT 'Stargate', 'Unrestricted', NULL, NULL, NULL, NULL
  UNION ALL SELECT 'Terraformer', 'Unrestricted', NULL, NULL, NULL, NULL
  UNION ALL SELECT 'Troopship', 'Unrestricted', NULL, NULL, NULL, NULL
  UNION ALL SELECT 'Carrier', 'Barred', NULL, 0.5, 50, 16
  UNION ALL SELECT 'Builder', 'Barred', NULL, NULL, 100, 32
  UNION ALL SELECT 'Morpher', 'Barred', NULL, 0.5, 75, 32
) t
WHERE s.game_type = 'sc3'
  AND EXISTS (
    SELECT 1 FROM game_ship_type_options o
    WHERE o.game_id = g.id AND o.ship_type = 'Jumpgate'
  )
  AND NOT EXISTS (
    SELECT 1 FROM game_ship_type_options o
    WHERE o.game_id = g.id AND o.ship_type IN ('Carrier', 'Builder', 'Morpher')
  )
  AND NOT EXISTS (
    SELECT 1 FROM game_ship_type_options o
    WHERE o.game_id = g.id AND o.ship_type = t.ship_type
  );
