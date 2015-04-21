CREATE TABLE IF NOT EXISTS pirates (
  id INTEGER PRIMARY KEY,
  name TEXT,
  catchphrase TEXT,
  created_at DATETIME,
  updated_at DATETIME
);

CREATE TABLE IF NOT EXISTS parrots (
  id INTEGER PRIMARY KEY,
  name TEXT,
  pirate_id INTEGER,
  created_at DATETIME,
  updated_at DATETIME
);
