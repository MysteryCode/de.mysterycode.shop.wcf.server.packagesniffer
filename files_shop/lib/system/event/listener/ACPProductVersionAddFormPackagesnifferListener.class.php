<?php

namespace shop\system\event\listener;

use shop\acp\form\AbstractStorageForm;
use wcf\system\event\listener\AbstractEventListener;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\BooleanFormField;

class ACPProductVersionAddFormPackagesnifferListener extends AbstractEventListener {
	/**
	 * @param    AbstractStorageForm    $eventObj
	 * @param    array                  $parameters
	 */
	public function onCreateForm(AbstractStorageForm $eventObj, array &$parameters) : void {
		/** @var FormContainer $fileContainer */
		$fileContainer = $eventObj->form->getNodeById('file');
		
		$fileContainer->appendChild(
			BooleanFormField::create('isAvailableForPackagesniffer')
				->label('shop.acp.product.version.packagesniffer.availability')
				->available()
		);
	}
}
