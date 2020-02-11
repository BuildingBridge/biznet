<?php
namespace Bitrix\ImOpenLines\Model;

use \Bitrix\Main,
	\Bitrix\Main\Type\DateTime,
	\Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Entity\Validator,
	\Bitrix\Main\ORM\Fields\StringField,
	\Bitrix\Main\ORM\Fields\IntegerField;

use \Bitrix\ImOpenLines\Config;

Loc::loadMessages(__FILE__);

/**
 * Class ConfigTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ACTIVE bool optional default 'Y'
 * <li> LINE_NAME string(255) optional
 * <li> CRM bool optional default 'Y'
 * <li> CRM_FORWARD bool optional default 'Y'
 * <li> CRM_TRANSFER_CHANGE bool optional default 'Y'
 * <li> CRM_CREATE string(50) optional default 'none'
 * <li> CRM_SOURCE string(50) optional default 'create'
 * <li> QUEUE_TIME int optional
 * <li> NO_ANSWER_TIME int optional
 * <li> QUEUE_TYPE string(50) optional default 'evenly'
 * <li> TIMEMAN bool optional default 'N'
 * <li> CHECKING_OFFLINE bool optional default 'N'
 * <li> NO_ANSWER_RULE string(50) optional default 'none'
 * <li> NO_ANSWER_FORM_ID int optional
 * <li> NO_ANSWER_BOT_ID int optional
 * <li> NO_ANSWER_TEXT string optional
 * <li> WORKTIME_ENABLE bool optional default 'N'
 * <li> WORKTIME_FROM string(5) optional
 * <li> WORKTIME_TO string(5) optional
 * <li> WORKTIME_TIMEZONE string(50) optional
 * <li> WORKTIME_HOLIDAYS string(2000) optional
 * <li> WORKTIME_DAYOFF string(20) optional
 * <li> WORKTIME_DAYOFF_RULE string(50) optional default 'form'
 * <li> WORKTIME_DAYOFF_FORM_ID int optional
 * <li> WORKTIME_DAYOFF_BOT_ID int optional
 * <li> WORKTIME_DAYOFF_TEXT string optional
 * <li> CLOSE_RULE string(50) optional default 'form'
 * <li> CLOSE_FORM_ID int optional
 * <li> CLOSE_BOT_ID int optional
 * <li> CLOSE_TEXT string optional
 * <li> AUTO_CLOSE_RULE string(50) optional default 'none'
 * <li> AUTO_CLOSE_FORM_ID int optional
 * <li> AUTO_CLOSE_BOT_ID int optional
 * <li> AUTO_CLOSE_TIME int optional
 * <li> AUTO_CLOSE_TEXT string optional
 * <li> AUTO_EXPIRE_TIME int optional
 * <li> TEMPORARY bool optional default 'Y'
 * <li> QUICK_ANSWERS_IBLOCK_ID int optional
 * <li> TYPE_MAX_CHAT string(50) optional
 * <li> MAX_CHAT int optional
 * </ul>
 *
 * @package Bitrix\Imopenlines
 **/

class ConfigTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_imopenlines_config';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 * @throws Main\SystemException
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('CONFIG_ENTITY_ID_FIELD'),
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('CONFIG_ENTITY_CRM_FIELD'),
				'default_value' => 'Y',
			),
			new StringField('LINE_NAME', [
				'validation' => [__CLASS__, 'validateText255'],
				'title' => Loc::getMessage('CONFIG_ENTITY_LINE_NAME_FIELD'),
			]),
			'CRM' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('CONFIG_ENTITY_CRM_FIELD'),
				'default_value' => 'Y',
			),
			new StringField('CRM_CREATE', [
				'validation' => [__CLASS__, 'validateText50'],
				'title' => Loc::getMessage('CONFIG_ENTITY_CRM_CREATE_FIELD'),
				'default_value' => 'lead',
			]),
			'CRM_FORWARD' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('CONFIG_ENTITY_CRM_FORWARD_FIELD'),
				'default_value' => 'Y',
			),
			'CRM_TRANSFER_CHANGE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('CONFIG_ENTITY_CRM_TRANSFER_CHANGE_FIELD'),
				'default_value' => 'Y',
			),
			new StringField('CRM_SOURCE', [
				'validation' => [__CLASS__, 'validateText50'],
				'title' => Loc::getMessage('CONFIG_ENTITY_CRM_SOURCE_FIELD'),
				'default_value' => 'create',
			]),
			'QUEUE_TIME' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('CONFIG_ENTITY_QUEUE_TIME_FIELD_NEW'),
				'default_value' => '60',
			),
			'NO_ANSWER_TIME' => [
				'data_type' => 'integer',
				'title' => Loc::getMessage('CONFIG_ENTITY_NO_ANSWER_TIME_FIELD'),
				'default_value' => '60',
			],
			new StringField('QUEUE_TYPE', [
				'validation' => [__CLASS__, 'validateText50'],
				'title' => Loc::getMessage('CONFIG_ENTITY_QUEUE_TYPE_FIELD'),
				'default_value' => 'all',
			]),
			'TIMEMAN' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('CONFIG_ENTITY_TIMEMAN_FIELD'),
				'default_value' => 'N',
			),
			'CHECKING_OFFLINE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('CONFIG_ENTITY_CHECKING_OFFLINE_FIELD_NEW'),
				'default_value' => 'N',
			),
			'CHECK_ONLINE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('CONFIG_ENTITY_CHECK_ONLINE_FIELD_NEW'),
				'default_value' => 'Y',
			),
			'WELCOME_BOT_ENABLE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('CONFIG_ENTITY_WELCOME_BOT_ENABLE_FIELD'),
				'default_value' => 'N',
			),
			'WELCOME_MESSAGE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('CONFIG_ENTITY_WELCOME_MESSAGE_FIELD'),
				'default_value' => 'Y',
			),
			'WELCOME_MESSAGE_TEXT' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('CONFIG_ENTITY_WELCOME_MESSAGE_TEXT_FIELD_NEW'),
			),
			'VOTE_MESSAGE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('CONFIG_ENTITY_VOTE_MESSAGE_FIELD'),
				'default_value' => 'Y',
			),
			'VOTE_CLOSING_DELAY' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('CONFIG_ENTITY_VOTE_CLOSING_DELAY_FIELD_NEW'),
				'default_value' => 'N',
			),
			'VOTE_MESSAGE_1_TEXT' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('CONFIG_ENTITY_VOTE_MESSAGE_1_TEXT_FIELD'),
			),
			'VOTE_MESSAGE_1_LIKE' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('CONFIG_ENTITY_VOTE_MESSAGE_1_LIKE_FIELD'),
			),
			'VOTE_MESSAGE_1_DISLIKE' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('CONFIG_ENTITY_VOTE_MESSAGE_1_DISLIKE_FIELD'),
			),
			'VOTE_MESSAGE_2_TEXT' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('CONFIG_ENTITY_VOTE_MESSAGE_2_TEXT_FIELD'),
			),
			'VOTE_MESSAGE_2_LIKE' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('CONFIG_ENTITY_VOTE_MESSAGE_2_LIKE_FIELD'),
			),
			'VOTE_MESSAGE_2_DISLIKE' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('CONFIG_ENTITY_VOTE_MESSAGE_2_DISLIKE_FIELD'),
			),
			'AGREEMENT_MESSAGE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'N',
			),
			'AGREEMENT_ID' => array(
				'data_type' => 'integer',
				'default_value' => '0',
			),
			'CATEGORY_ENABLE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('CONFIG_ENTITY_CATEGORY_ENABLE_FIELD'),
				'default_value' => 'N',
			),
			'CATEGORY_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('CONFIG_ENTITY_CATEGORY_ID_FIELD'),
				'default_value' => '0',
			),
			new StringField('WELCOME_BOT_JOIN', [
				'validation' => [__CLASS__, 'validateText50'],
				'title' => Loc::getMessage('CONFIG_ENTITY_WELCOME_BOT_JOIN_FIELD'),
				'default_value' => 'first',
			]),
			'WELCOME_BOT_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('CONFIG_ENTITY_BOT_ID_FIELD'),
				'default_value' => '0',
			),
			'WELCOME_BOT_TIME' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('CONFIG_ENTITY_BOT_TIME_FIELD'),
				'default_value' => '600',
			),
			new StringField('WELCOME_BOT_LEFT', [
				'validation' => [__CLASS__, 'validateText50'],
				'title' => Loc::getMessage('CONFIG_ENTITY_WELCOME_BOT_LEFT_FIELD'),
				'default_value' => 'queue',
			]),
			new StringField('NO_ANSWER_RULE', [
				'validation' => [__CLASS__, 'validateText50'],
				'title' => Loc::getMessage('CONFIG_ENTITY_NO_ANSWER_RULE_FIELD'),
				'default_value' => Config::RULE_NONE,
				'fetch_data_modification' => function ()
				{
					return [
						function ($value)
						{
							if(Config::RULE_FORM == $value || Config::RULE_QUEUE == $value)
							{
								$value = Config::RULE_NONE;
							}

							return $value;
						}
					];
				}
			]),
			'NO_ANSWER_FORM_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('CONFIG_ENTITY_NO_ANSWER_FORM_ID_FIELD'),
				'default_value' => '0',
			),
			'NO_ANSWER_BOT_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('CONFIG_ENTITY_NO_ANSWER_BOT_ID_FIELD'),
				'default_value' => '0',
			),
			'NO_ANSWER_TEXT' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('CONFIG_ENTITY_NO_ANSWER_TEXT_FIELD'),
			),
			'WORKTIME_ENABLE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('CONFIG_ENTITY_WORKTIME_ENABLE_FIELD'),
				'default_value' => 'N',
			),
			new StringField('WORKTIME_FROM', [
				'validation' => [__CLASS__, 'validateText5'],
				'title' => Loc::getMessage('CONFIG_ENTITY_WORKTIME_FROM_FIELD'),
				'default_value' => '9',
			]),
			new StringField('WORKTIME_TO', [
				'validation' => [__CLASS__, 'validateText5'],
				'title' => Loc::getMessage('CONFIG_ENTITY_WORKTIME_TO_FIELD'),
				'default_value' => '18.30',
			]),
			new StringField('WORKTIME_TIMEZONE', [
				'validation' => [__CLASS__, 'validateText50'],
				'title' => Loc::getMessage('CONFIG_ENTITY_WORKTIME_TIMEZONE_FIELD'),
			]),
			new StringField('WORKTIME_HOLIDAYS', [
				'validation' => [__CLASS__, 'validateText2000'],
				'title' => Loc::getMessage('CONFIG_ENTITY_WORKTIME_HOLIDAYS_FIELD'),
			]),
			new StringField('WORKTIME_DAYOFF', [
				'validation' => [__CLASS__, 'validateText20'],
				'title' => Loc::getMessage('CONFIG_ENTITY_WORKTIME_DAYOFF_FIELD'),
			]),
			new StringField('WORKTIME_DAYOFF_RULE', [
				'validation' => [__CLASS__, 'validateText50'],
				'title' => Loc::getMessage('CONFIG_ENTITY_WORKTIME_DAYOFF_RULE_FIELD'),
				'default_value' => 'form',
			]),
			'WORKTIME_DAYOFF_FORM_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('CONFIG_ENTITY_WORKTIME_DAYOFF_FORM_ID_FIELD'),
				'default_value' => '0',
			),
			'WORKTIME_DAYOFF_BOT_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('CONFIG_ENTITY_WORKTIME_DAYOFF_BOT_ID_FIELD'),
				'default_value' => '0',
			),
			'WORKTIME_DAYOFF_TEXT' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('CONFIG_ENTITY_WORKTIME_DAYOFF_TEXT_FIELD'),
			),
			new StringField('CLOSE_RULE', [
				'validation' => [__CLASS__, 'validateText50'],
				'title' => Loc::getMessage('CONFIG_ENTITY_CLOSE_RULE_FIELD'),
				'default_value' => 'text',
			]),
			'CLOSE_FORM_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('CONFIG_ENTITY_CLOSE_FORM_ID_FIELD'),
				'default_value' => '0',
			),
			'CLOSE_BOT_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('CONFIG_ENTITY_CLOSE_BOT_ID_FIELD'),
				'default_value' => '0',
			),
			'CLOSE_TEXT' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('CONFIG_ENTITY_CLOSE_TEXT_FIELD'),
			),
			'FULL_CLOSE_TIME' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('CONFIG_ENTITY_FULL_CLOSE_TIME_FIELD'),
				'default_value' => '10',
			),
			new StringField('AUTO_CLOSE_RULE', [
				'validation' => [__CLASS__, 'validateText50'],
				'title' => Loc::getMessage('CONFIG_ENTITY_AUTO_CLOSE_RULE_FIELD'),
				'default_value' => 'none',
			]),
			'AUTO_CLOSE_FORM_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('CONFIG_ENTITY_AUTO_CLOSE_FORM_ID_FIELD'),
				'default_value' => '0',
			),
			'AUTO_CLOSE_BOT_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('CONFIG_ENTITY_AUTO_CLOSE_BOT_ID_FIELD'),
				'default_value' => '0',
			),
			'AUTO_CLOSE_TIME' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('CONFIG_ENTITY_AUTO_CLOSE_TIME_FIELD_NEW'),
				'default_value' => '14400',
			),
			'AUTO_CLOSE_TEXT' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('CONFIG_ENTITY_AUTO_CLOSE_TEXT_FIELD_NEW'),
			),
			'AUTO_EXPIRE_TIME' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('CONFIG_ENTITY_AUTO_EXPIRE_TIME_FIELD'),
				'default_value' => '86400',
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('CONFIG_ENTITY_DATE_CREATE_FIELD'),
				'default_value' => array(__CLASS__, 'getCurrentDate'),
			),
			'DATE_MODIFY' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('CONFIG_ENTITY_DATE_MODIFY_FIELD'),
				'default_value' => array(__CLASS__, 'getCurrentDate'),
			),
			'MODIFY_USER_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('CONFIG_ENTITY_MODIFY_USER_ID_FIELD'),
			),
			'TEMPORARY' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('CONFIG_ENTITY_TEMPORARY_FIELD'),
				'default_value' => 'Y',
			),
			new StringField('XML_ID', [
				'validation' => [__CLASS__, 'validateText255'],
			]),
			new StringField('LANGUAGE_ID', [
				'validation' => [__CLASS__, 'validateText2'],
			]),
			'STATISTIC' => array(
				'data_type' => 'Bitrix\ImOpenLines\Model\ConfigStatistic',
				'reference' => array('=this.ID' => 'ref.CONFIG_ID')
			),
			'QUICK_ANSWERS_IBLOCK_ID' => array(
				'data_type' => 'integer',
			),
			'SESSION_PRIORITY' => array(
				'data_type' => 'integer',
				'default_value' => 0,
			),
			new StringField('TYPE_MAX_CHAT', [
				'validation' => [__CLASS__, 'validateText50'],
				'default_value' => 'ANSWERED_NEW',
			]),
			'MAX_CHAT' => array(
				'data_type' => 'integer',
				'default_value' => '0',
			),
			new StringField('OPERATOR_DATA', [
				'validation' => [__CLASS__, 'validateText50'],
				'title' => Loc::getMessage('CONFIG_ENTITY_OPERATOR_DATA_FIELD'),
				'default_value' => 'profile',
			]),
			new StringField('DEFAULT_OPERATOR_DATA', [
				'title' => Loc::getMessage('CONFIG_ENTITY_DEFAULT_OPERATOR_DATA_FIELD'),
				'serialized' => true
			]),
			new IntegerField('MIN_DAYS_TO_CHECK_DIALOGS', [
				'title' => Loc::getMessage('CONFIG_ENTITY_DAYS_TO_CHECK_DIALOGS_FIELD'),
				'default_value' => 1
			]),
			'KPI_FIRST_ANSWER_TIME' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('CONFIG_ENTITY_KPI_FIRST_ANSWER_TIME'),
				'default_value' => 0,
			),
			'KPI_FIRST_ANSWER_ALERT' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('CONFIG_ENTITY_KPI_ANSWER_ALERT'),
				'default_value' => 'N',
			),
			'KPI_FIRST_ANSWER_LIST' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('CONFIG_ENTITY_KPI_ANSWER_LIST'),
				'serialized' => true,
			),
			'KPI_FIRST_ANSWER_TEXT' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('CONFIG_ENTITY_KPI_ANSWER_TEXT')
			),
			'KPI_FURTHER_ANSWER_TIME' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('CONFIG_ENTITY_KPI_FURTHER_ANSWER_TIME'),
				'default_value' => 0,
			),
			'KPI_FURTHER_ANSWER_ALERT' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('CONFIG_ENTITY_KPI_ANSWER_ALERT'),
				'default_value' => 'N',
			),
			'KPI_FURTHER_ANSWER_LIST' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('CONFIG_ENTITY_KPI_ANSWER_LIST'),
				'serialized' => true,
			),
			'KPI_FURTHER_ANSWER_TEXT' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('CONFIG_ENTITY_KPI_ANSWER_TEXT')
			),
			'KPI_CHECK_OPERATOR_ACTIVITY' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('CONFIG_ENTITY_KPI_CHECK_OPERATOR_ACTIVITY'),
				'default_value' => 'N',
			),
		);
	}

	/**
	 * Returns validators for a text field of up to 2000 characters.
	 *
	 * @return array
	 * @throws Main\ArgumentTypeException
	 */
	public static function validateText2000()
	{
		return [
			new Validator\Length(null, 2000),
		];
	}
	/**
	 * Returns validators for a text field of up to 255 characters.
	 *
	 * @return array
	 * @throws Main\ArgumentTypeException
	 */
	public static function validateText255()
	{
		return [
			new Validator\Length(null, 255),
		];
	}
	/**
	 * Returns validators for a text field of up to 50 characters.
	 *
	 * @return array
	 * @throws Main\ArgumentTypeException
	 */
	public static function validateText50()
	{
		return [
			new Validator\Length(null, 50),
		];
	}
	/**
	 * Returns validators for a text field of up to 20 characters.
	 *
	 * @return array
	 * @throws Main\ArgumentTypeException
	 */
	public static function validateText20()
	{
		return [
			new Validator\Length(null, 20),
		];
	}
	/**
	 * Returns validators for a text field of up to 5 characters.
	 *
	 * @return array
	 * @throws Main\ArgumentTypeException
	 */
	public static function validateText5()
	{
		return [
			new Validator\Length(null, 5),
		];
	}
	/**
	 * Returns validators for a text field of up to 2 characters.
	 *
	 * @return array
	 * @throws Main\ArgumentTypeException
	 */
	public static function validateText2()
	{
		return [
			new Validator\Length(null, 2),
		];
	}

	/**
	 * Return current date for DATE_CREATE field.
	 *
	 * @return DateTime
	 * @throws Main\ObjectException
	 */
	public static function getCurrentDate()
	{
		return new DateTime();
	}
}