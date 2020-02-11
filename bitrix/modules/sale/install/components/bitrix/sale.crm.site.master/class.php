<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Main\Config\Option,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\Update\CrmEntityCreatorStepper,
	Bitrix\Wizard\Tools,
	Bitrix\Wizard\Templates;

Loc::loadMessages(__FILE__);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/wizard.php"); //Wizard API
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/wizard/utils.php"); //Wizard utils
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client.php");
require_once("tools/modulechecker.php");
require_once("tools/persontypepreparer.php");
require_once("tools/crmpackage.php");
require_once("tools/sitepatcher.php");
require_once("tools/agentchecker.php");
require_once("tools/b24connectoruninstaller.php");

/**
 * Class SaleCrmSiteMaster
 */
class SaleCrmSiteMaster extends \CBitrixComponent
{
	/** @var string Full path to wizard directory */
	const WIZARD_DIR = __DIR__."/wizard/";

	/** @var string */
	const CRM_WIZARD_SITE_ID = "~CRM_WIZARD_SITE_ID";

	/** @var string Has the last step been reached? */
	const IS_SALE_CRM_SITE_MASTER_FINISH = "~IS_SALE_CRM_SITE_MASTER_FINISH";

	/** @var string */
	const IS_SALE_CRM_SITE_MASTER_STUB = "~IS_SALE_CRM_SITE_MASTER_STUB";

	const ERROR_TYPE_COMPONENT = "COMPONENT";
	const ERROR_TYPE_ORDER = "ORDER";
	const ERROR_TYPE_WIZARD = "WIZARD";

	/** @var CWizardBase wizard */
	protected $wizard;

	/** @var Tools\ModuleChecker $moduleChecker */
	protected $moduleChecker;

	/** @var array default steps */
	protected $defaultStep = [];

	/** @var array required steps */
	protected $requiredStep = [];

	/** @var array variable for wizard */
	protected $wizardVar = [];

	/** @var array error for wizard's step */
	protected $wizardStepErrors = [];

	/**
	 * @param $arParams
	 * @return array
	 */
	public function onPrepareComponentParams($arParams)
	{
		$this->defaultStep = $arParams["DEFAULT_STEPS"];
		$this->requiredStep = $arParams["REQUIRED_STEPS"];
		$modulesRequired = $arParams["MODULES_REQUIRED"];

		$this->moduleChecker = new Tools\ModuleChecker();
		$this->moduleChecker->setRequiredModules($modulesRequired);

		$this->arResult = [
			"CONTENT" => "",
			"WIZARD_STEPS" => [],
			"ERROR" => [
				self::ERROR_TYPE_COMPONENT => [],
				self::ERROR_TYPE_WIZARD => [],
				self::ERROR_TYPE_ORDER => [],
			],
		];

		return $arParams;
	}

	/**
	 * @return Tools\ModuleChecker
	 */
	public function getModuleChecker()
	{
		return $this->moduleChecker;
	}

	/**
	 * @param $name
	 * @param $value
	 */
	protected function addWizardVar($name, $value)
	{
		$this->wizardVar[$name] = $value;
	}

	/**
	 * @return array
	 */
	protected function getWizardVars()
	{
		return $this->wizardVar;
	}

	/**
	 * @param $stepName
	 * @param $value
	 */
	protected function addWizardStepError($stepName, $value)
	{
		$this->wizardStepErrors[$stepName]["ERRORS"][] = $value;
	}

	/**
	 * @param $stepName
	 * @return array
	 */
	public function getWizardStepErrors($stepName)
	{
		return $this->wizardStepErrors[$stepName]["ERRORS"];
	}

	/**
	 * @param $errors
	 * @param $type
	 */
	protected function addErrors($errors, $type)
	{
		if (!is_array($errors))
		{
			$errors = [$errors];
		}

		foreach ($errors as $error)
		{
			$this->addError($error, $type);
		}
	}

	/**
	 * @param array|string $error
	 * @param $type
	 */
	protected function addError($error, $type)
	{
		$this->arResult["ERROR"][$type] = array_merge($this->arResult["ERROR"][$type], [$error]);
	}

	/**
	 * @param $type
	 * @return array
	 */
	protected function getErrors($type)
	{
		return isset($this->arResult["ERROR"][$type]) ? $this->arResult["ERROR"][$type] : [];
	}

	/**
	 * @return array
	 */
	protected function getWizardErrors()
	{
		return $this->arResult["WIZARD_ERRORS"];
	}

	/**
	 * @param $stepName
	 * @param $sort
	 * @param bool $replace
	 */
	protected function addWizardStep($stepName, $sort, $replace = false)
	{
		if ($replace)
		{
			$this->defaultStep = [];
		}

		$this->defaultStep[$stepName] = [
			"SORT" => $sort
		];
	}

