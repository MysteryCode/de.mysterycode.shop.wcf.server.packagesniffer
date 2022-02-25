UPDATE shop1_storage SET isAvailableForPackagesniffer = 1 WHERE (productID IS NOT NULL OR optionID IS NOT NULL) AND customerID IS NULL AND isDisabled = 0;
