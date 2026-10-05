# Stellar Crisis PHP
This is the PHP implementation of the classic turn based strategy web game Stellar Crisis.  Stellar Crisis is the web's first complete multi-player strategy game. Players from all over the world compete to build megalithic galactic empires, develop powerful new technologies, and fight pitched battles in far away star systems. It is free, absolutely and positively. It also comes with ABSOLUTELY NO WARRANTY.

# Servers
Three stellar crisis servers remain.  Two servers run the php code.  The third runs Almonaster, a different implementation of the game.
http://homeserve.org/sc/sc.php
http://falcon.almonaster.net
https://sc.rocketempires.com/sc.php

# Code
In addition to the php software, there are two additional implementations of stellar crisis.
The original: https://sourceforge.net/projects/sc-original/
Almonaster: https://github.com/mfeingol/almonaster

There is a fourth implementation from the late 90s called MkII.  I believe it is lost to time.  If anyone has a copy, please reach out.  I would also love a copy of the original code written in perl, before it was converted to pike.  Again, if anyone has a copy, please reach out.

# History
Depending on who you ask, Stellar Crisis first appeared in either 1993 or 1995.  The earliest announcement I have been able to find on usenet is from 1995.  The php implementation originated in the early '00s, first appearing on the Iceberg server.  By the late 00s, the Iceberg server was defunct.  Homeserve was started as a replacement.  I acquired a copy of the php code in 2010.  I added a tournament manager, which I have been using to run an annual tournament since 2011.  In 2019, I upgraded the software to run on php7 and newer versions of mysql/mariadb.  This upgrade was substantially more complicated than I anticipated.  In 2021, I implemented Jumpgates (a shiptype introduced in stellar crisis 3 that had never been implemented in the php version of the game).

# Competitive Play
Stellar Crisis has a long history of competitive play.  Past competitions include Clan Wars, "Olympics," and the Stellar Kings Cup.  The Stellar Crisis Open Championship, began in 2011, continues to be run annually on the Rocket Empires server.  The tournament starts on the last Saturday in July, with registration opening a week before.

# Installation/Configuration
1. Preqrequisites: windows or linux, apache, mysql or mariadb, and php.
2. Create a database in mysql or mariadb to house the stellar crisis data.  Run the sc.sql script to create the tables and insert the initial data. (eg mysql -u user -p --database=stellarcrisis <sc.sql)
3. Copy the code into a directory that will be served by apache.  If on shared hosting, this is usually a "public_html" directory.  Optionally, the code may be put into a subfolder (eg publichtml/sc).
4. Configure the database connection information in serverconfig.php
5. Create an admin account with a hashed password. From a shell on the server, generate a hash and insert the row, for example:

```
php -r 'echo password_hash("your-password-here", PASSWORD_DEFAULT), "\n";'
```

Then (paste the hash into the INSERT):

```
INSERT INTO empires (name, last_ip, password, password_hash, is_admin, validation_info, comment, email, real_name)
VALUES ('admin', '', NULL, '$2y$10$...paste-hash-here...', '1', '', '', 'email', 'admin');
```

6. Delete the utility directory, or otherwise make sure it is not accessible to the general public.
7. If apache is running, you should now be able to navigate to sc.php, login, and create some series.  Have fun!

# Upgrade
These instructions are to upgrade a server that is running code from before the addition of tournaments.
Run the Following mysql script:
```    alter table games add game_type varchar(3) not null default 'sc2';
    alter table series add `game_type` varchar(3) NOT NULL DEFAULT 'sc2';
    insert into ship_types (type, mobile, version) values ('Jumpgate', '0', 'v3');
    alter table messages change type type
        ENUM('instant','motd','private','broadcast','team','update','scout','game_message');
        alter table history change event event
            ENUM('started','bridier','update','Truce','Trade','War','Alliance',
            'empire','unknown','ship to ship','ship to system','destroyed',
            'sighted','minefield','nuked','annihilated','invaded',
            'unsuccessfully invaded','colonized','terraformed','opened',
            'closed','joined','nuked out','invaded out','annihilated out',
            'ruins','surrender','draw','won','send','Shared HQ');
    create table if not exists series_ship_type_options (
        id int not null primary key auto_increment,
        series_id int(11) not null,
        ship_type varchar(20) not null,
        status varchar(12) not null,
        range_multiplier decimal(6,3),
        loss decimal(5,3),
        build_cost smallint,
        maintenance_cost smallint);
    create table if not exists game_ship_type_options (
        id int not null primary key auto_increment,
        game_id int(11) not null,
        ship_type varchar(20) not null,
        status varchar(12) not null,
        range_multiplier decimal(6,3),
        loss decimal(5,3),
        build_cost smallint,
        maintenance_cost smallint);
    create table if not exists tournament (
        id int(11) not null primary key auto_increment,
        starttime int(11) not null,
        name varchar(100) not null,
        description varchar(500) not null,
        series int(11) not null,
        completed tinyint(1) not null default 0
    );
```

# Upgrade (SC3 remaining features)
These instructions are to upgrade a server that already has Jumpgate / ship_type_options support to the remaining SC 3.0 ships and series options (Carrier, Builder, Morpher, and related series knobs).
Run the Following mysql script:
```
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

UPDATE series_ship_type_options SET status = 'Developed' WHERE status = 'Available';
UPDATE game_ship_type_options SET status = 'Developed' WHERE status = 'Available';

ALTER TABLE history
  MODIFY event ENUM(
    'started','bridier','update','Truce','Trade','War','Alliance',
    'empire','unknown','ship to ship','ship to system','destroyed',
    'sighted','minefield','nuked','annihilated','invaded',
    'unsucessfully invaded','colonized','terraformed','opened',
    'closed','joined','nuked out','invaded out','annihilated out',
    'ruins','surrender','draw','won','send','Shared HQ','created'
  ) NOT NULL DEFAULT 'unknown';
```

If the server already ran the SC3 remaining-features script above, also run `upgrade_sc3_jumpgate_series.sql`. That fills ship-type options for existing sc3 series that only have Jumpgate: classic ships stay developable (Attack/Science/Colony start Developed), Jumpgate is left as-is, and Carrier/Builder/Morpher are Barred. The script is included at the end of `upgrade_sc3.sql` for first-time upgrades and is safe to re-run.

# Upgrade (password hashing and session tokens)
These instructions are for an existing server that still stores empire passwords in plaintext and uses a hidden `pass` field for session continuity. Deploy the new PHP code and run:

```
mysql -u user -p --database=your_database < upgrade_password_security.sql
```

That script:

1. Changes the `empires` primary key from `(name, password)` to `(name)`
2. Makes `empires.password` nullable (legacy plaintext until each account upgrades)
3. Adds `empires.password_hash`
4. Creates the `auth_tokens` table (session tokens; soft-revoke via `revoked_at`, no hard deletes)

Behavior after the upgrade:

- Existing plaintext passwords keep working until each player next authenticates with their password
- On that success, the server writes `password_hash`, sets `password` to NULL, and issues a session token
- Authenticated pages use a hidden `token` field instead of echoing the password
- Old open tabs that still POST `pass` continue to work if the password verifies against the hash
- Token lifetime is up to one year; logout and password changes soft-revoke tokens

Design notes: `docs/design-password-security.md`.

Do not run this script twice on the same database without checking whether `password_hash` / `auth_tokens` already exist.

# License
Stellar Crisis PHP is available under The GPL v2.0
