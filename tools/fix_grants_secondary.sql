-- Run these statements in phpMyAdmin (SQL tab) or mysql client as root
-- Replace PASSWORD_HERE with the exact password from c:\dev\htdocs\checklist\.env

CREATE USER IF NOT EXISTS 'travelapp'@'localhost' IDENTIFIED BY 'Vdc123$%^&*()';
CREATE USER IF NOT EXISTS 'travelapp'@'127.0.0.1' IDENTIFIED BY 'Vdc123$%^&*()';

ALTER USER 'travelapp'@'localhost' IDENTIFIED BY 'Vdc123$%^&*()';
ALTER USER 'travelapp'@'127.0.0.1' IDENTIFIED BY 'Vdc123$%^&*()';

GRANT ALL PRIVILEGES ON `travelapp`.* TO 'travelapp'@'localhost';
GRANT ALL PRIVILEGES ON `travelapp`.* TO 'travelapp'@'127.0.0.1';

FLUSH PRIVILEGES;

-- Verify grants
SHOW GRANTS FOR 'travelappvdcapp_1'@'localhost';
SHOW GRANTS FOR 'travelappvdcapp_1'@'127.0.0.1';
