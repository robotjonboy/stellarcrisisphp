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
