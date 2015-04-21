PRAGMA foreign_keys = OFF;

DELETE FROM pirates;
DELETE FROM catchphrases_pirates;
DELETE FROM catchphrases;
DELETE FROM parrots;
DELETE FROM crew;
DELETE FROM boats;

PRAGMA foreign_keys = ON;
