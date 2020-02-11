<?php

namespace Bitrix\Pull\SharedServer;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

class Client
{
	const TYPE_CP = "CP";

	public static function register()
	{
		$result = new Result();
		$serverAddress = Config::selectServer();
		Config::setServerAddress($serverAddress);
		$registerResult = static::performRegister();

		if(!$registerResult->isSuccess())
		{
			Config::setRegistered(false);
			return $result->addErrors($registerResult->getErrors());
		}

		$registrationData = $registerResult->getData();

		Config::setSignatureKey($registrationData["securityKey"]);
		Config::setRegistered(true);

		return $result;
	}

	protected static function performRegister()
	{
		$result = new Result();
		$params = [
			"BX_LICENCE" => static::getPublicLicenseCode(),
			"BX_TYPE" => static::TYPE_CP,
		];
		$params["BX_HASH"] = static::signRequest($params);

		$request = [
			"verificationQuery" => http_build_query($params)
		];

		$httpClient = new HttpClient([
			"disableSslVerification" => true
		]);
		$queryResult = $httpClient->query(HttpClient::HTTP_POST, Config::getRegisterUrl(), $request);

		if(!$queryResult)
		{
			$errors = $httpClient->getError();
			foreach ($errors as $code => $message)
			{
				$result->addError(new Error($message, $code));
			}
			return $result;
		}
		$returnCode = $httpClient->getStatus();
		if($returnCode != 200)
		{
			$response = $httpClient->getResult();
			try
			{
				$parsedResponse = Json::decode($response);

				$result->addError(new Error($parsedResponse["error"]));
			}
			catch (\Exception $e)
			{
				$result->addError(new Error("Server returned " . $returnCode . " code", "WRONG_SERVER_RESPONSE"));
			}

			return $result;
		}

		$response = $httpClient->getResult();
		if($response == "")
		{
			$result->addError(new Error("Empty server response", "EMPTY_SERVER_RESPONSE"));
			return $result;
		}

		try
		{
			$parsedResponse = Json::decode($response);
		}
		catch (\Exception $e)
		{
			$result->addError(new Error("Could not parse server response. Raw response: " . $response));
			return $result;
		}

		if($parsedResponse["status"] === "error")
		{
			$result->addError(new Error($parsedResponse["error"]));
		}
		else
		{
			$result->setData($parsedResponse);
		}

		return $result;
	}

	protected static function getLicenseKey()
	{
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client.php");
		return \CUpdateClient::GetLicenseKey();
	}

	public static function getPublicLicenseCode()
	{
		return md5("BITRIX" . static::getLicenseKey() . "LICENCE");
	}

	protected static function signRequest(array $request)
	{
		$paramStr = md5(implode("|", $request));
		return md5($paramStr . md5(static::getLicenseKey()));
	}
}