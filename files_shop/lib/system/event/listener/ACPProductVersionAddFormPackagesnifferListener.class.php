<?php

namespace shop\system\event\listener;

use shop\acp\form\AbstractStorageForm;
use shop\data\storage\Storage;
use shop\data\storage\StorageAction;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\WCF;

class ACPProductVersionAddFormPackagesnifferListener implements IParameterizedEventListener {
	/**
	 * @var boolean|null
	 */
	protected $packagesnifferAvailability;
	
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		/** @var AbstractStorageForm $eventObj */
		
		if ($eventName == 'readFormParameters') {
			if (isset($_POST['packagesnifferAvailability'])) {
				$this->packagesnifferAvailability = true;
			}
		}
		else if ($eventName == 'readData') {
			if (empty($_POST) && $eventObj->storageID) {
				$storage = new Storage($eventObj->storageID);
				$this->packagesnifferAvailability = (bool) $storage->isAvailableForPackagesniffer;
			}
		}
		else if ($eventName == 'save') {
			(new StorageAction([$eventObj->storageID], 'update', [
				'data' => [
					'isAvailableForPackagesniffer' => $this->packagesnifferAvailability ? 1 : 0
				]
			]))->executeAction();
		}
		else if ($eventName == 'assignVariables') {
			WCF::getTPL()->assign('packagesnifferAvailability', $this->packagesnifferAvailability);
		}
	}
}
