--
-- Creates a user labtrove with password labtrove
-- Then creates a db called labtrove
--

CREATE USER 'labtrove'@'localhost' IDENTIFIED BY  'labtrove';
CREATE DATABASE  `labtrove` ;
GRANT SELECT , INSERT , UPDATE , DELETE ON  `labtrove` . * TO  'labtrove'@'localhost';
FLUSH PRIVILEGES;