	/**
	 * Include wizard's step
	 */
	protected function includeWizardSteps()
	{
		$steps = $this->arResult["WIZARD_STEPS"];
		foreach ($steps as $step)
		{
			$class = array_pop(explode("\\", $step));
			$stepFile = strtolower($class).".php";
			if (Main\IO\File::isFileExists(self::WIZARD_DIR.$stepFile))
			{
				require_once(self::WIZARD_DIR.$stepFile);
			}
			else
			{
				$this->addError(Loc::getMessage("SALE_CSM_WIZARD_STEP_NOT_FOUND", [
					"#STEP_NAME#" => $step
				]), self::ERROR_TYPE_WIZARD);
			}
		}
	}

	/**
	 * Include wizard's template
	 */
	protected function includeWizardTemplate()
	{
		if (Main\IO\File::isFileExists(self::WIZARD_DIR."template/crmsitemastertemplate.php"))
		{
			require_once(self::WIZARD_DIR."template/crmsitemastertemplate.php");
		}
		else
		{
			$this->addError(Loc::getMessage("SALE_CSM_WIZARD_TEMPLATE_NOT_FOUND"), self::ERROR_TYPE_WIZARD);
		}
	}

	/**
	 * Create wizard and add steps
	 *
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	protected function createWizard()
	{
		$crmPackage = new Tools\CrmPackage();
		$crmPackage->setId("bitrix:portal");
		$crmPackage->setSiteId($this->getCrmSiteId());

		$this->wizard = new CWizardBase(Loc::getMessage("SALE_CSM_TITLE"), $crmPackage);

		// before AddSteps
		$this->setWizardVariables();

		$this->wizard->AddSteps($this->arResult["WIZARD_STEPS"]); //Add steps
		$this->wizard->DisableAdminTemplate();
		$this->wizard->SetTemplate(new Templates\CrmSiteMasterTemplate());
		$this->wizard->SetReturnOutput();
		$this->wizard->SetFormName("sale_crm_site_master");
	}

	/**
	 * @return CWizardBase
	 */
	public function getWizard()
	{
		return $this->wizard;
	}

	/**
	 * Set variables for wizard
	 */
	protected function setWizardVariables()
	{
		if (!($wizard = $this->getWizard()))
		{
			return;
		}

		$wizard->SetVar("component", $this);
		$wizard->SetVar("modulesRequired", $this->moduleChecker->getRequiredModules());

		foreach ($this->getWizardVars() as $varName => $varValue)
		{
			$wizard->SetVar($varName, $varValue);
		}
	}

	/**
	 * Check additional required step
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected function initSteps()
	{
		$notExistModules = $this->moduleChecker->getNotExistModules();

		if ($notExistModules)
		{
			$notAvailableModule = $this->moduleChecker->checkAvailableModules($notExistModules);
			if ($notAvailableModule["ERROR"])
			{
				$this->addWizardStepError("Bitrix\Wizard\Steps\ActivationKeyStep", $notAvailableModule["ERROR"]);
			}

			if ($notAvailableModule["MODULES"])
			{
				// Activate coupon
				$this->addWizardStep("Bitrix\Wizard\Steps\ActivationKeyStep", 140);
			}
			else
			{
				// Update system
				$this->addWizardStep("Bitrix\Wizard\Steps\UpdateSystemStep", 150);
				$this->addWizardVar("not_exist_modules", $notExistModules);
			}
		}
		else
		{
			$installedModules = $this->moduleChecker->checkInstalledModules();
			if ($installedModules["MIN_VERSION"])
			{
				$this->addWizardStep("Bitrix\Wizard\Steps\ModuleStep", 160);
				$this->addWizardVar("min_version_modules", $installedModules["MIN_VERSION"]);
			}
			else
			{
				if ($installedModules["NOT_INSTALL"] || $this->moduleChecker->isModuleInstall())
				{
					$this->addWizardStep("Bitrix\Wizard\Steps\ModuleStep", 160);
					$this->addWizardStep("Bitrix\Wizard\Steps\ModuleInstallStep", 170);

					$this->addWizardVar("not_installed_modules", $installedModules["NOT_INSTALL"]);
				}
			}
		}

		$this->checkAgents();
		$this->checkPersonType();
		$this->checkB24Connection();

		$this->sortSteps();
	}

	/**
	 * Add wizard steps to component's params
	 */
	protected function addStepsToResult()
	{
		foreach ($this->defaultStep as $stepName => $step)
		{
			$this->arResult["WIZARD_STEPS"][] = $stepName;
		}
	}

