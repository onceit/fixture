PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS pirates (
  id INTEGER PRIMARY KEY,
  name TEXT,
  title TEXT,
  created_at DATETIME,
  updated_at DATETIME
);

CREATE TABLE IF NOT EXISTS catchphrases_pirates (
  catchphrase_id INTEGER,
  pirate_id INTEGER,
  position INTEGER,
  created_at DATETIME,
  updated_at DATETIME,
  FOREIGN KEY(catchphrase_id) REFERENCES catchphrases(id),
  FOREIGN KEY(pirate_id) REFERENCES pirates(id)
);

CREATE TABLE IF NOT EXISTS catchphrases (
  id INTEGER PRIMARY KEY,
  phrase TEXT
);

CREATE TABLE IF NOT EXISTS parrots (
  id INTEGER PRIMARY KEY,
  name TEXT,
  pirate_id INTEGER,
  FOREIGN KEY(pirate_id) REFERENCES pirates(id)
);

CREATE TABLE IF NOT EXISTS crew (
  id INTEGER PRIMARY KEY,
  name TEXT,
  role TEXT,
  boat_id INTEGER,
  FOREIGN KEY(boat_id) REFERENCES boats(id)
);

CREATE TABLE IF NOT EXISTS boats (
  id INTEGER PRIMARY KEY,
  name TEXT
);
