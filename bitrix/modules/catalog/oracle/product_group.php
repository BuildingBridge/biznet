<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/general/product_group.php");

class CCatalogProductGroups extends CAllCatalogProductGroups
{
	public static function Add($arFields)
	{
		global $DB;

		if (!self::CheckFields("ADD", $arFields, 0))
			return False;

		$ID = (int)$DB->NextID('SQ_B_CATALOG_PRODUCT2GROUP');

		$arInsert = $DB->PrepareInsert("B_CATALOG_PRODUCT2GROUP", $arFields);

		$strSql = "INSERT INTO B_CATALOG_PRODUCT2GROUP(ID, ".$arInsert[0].") VALUES(".$ID.", ".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		return $ID;
	}

	/**
	 * @param array $arOrder
	 * @param array $arFilter
	 * @param bool|array $arGroupBy
	 * @param bool|array $arNavStartParams
	 * @param array $arSelectFields
	 * @return bool|CDBResult
	 */
	public static function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		$arFields = array(
			"ID" => array("FIELD" => "CPG.ID", "TYPE" => "int"),
			"PRODUCT_ID" => array("FIELD" => "CPG.PRODUCT_ID", "TYPE" => "int"),
			"GROUP_ID" => array("FIELD" => "CPG.GROUP_ID", "TYPE" => "int"),
			"ACCESS_LENGTH" => array("FIELD" => "CPG.ACCESS_LENGTH", "TYPE" => "int"),
			"ACCESS_LENGTH_TYPE" => array("FIELD" => "CPG.ACCESS_LENGTH_TYPE", "TYPE" => "char"),
			"GROUP_ACTIVE" => array("FIELD" => "G.ACTIVE", "TYPE" => "char", "FROM" => "INNER JOIN B_GROUP G ON (CPG.GROUP_ID = G.ID)"),
			"GROUP_NAME" => array("FIELD" => "G.NAME", "TYPE" => "string", "FROM" => "INNER JOIN B_GROUP G ON (CPG.GROUP_ID = G.ID)")
		);

		$arSqls = CCatalog::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (empty($arGroupBy) && is_array($arGroupBy))
		{
			$strSql = "SELECT ".$arSqls["SELECT"]." FROM B_CATALOG_PRODUCT2GROUP CPG ".$arSqls["FROM"];
			if (!empty($arSqls["WHERE"]))
				$strSql .= " WHERE ".$arSqls["WHERE"];
			if (!empty($arSqls["GROUPBY"]))
				$strSql .= " GROUP BY ".$arSqls["GROUPBY"];

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return false;
		}

		$strSql = "SELECT ".$arSqls["SELECT"]." FROM B_CATALOG_PRODUCT2GROUP CPG ".$arSqls["FROM"];
		if (!empty($arSqls["WHERE"]))
			$strSql .= " WHERE ".$arSqls["WHERE"];
		if (!empty($arSqls["GROUPBY"]))
			$strSql .= " GROUP BY ".$arSqls["GROUPBY"];
		if (!empty($arSqls["ORDERBY"]))
			$strSql .= " ORDER BY ".$arSqls["ORDERBY"];

		$intTopCount = 0;
		$boolNavStartParams = (!empty($arNavStartParams) && is_array($arNavStartParams));
		if ($boolNavStartParams && array_key_exists('nTopCount', $arNavStartParams))
		{
			$intTopCount = intval($arNavStartParams["nTopCount"]);
		}
		if ($boolNavStartParams && 0 >= $intTopCount)
		{
			$strSql_tmp = "SELECT COUNT('x') as CNT FROM B_CATALOG_PRODUCT2GROUP CPG ".$arSqls["FROM"];
			if (!empty($arSqls["WHERE"]))
				$strSql_tmp .= " WHERE ".$arSqls["WHERE"];
			if (!empty($arSqls["GROUPBY"]))
				$strSql_tmp .= " GROUP BY ".$arSqls["GROUPBY"];

			$dbRes = $DB->Query($strSql_tmp, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$cnt = 0;
			if ($arRes = $dbRes->Fetch())
				$cnt = $arRes["CNT"];

			$dbRes = new CDBResult();

			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if ($boolNavStartParams && 0 < $intTopCount)
			{
				$strSql = "SELECT * FROM (".$strSql.") WHERE ROWNUM<=".$intTopCount;
			}
			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $dbRes;
	}
}