	/**
	 * Sort wizard's step
	 */
	protected function sortSteps()
	{
		$arSteps = [];
		foreach ($this->defaultStep as $stepName => $step)
		{
			$arSteps[$stepName] = $step["SORT"];
		}

		// sort step
		array_multisort($arSteps, SORT_ASC, $this->defaultStep);
		unset($arSteps);
	}

	/**
	 * @param string $currentStep
	 * @return array
	 */
	public function getSteps($currentStep)
	{
		$result = [];

		$stepsName = $this->arResult["WIZARD_STEPS"];

		$firstStep = $stepsName[0];
		$lastKey = $stepsName[count($stepsName) - 1];

		if ($firstStep === $currentStep)
		{
			$result["NEXT_STEP"] = $stepsName[1];
		}
		elseif ($lastKey === $currentStep)
		{
			$result["PREV_STEP"] = $stepsName[count($stepsName) - 2];
		}
		else
		{
			$key = array_search($currentStep, $stepsName);
			$result["NEXT_STEP"] = $stepsName[$key+1];
			$result["PREV_STEP"] = $stepsName[$key-1];
		}

		return $result;
	}

	/**
	 * Control required steps
	 */
	protected function controlRequiredSteps()
	{
		if ($this->wizard->IsNextButtonClick())
		{
			$nextStepId = $this->wizard->GetNextStepID();
			$nextStepSort = $this->defaultStep[$nextStepId]["SORT"];
			foreach ($this->requiredStep as $stepName => $stepValues)
			{
				if (array_key_exists($nextStepId, $this->requiredStep))
				{
					continue;
				}

				if ($this->isStepExists($stepName))
				{
					if ($nextStepSort >= $stepValues["SORT"])
					{
						$this->setStepImmediately($stepName);
						break;
					}
				}
			}
		}
	}

	/**
	 * @param $stepName
	 * @return bool
	 */
	protected function isStepExists($stepName)
	{
		if (in_array($stepName, $this->arResult["WIZARD_STEPS"]))
		{
			return true;
		}

		return false;
	}

	/**
	 * @param $stepName
	 */
	protected function setStepImmediately($stepName)
	{
		unset($_REQUEST[$this->wizard->nextButtonID]);
		unset($_REQUEST[$this->wizard->nextStepHiddenID]);

		$this->wizard->SetCurrentStep($stepName);
	}

	/**
	 * @param $siteId
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function setCrmSiteId($siteId)
	{
		Option::set("sale", self::CRM_WIZARD_SITE_ID, $siteId);
	}

	/**
	 * @return string
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function getCrmSiteId()
	{
		return Option::get("sale", self::CRM_WIZARD_SITE_ID);
	}

	/**
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function setSaleCrmSiteMasterFinish()
	{
		Option::set("sale", self::IS_SALE_CRM_SITE_MASTER_FINISH, "Y");
	}

	/**
	 * @return bool
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function isSaleCrmSiteMasterFinish()
	{
		return (Option::get("sale", self::IS_SALE_CRM_SITE_MASTER_FINISH, "N") === "Y");
	}

	/**
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function setSaleCrmSiteMasterStub()
	{
		Option::set("sale", self::IS_SALE_CRM_SITE_MASTER_STUB, "Y");
	}

	/**
	 * @return string
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function getPathToOrderList()
	{
		$site = Main\SiteTable::getList([
			"select" => ["SERVER_NAME"],
			"filter" => ["=LID" => $this->getCrmSiteId()]
		])->fetch();

		$siteUrl = ($this->request->isHttps() ? "https://" : "http://").$site["SERVER_NAME"];
		$pathToOderList = Main\Config\Option::get('crm', 'path_to_order_list', '/shop/orders/');

		return $siteUrl.$pathToOderList;
	}

	/**
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected function checkPersonType()
	{
		$personTypePreparer = new Tools\PersonTypePreparer();

		$personTypeList = $personTypePreparer->getPersonTypeList();

		if ($personTypeList["NOT_MATCH"])
		{
			$this->addWizardStep("Bitrix\Wizard\Steps\PersonTypeStep", 130);
		}
	}

	/**
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	protected function checkAgents()
	{
		$agentChecker = new Tools\AgentChecker();
		$result = $agentChecker->checkAgents();
		if (!$result->isSuccess())
		{
			$this->addWizardStep("Bitrix\Wizard\Steps\AgentStep", 110);

			$errors = $result->getErrors();
			foreach ($errors as $error)
			{
				if ($error->getCode() === Tools\AgentChecker::ERROR_CODE_FAIL)
				{
					$this->addWizardVar("error", $error->getMessage());
					$this->addWizardVar("errorType", Tools\AgentChecker::ERROR_CODE_FAIL);

					break;
				}
				elseif ($error->getCode() === Tools\AgentChecker::ERROR_CODE_WARNING)
				{
					$this->addWizardVar("warning", $error->getMessage());
					$this->addWizardVar("errorType", Tools\AgentChecker::ERROR_CODE_WARNING);

					break;
				}
			}
		}
	}

	/**
	 * @throws Main\LoaderException
	 */
	private function checkB24Connection()
	{
		$b24connector = new Tools\B24ConnectorUnInstaller();
		if ($b24connector->isModule())
		{
			if ($b24connector->isSiteConnected())
			{
				$this->addWizardStep("Bitrix\Wizard\Steps\B24ConnectorStep", 120);
			}
			else
			{
				$result = $b24connector->uninstallModule();
				if (!$result->isSuccess())
				{
					$this->addWizardStep("Bitrix\Wizard\Steps\B24ConnectorStep", 120);
					$errors = [];
					foreach ($result->getErrors() as $error)
					{
						$errors[] = $error->getMessage();
					}

					$this->addWizardVar("b24connector_error", implode("<br>", $errors));
				}
			}
		}
	}

