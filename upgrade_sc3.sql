-- SC3 remaining features upgrade
-- Run against an existing database that already has Jumpgate / ship_type_options.

INSERT IGNORE INTO ship_types (type, mobile, version) VALUES
('Carrier', '1', 'v3'),
('Builder', '1', 'v3'),
('Morpher', '1', 'v3');

ALTER TABLE ships
  ADD COLUMN morpher enum('1','0') NOT NULL DEFAULT '0' AFTER type;

ALTER TABLE players
  ADD COLUMN restricted_techs smallint(6) NOT NULL DEFAULT '0' AFTER traded_in;

ALTER TABLE series
  ADD COLUMN max_ag_ratio float NOT NULL DEFAULT 100 AFTER weekend_updates,
  ADD COLUMN max_ships smallint(6) NOT NULL DEFAULT 0,
  ADD COLUMN initial_tech float NOT NULL DEFAULT 1.0,
  ADD COLUMN hw_ag smallint(6) NOT NULL DEFAULT 100,
  ADD COLUMN hw_min smallint(6) NOT NULL DEFAULT 100,
  ADD COLUMN hw_fuel smallint(6) NOT NULL DEFAULT 100,
  ADD COLUMN min_pop_to_build smallint(6) NOT NULL DEFAULT 50,
  ADD COLUMN planet_create_cost smallint(6) NOT NULL DEFAULT 90,
  ADD COLUMN max_planets int(11) NOT NULL DEFAULT 1000,
  ADD COLUMN max_restricted_techs smallint(6) NOT NULL DEFAULT 0,
  ADD COLUMN max_trade_ins smallint(6) NOT NULL DEFAULT 0,
  ADD COLUMN max_allies tinyint(3) unsigned DEFAULT NULL;

-- Legacy Jumpgate "Available" means Developed
UPDATE series_ship_type_options SET status = 'Developed' WHERE status = 'Available';
UPDATE game_ship_type_options SET status = 'Developed' WHERE status = 'Available';

-- Builder create events in game history / end-of-game movie
ALTER TABLE history
  MODIFY event ENUM(
    'started','bridier','update','Truce','Trade','War','Alliance',
    'empire','unknown','ship to ship','ship to system','destroyed',
    'sighted','minefield','nuked','annihilated','invaded',
    'unsucessfully invaded','colonized','terraformed','opened',
    'closed','joined','nuked out','invaded out','annihilated out',
    'ruins','surrender','draw','won','send','Shared HQ','created'
  ) NOT NULL DEFAULT 'unknown';

-- Existing Jumpgate-only sc3 series: keep Jumpgate, add classic techs, bar other v3 ships.
-- Idempotent; also in upgrade_sc3_jumpgate_series.sql for servers that already ran the ALTERs above.
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
