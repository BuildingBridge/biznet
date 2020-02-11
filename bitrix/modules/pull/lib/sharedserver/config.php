<?php

namespace Bitrix\Pull\SharedServer;

use Bitrix\Main\Config\Option;

class Config
{
	const DEFAULT_SERVER = "https://rtc-cloud-ms1.bitrix.info";
	const REGISTER_URL = "/register-client/";
	const PUB_URL = "/pub/";
	const SUB_URL = "/subws/";
	const REST_URL = "/rest/";

	const SERVER_ADDRESS = 'shared_server_address';
	const SHARED_SERVER_KEY = 'shared_server_key';
	const IS_REGISTERED_ON_SHARED_SERVER = 'registered_on_shared_server';

	public static function getServerAddress()
	{
		return Option::get("pull", static::SERVER_ADDRESS);
	}

	public static function setServerAddress($serverAddress)
	{
		Option::set("pull", static::SERVER_ADDRESS, $serverAddress);
	}

	public static function getRegisterUrl()
	{
		return static::getServerAddress() . (defined("PULL_SHARED_REGISTER_URL") ? PULL_SHARED_REGISTER_URL : static::REGISTER_URL);
	}

	/**
	 * Returns url for publishing events.
	 *
	 * @return string
	 */
	public static function getPublishUrl()
	{
		return static::getServerAddress() . static::PUB_URL;
	}

	/**
	 * Returns url for receiving events with long polling transport.
	 *
	 * @return string
	 */
	public static function getLongPollingUrl()
	{
		return static::getServerAddress() . static::SUB_URL;
	}

	/**
	 * Returns url for receiving events with websocket transport.
	 *
	 * @return string
	 */
	public static function getWebSocketUrl()
	{
		$result = static::getServerAddress() . static::SUB_URL;
		$result = str_replace(["https", "http"], ["wss", "ws"], $result);
		return $result;
	}

	/**
	 *
	 */
	public static function getWebPublishUrl()
	{
		return static::getServerAddress() . static::REST_URL;
	}

	public static function getServerVersion()
	{
		return 4;
	}

	public static function setSignatureKey($signatureKey)
	{
		Option::set("pull", static::SHARED_SERVER_KEY, $signatureKey);
	}

	public static function getSignatureKey()
	{
		return Option::get("pull", static::SHARED_SERVER_KEY);
	}

	public static function setRegistered($isRegistered)
	{
		Option::set("pull", static::IS_REGISTERED_ON_SHARED_SERVER, ($isRegistered ? "Y" : "N"));
	}

	public static function isRegistered()
	{
		return (Option::get("pull", static::IS_REGISTERED_ON_SHARED_SERVER) === "Y");
	}

	public static function selectServer()
	{
		// todo: actual selection of the server

		return defined("PULL_SHARED_SERVER") ? PULL_SHARED_SERVER : static::DEFAULT_SERVER;
	}
}