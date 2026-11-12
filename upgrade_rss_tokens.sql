-- Per-empire RSS feed tokens (replace md5(id+name+join_date) IDOR).
-- Run against an existing Stellar Crisis database.
-- Existing empires receive a token lazily on next Game List visit (or run the UPDATE below).

ALTER TABLE empires
  ADD COLUMN rss_token char(64) NULL DEFAULT NULL AFTER validation_info;

ALTER TABLE empires
  ADD UNIQUE KEY rss_token (rss_token);
