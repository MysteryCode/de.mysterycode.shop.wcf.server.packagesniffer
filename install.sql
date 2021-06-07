ALTER TABLE shop1_storage ADD COLUMN isAvailableForPackagesniffer TINYINT(1) NOT NULL DEFAULT 0;

UPDATE shop1_storage SET isAvailableForPackagesniffer = 1 WHERE (productID IS NOT NULL OR optionID IS NOT NULL) AND customerID IS NULL AND isActive = 1;