	private function prepareGrid()
	{
		$gridOptions = new Main\Grid\Options('order_error_list');
		$sort = $gridOptions->GetSorting(['sort' => ['ID' => 'DESC'], 'vars' => ['by' => 'by', 'order' => 'order']]);
		$navParams = $gridOptions->GetNavParams();

		$nav = new Main\UI\PageNavigation('order_error_list');
		$nav->allowAllRecords(true)->setPageSize($navParams['nPageSize'])->initFromUri();

		$errorList = CrmEntityCreatorStepper::getErrors([
			'count_total' => true,
			'offset' => $nav->getOffset(),
			'limit' => $nav->getLimit(),
			"order" => $sort["sort"]
		]);
		$nav->setRecordCount($errorList->getCount());

		$this->arResult["GRID"] = [
			"NAV_OBJECT" => $nav,
			"TOTAL_ROWS_COUNT" => $nav->getRecordCount(),
		];

		$tmpError = [];
		while ($error = $errorList->fetch())
		{
			$tmpError[]["data"] = [
				"ORDER_ID" => $error["ORDER_ID"],
				"ERROR" => $error["ERROR"]
			];
		}

		if ($tmpError)
		{
			$this->addErrors($tmpError, self::ERROR_TYPE_ORDER);
		}
	}

	/**
	 * @return mixed|void
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function executeComponent()
	{
		$this->checkModules();
		$this->checkSession();

		if ($errors = $this->getErrors(self::ERROR_TYPE_COMPONENT))
		{
			ShowError(implode("<br>", $errors));
			return;
		}

		if (self::isSaleCrmSiteMasterFinish()
			|| CrmEntityCreatorStepper::isAgent()
			|| CrmEntityCreatorStepper::isFinished()
		)
		{
			if (CrmEntityCreatorStepper::isFinished())
			{
				$this->prepareGrid();
			}

			if (empty($this->getErrors(self::ERROR_TYPE_ORDER)))
			{
				$this->addWizardStep("Bitrix\Wizard\Steps\FinishStep", 100, true);
			}
		}

		if (!self::isSaleCrmSiteMasterFinish())
		{
			$this->initSteps();
		}

		$this->addStepsToResult();

		$this->includeWizardTemplate();
		$this->includeWizardSteps();

		if (!$this->getErrors(self::ERROR_TYPE_COMPONENT) && !$this->getErrors(self::ERROR_TYPE_WIZARD))
		{
			$this->createWizard();
			$this->controlRequiredSteps();

			$content = $this->wizard->Display();
			$this->arResult['CONTENT'] = $content;
		}

		if ($wizardErrors = $this->getErrors(self::ERROR_TYPE_WIZARD))
		{
			ShowError(implode("<br>", $wizardErrors));
		}
		else
		{
			$this->includeComponentTemplate();
		}
	}

	private function checkModules()
	{
		if (!Loader::includeModule("sale"))
		{
			$this->addError(Loc::getMessage("SALE_CSM_MODULE_NOT_INSTALL"), self::ERROR_TYPE_COMPONENT);
		}
	}

	private function checkSession()
	{
		if ($this->request->isPost() && !check_bitrix_sessid())
		{
			$this->addError(Loc::getMessage("SALE_CSM_WIZARD_ERROR_SESSION_EXPIRED"), self::ERROR_TYPE_COMPONENT);
		}
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return __CLASS__;
	}
}