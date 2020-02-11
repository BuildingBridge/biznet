<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class SaleCrmSiteMasterAjaxController
 */
class SaleCrmSiteMasterAjaxController extends Bitrix\Main\Engine\Controller
{
	/**
	 * @param $key
	 */
	public function updateLicenseKeyAction($key)
	{
		$key = strtoupper($key);
		if ($this->isLicenseKey($key))
		{
			$this->updateLicenseKey($key);
		}
	}

	/**
	 * @param $key
	 * @return false|int
	 */
	protected function isLicenseKey($key)
	{
		return preg_match(
			'/^([A-Z0-9]{3}-[A-Z0-9]{2}-[A-Z0-9]{12,16})$/',
			$key
		);
	}

	/**
	 * @param $key
	 */
	protected function updateLicenseKey($key)
	{
		if ($fp = fopen($_SERVER['DOCUMENT_ROOT']."/bitrix/license_key.php", "wb"))
		{
			fwrite($fp, '<'.'?$LICENSE_KEY = "'.\EscapePHPString($key).'";?'.'>');
			fclose($fp);
		}
		else
		{
			$this->errorCollection[] = new Bitrix\Main\Error(Loc::getMessage("SALE_CSM_LICENSE_FILE_WRITE_ERROR"));
		}
	}
}