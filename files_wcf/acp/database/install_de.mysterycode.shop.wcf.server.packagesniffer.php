<?php

use wcf\system\database\table\column\DefaultFalseBooleanDatabaseTableColumn;
use wcf\system\database\table\PartialDatabaseTable;

return [
	PartialDatabaseTable::create('shop1_storage')
		->columns([
			DefaultFalseBooleanDatabaseTableColumn::create('isAvailableForPackagesniffer'),
		]),
];
