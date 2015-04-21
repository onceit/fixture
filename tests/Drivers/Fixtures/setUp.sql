CREATE TABLE IF NOT EXISTS pirates (
  id INTEGER PRIMARY KEY,
  name TEXT,
  catchphrase TEXT,
  monkey_id INTEGER,
  created_at DATETIME,
  updated_at DATETIME
);

CREATE TABLE IF NOT EXISTS monkeys (
  id INTEGER PRIMARY KEY,
  name TEXT,
  created_at DATETIME,
  updated_at DATETIME
);

CREATE TABLE IF NOT EXISTS fruits (
  id INTEGER PRIMARY KEY,
  name TEXT,
  created_at DATETIME,
  updated_at DATETIME
);

CREATE TABLE IF NOT EXISTS fruits_monkeys (
  id INTEGER PRIMARY KEY,
  fruit_id INTEGER,
  monkey_id INTEGER,
  created_at DATETIME,
  updated_at DATETIME
);
