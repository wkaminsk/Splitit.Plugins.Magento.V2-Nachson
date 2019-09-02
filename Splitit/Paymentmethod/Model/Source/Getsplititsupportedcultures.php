<?php
namespace Splitit\Paymentmethod\Model\Source;

class Getsplititsupportedcultures {

	public $helper;
	/**
	 * @var \Magento\Framework\HTTP\Client\Curl
	 */
	protected $curl;

	/**
	 * Constructor
	 */
	public function __construct(
		\Magento\Framework\App\Helper\AbstractHelper $helper,
		\Magento\Framework\HTTP\Client\Curl $curl
	) {
		$this->helper = $helper;
		$this->curl = $curl;
	}

	/**
	 * Get options for culture
	 *
	 * @return array
	 */
	public function toOptionArray() {
		$apiUrl = $this->getApiUrl();
		$getSplititSupportedCultures = $this->getSplititSupportedCultures($apiUrl . "api/Infrastructure/SupportedCultures");
		$decodedResult = json_decode($getSplititSupportedCultures, true);
		$allCulture = array();
		if (isset($decodedResult["ResponseHeader"]["Succeeded"]) && $decodedResult["ResponseHeader"]["Succeeded"] == 1 && count($decodedResult["Cultures"])) {
			foreach ($decodedResult["Cultures"] as $key => $value) {
				$allCulture[] = array('value' => $value["CultureName"], 'label' => $value["DisplayName"]);
			}
		}

		return $allCulture;
	}

	/**
	 * Get supported culture from Splitit
	 * @param approvalUrl string
	 * @return json
	 */
	public function getSplititSupportedCultures($approvalUrl) {
		$url = $approvalUrl . '?format=json';
		try {
			$this->curl->setOption(CURLOPT_FOLLOWLOCATION, 1);
			$this->curl->setOption(CURLOPT_SSL_VERIFYPEER, 0);
			$this->curl->get($url);
			$result = $this->curl->getBody();

		} catch (\Exception $e) {
			$result["errorMsg"] = $this->getServerDownMsg();
			$result = json_encode($result);
		}
		return $result;
	}

	/**
	 * Get Api url
	 *
	 * @return array
	 */
	public function getApiUrl() {

		if ($this->getConfig("payment/splitit_paymentmethod/sandbox_flag")) {
			return $this->getConfig("payment/splitit_paymentmethod/api_url_sandbox");
		}
		return $this->getConfig("payment/splitit_paymentmethod/api_url");
	}

	/**
	 * To get the config value
	 * @return string
	 */
	public function getConfig($config_path) {
		return $this->helper->scopeConfig->getValue(
			$config_path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	/**
	 * Set server down message
	 * @return string
	 */
	public function getServerDownMsg() {
		return "Failed to connect to splitit payment server. Please retry again later.";
	}
}