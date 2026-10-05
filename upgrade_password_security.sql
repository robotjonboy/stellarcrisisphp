-- Password hashing and session tokens
-- Run against an existing Stellar Crisis database.

ALTER TABLE empires DROP PRIMARY KEY;
ALTER TABLE empires ADD PRIMARY KEY (name);
ALTER TABLE empires MODIFY password varchar(20) NULL DEFAULT NULL;
ALTER TABLE empires ADD COLUMN password_hash varchar(255) NULL DEFAULT NULL AFTER password;

CREATE TABLE IF NOT EXISTS auth_tokens (
  id int(11) NOT NULL AUTO_INCREMENT,
  empire_id int(11) NOT NULL,
  token_hash char(64) NOT NULL,
  created_at int(11) NOT NULL,
  expires_at int(11) NOT NULL,
  last_used_at int(11) DEFAULT NULL,
  revoked_at int(11) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY token_hash (token_hash),
  KEY empire_id (empire_id),
  KEY expires_at (expires_at)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
