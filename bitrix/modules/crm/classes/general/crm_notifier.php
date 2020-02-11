<?php

IncludeModuleLangFile(__FILE__);

class CCrmNotifier
{
	protected static $ERRORS = array();
	public static function Notify($addresseeID, $internalMessage, $externalMessage, $schemeTypeID, $tag = '')
	{
		self::ClearErrors();

		if(!(IsModuleInstalled('im') && CModule::IncludeModule('im')))
		{
			self::RegisterError('IM module is not installed.');
			return false;
		}

		if($addresseeID <= 0)
		{
			self::RegisterError('Addressee is not assigned.');
			return false;
		}

		$arMessage = array(
			'TO_USER_ID' => $addresseeID,
			'FROM_USER_ID' => 0,
			'NOTIFY_TYPE' => IM_NOTIFY_SYSTEM,
			'NOTIFY_MODULE' => 'crm',
			'NOTIFY_MESSAGE' => strval($internalMessage),
			'NOTIFY_MESSAGE_OUT' => strval($externalMessage)
		);

		$schemeTypeName = CCrmNotifierSchemeType::ResolveName($schemeTypeID);
		if($schemeTypeName !== '')
		{
			$arMessage['NOTIFY_EVENT'] = $schemeTypeName;
		}

		$tag = strval($tag);
		if($tag !== '')
		{
			$arMessage['NOTIFY_TAG'] = $tag;
		}

		$msgID = CIMNotify::Add($arMessage);
		if(!$msgID)
		{
			$e = $GLOBALS['APPLICATION']->GetException();
			$errorMessage = $e ? $e->GetString() : 'Unknown sending error. message not send.';

			self::RegisterError($errorMessage);
			return false;
		}

		return true;
	}
	protected static function RegisterError($msg)
	{
		$msg = strval($msg);
		if($msg !== '')
		{
			self::$ERRORS[] = $msg;
		}
	}
	private static function ClearErrors()
	{
		if(!empty(self::$ERRORS))
		{
			self::$ERRORS = array();
		}
	}
	public static function GetLastErrorMessage()
	{
		return ($c = count(self::$ERRORS)) > 0 ? self::$ERRORS[$c - 1] : '';
	}
	public static function GetErrorMessages()
	{
		return self::$ERRORS;
	}
	public static function GetErrorCount()
	{
		return count(self::$ERRORS);
	}
}

class CCrmNotifierSchemeType
{
	const Undefined = 0;
	const IncomingEmail = 1;
	const WebForm = 4;
	const Callback = 5;

	const IncomingEmailName = 'incoming_email';
	const WebFormName = 'webform';
	const CallbackName = 'callback';

	public static function ResolveName($typeID)
	{
		$typeID = intval($typeID);
		switch ($typeID)
		{
			case self::IncomingEmail:
				return self::IncomingEmailName;
			case self::WebForm:
				return self::WebFormName;
			case self::Callback:
				return self::WebFormName;
		}

		return '';
	}

	public static function PrepareNotificationSchemes()
	{
		return array(
			'crm' => array(
				'incoming_email' => array(
					'NAME' => GetMessage('CRM_NOTIFY_SCHEME_ACTIVITY_EMAIL_INCOMING'),
					'MAIL' => true,
					'XMPP' => true,
				),
				"post" => Array(
					"NAME" => GetMessage('CRM_NOTIFY_SCHEME_LIVEFEED_POST')
				),
				'mention' => array(
					'NAME' => GetMessage('CRM_NOTIFY_SCHEME_LIVEFEED_MENTION')
				),
				self::WebFormName => array(
					'NAME' => GetMessage('CRM_NOTIFY_SCHEME_WEBFORM'),
					"LIFETIME" => 86400*7
				),
				self::CallbackName => array(
					'NAME' => GetMessage('CRM_NOTIFY_SCHEME_CALLBACK'),
					"LIFETIME" => 86400*7
				)
			),
		);
	}
}
