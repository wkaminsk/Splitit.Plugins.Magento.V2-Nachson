<?php
namespace Splitit\Paymentmethod\Model\Source;

class Getsplititsupportedcultures {
	/**
	 * Get options for culture
	 *
	 * @return array
	 */
	public function toOptionArray() {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$api = $objectManager->get('Splitit\Paymentmethod\Model\Api');
		$apiUrl = $api->getApiUrl();
		$getSplititSupportedCultures = $api->getSplititSupportedCultures($apiUrl . "api/Infrastructure/SupportedCultures");
		$decodedResult = json_decode($getSplititSupportedCultures, true);
		$allCulture = array();
		if (isset($decodedResult["ResponseHeader"]["Succeeded"]) && $decodedResult["ResponseHeader"]["Succeeded"] == 1 && count($decodedResult["Cultures"])) {
			foreach ($decodedResult["Cultures"] as $key => $value) {
				$allCulture[] = array('value' => $value["CultureName"], 'label' => $value["DisplayName"]);
			}
		}

		return $allCulture;

	}
}