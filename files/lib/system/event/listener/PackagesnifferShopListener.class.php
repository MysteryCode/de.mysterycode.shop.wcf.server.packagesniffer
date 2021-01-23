<?php

namespace shop\system\event\listener;

use shop\data\storage\StorageList;
use shop\data\wcf\package\WCFPackageList;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\WCF;
use wcf\util\StringUtil;
use wcf\util\UserUtil;
use wcf\util\XMLWriter;

/**
 * Class PackagesnifferShopListener
 *
 * @package	shop\system\event\listener
 * @author	Florian Gail
 * @copyright	2018-2021 Florian Gail <https://www.mysterycode.de>
 * @license	Kostenlose Produkte <https://www.mysterycode.de/licenses/kostenlose-plugins/>
 */
class PackagesnifferShopListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		/** @var \shop\page\UpdatePage $eventObj */
		
		// this script should never allow real downloads!
		// that's why we return in case the page should send the download
		if (isset($_POST['packageName']) || isset($_POST['packageVersion']) || $eventObj->customer !== null) return;
		
		// return also in case the request was not triggered by the packagesniffer
		$ipv6 = UserUtil::getIpAddress();
		$ipv4 = UserUtil::convertIPv6To4($ipv6);
		$whitelist = explode("\n", StringUtil::unifyNewlines(SHOP_SERVER_IP_WHITELIST));
		if (!in_array($ipv4, $whitelist) && !in_array($ipv6, $whitelist)) return;
		
		// read every active storage that belongs to a product or option
		// skips customer related storages
		$storageList = new StorageList();
		$storageList->readObjectIDs();
		$storageList->getConditionBuilder()->add('(productID IS NOT NULL OR optionID IS NOT NULL)');
		$storageList->getConditionBuilder()->add('customerID IS NULL');
		$storageList->getConditionBuilder()->add('isActive = 1');
		
		// wrap storages into package list with packages-information
		$packageList = new WCFPackageList();
		if (!empty($storageList->get)) {
			$packageList->setVersionLoading(true);
			$packageList->setPackageServerID($eventObj->server->serverID);
			$packageList->setStorageIDs($storageList->getObjectIDs());
		} else {
			$packageList->getConditionBuilder()->add("1=0");
		}
		$packageList->readObjects();
		
		// show nice XML output generated live by PHP
		// dancing is fun!
		echo $this->generateXML($packageList);
		
		// cleanup session from user online list
		WCF::getSession()->delete();
		exit;
	}
	
	/**
	 * Generates the XML tree based on the given package liste
	 *
	 * @param WCFPackageList $packageList
	 * @return string
	 */
	protected function generateXML(WCFPackageList $packageList) {
		$xmlWriter = new XMLWriter();
		$xmlWriter->beginDocument('section', 'http://www.woltlab.com', 'https://www.woltlab.com/XSD/hurricane/packageUpdateServer.xsd', ['name' => 'packages']);
		
		foreach ($packageList as $package) {
			/** @var $package \shop\data\wcf\package\WCFPackage **/
			
			$xmlWriter->startElement('package', ['name' =>  $package->package]);
			
			$xmlWriter->startElement('packageinformation');
			$xmlWriter->writeElement('packagename', $package->packageName);
			$xmlWriter->writeElement('packagedescription', $package->packageDescription);
			
			if ($package->isApplication) {
				$xmlWriter->writeElement('isapplication', 1);
				$xmlWriter->writeElement('standalone', 1);
			}
			
			$xmlWriter->endElement();
			
			if ($package->author) {
				$xmlWriter->startElement('authorinformation');
				$xmlWriter->writeElement('author', $package->author);
				if ($package->authorUrl) {
					$xmlWriter->writeElement('authorurl', $package->authorUrl);
				}
				$xmlWriter->endElement();
			}
			
			$versions = $package->getVersions();
			if (!empty($versions)) {
				$xmlWriter->startElement('versions');
				
				foreach ($versions as $version) {
					/** @var \shop\data\wcf\package\version\WCFPackageVersion $version */
					
					$xmlWriter->startElement('version', [
						'name' => $version->name, //TODO check
						'accessible' => 'true'
					]);
					
					$xmlWriter->writeElement('updatetype', $version->updateType);
					$xmlWriter->writeElement('timestamp', $version->timestamp);
					$xmlWriter->writeElement('versiontype', $version->versionType);
					if (!empty($version->licenseUrl))
						$xmlWriter->writeElement('license', $version->license, ['url' => $version->licenseUrl]);
					else
						$xmlWriter->writeElement('license', $version->license);
					
					$requirements = $version->getRequiredPackages();
					if (!empty($requirements)) {
						$xmlWriter->startElement('requiredpackages');
						foreach ($requirements as $requirement) {
							if (!empty($requirement['minversion']))
								$xmlWriter->writeElement('requiredpackage', $requirement['name'], ['minversion' => $requirement['minversion']]);
							else
								$xmlWriter->writeElement('requiredpackage', $requirement['name']);
						}
						$xmlWriter->endElement();
					}
					
					$excludes = $version->getExcludedPackages();
					if (!empty($excludes)) {
						$xmlWriter->startElement('excludedpackages');
						foreach ($excludes as $exclude) {
							if (!empty($exclude['version']))
								$xmlWriter->writeElement('excludedpackage', $exclude['name'], ['version' => $exclude['version']]);
							else
								$xmlWriter->writeElement('excludedpackage', $exclude['name']);
						}
						$xmlWriter->endElement();
					}
					
					$optionals = $version->getOptionalPackages();
					if (!empty($optionals)) {
						$xmlWriter->startElement('optionalpackages');
						foreach ($optionals as $optional) {
							$xmlWriter->writeElement('optionalpackage', $optional['name']);
						}
						$xmlWriter->endElement();
					}
					
					$updates = explode(';', $version->fromVersions);
					if (!empty($updates)) {
						$xmlWriter->startElement('fromversions');
						foreach ($updates as $update) {
							$xmlWriter->writeElement('fromversion', $update);
						}
						$xmlWriter->endElement();
					}
					
					$xmlWriter->endElement();
				}
				
				$xmlWriter->endElement();
			}
			
			$xmlWriter->endElement();
		}
		
		return $xmlWriter->endDocument();
	}
}
