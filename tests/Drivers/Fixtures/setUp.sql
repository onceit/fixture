CREATE TABLE IF NOT EXISTS pirates (
  id INTEGER PRIMARY KEY,
  name TEXT,
  title TEXT,
  catchphrase TEXT,
  created_at DATETIME,
  updated_at DATETIME
);

CREATE TABLE IF NOT EXISTS catchphrases_pirates (
  catchphrase_id INTEGER,
  pirate_id INTEGER,
  created_at DATETIME,
  updated_at DATETIME
);

CREATE TABLE IF NOT EXISTS catchphrases (
  id INTEGER PRIMARY KEY,
  name TEXT,
  emotion TEXT,
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

CREATE TABLE IF NOT EXISTS crew (
  id INTEGER PRIMARY KEY,
  name TEXT,
  role TEXT,
  boat_id INTEGER,
  created_at DATETIME,
  updated_at DATETIME
);

CREATE TABLE IF NOT EXISTS boats (
  id INTEGER PRIMARY KEY,
  name TEXT,
  created_at DATETIME,
  updated_at DATETIME
);
