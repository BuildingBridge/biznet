<?php

class CrmReportAnalyticsLimit extends CBitrixComponent
{
	public function executeComponent()
	{
		$this->arResult['LIMITS'] = isset($this->arParams['LIMITS']) ? $this->arParams['LIMITS'] : [];
		$this->arResult['BOARD_ID'] = $this->arParams['BOARD_ID'];
		$this->includeComponentTemplate();
	}

}