<?
class CIBlockRSS extends CAllIBlockRSS
{
	function GetCache($cacheKey)
	{
		global $DB;

		$strSql = "
			SELECT
				CACHE,
				case when CACHE_DATE>getdate() then 'Y' else 'N' end as VALID
			FROM
				b_iblock_cache
			WHERE
				CACHE_KEY = '".$DB->ForSql($cacheKey)."'
			";

		$db_res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		return $db_res->Fetch();
	}

	function Add($IBLOCK_ID, $NODE, $NODE_VALUE)
	{
		global $DB;
		$IBLOCK_ID = IntVal($IBLOCK_ID);
		$strSql = "
			INSERT INTO b_iblock_rss (
				IBLOCK_ID,
				NODE,
				NODE_VALUE
			) VALUES (
				$IBLOCK_ID,
				'".$DB->ForSql($NODE, 50)."',
				'".$DB->ForSql($NODE_VALUE, 255)."'
			)
			";

		$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
	}

	function UpdateCache($cacheKey, $CACHE, $HOURS_CACHE, $bCACHED)
	{
		global $DB;

		if(is_array($HOURS_CACHE) && array_key_exists("minutes", $HOURS_CACHE))
			$TTL = intval($HOURS_CACHE["minutes"]) * 60;
		else
			$TTL = intval($HOURS_CACHE) * 3600;

		if ($bCACHED)
		{
			$strSql = "
				UPDATE b_iblock_cache SET
					CACHE = '".$DB->ForSql($CACHE)."',
					CACHE_DATE = dateadd(second, $TTL, getdate())
				WHERE
					CACHE_KEY = '".$DB->ForSql($cacheKey)."'
				";

			$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		}
		else
		{
			$strSql = "
				INSERT INTO b_iblock_cache (
					CACHE_KEY,
					CACHE,
					CACHE_DATE
				) VALUES (
					'".$DB->ForSql($cacheKey)."',
					'".$DB->ForSql($CACHE)."',
					dateadd(second, $TTL, getdate())
				)
				";

			$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		}
		$db_res = $DB->Query("DELETE from b_iblock_cache WHERE CACHE_DATE < getdate()");
	}

	function GetRSSText($arIBLOCK, $LIMIT_NUM = false, $LIMIT_DAY = false, $yandex = false)
	{
		global $DB;

		$strRes = "";

		$serverName = "";

		if (isset($arIBLOCK["SERVER_NAME"]) && strlen($arIBLOCK["SERVER_NAME"]) > 0)
			$serverName = $arIBLOCK["SERVER_NAME"];

		if (strlen($serverName) <=0 && !isset($arIBLOCK["SERVER_NAME"]))
		{
			$dbSite = CSite::GetList(($b="sort"), ($o="asc"), array("LID" => $arIBLOCK["LID"]));
			if ($arSite = $dbSite->Fetch())
				$serverName = $arSite["SERVER_NAME"];
		}

		if (strlen($serverName) <=0)
		{
			if (defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME)>0)
				$serverName = SITE_SERVER_NAME;
			else
				$serverName = COption::GetOptionString("main", "server_name", "www.bitrixsoft.com");
		}

		$strRes .= "<channel>\n";
		$strRes .= "<title>".htmlspecialcharsbx($arIBLOCK["NAME"])."</title>\n";
		$strRes .= "<link>http://".$serverName."</link>\n";
		$strRes .= "<description>".htmlspecialcharsbx($arIBLOCK["DESCRIPTION"])."</description>\n";
		$strRes .= "<lastBuildDate>".date("r")."</lastBuildDate>\n";
		$strRes .= "<ttl>".$arIBLOCK["RSS_TTL"]."</ttl>\n";

		$db_img_arr = CFile::GetFileArray($arIBLOCK["PICTURE"]);
		if ($db_img_arr)
		{
			if(substr($db_img_arr["SRC"], 0, 1) == "/")
				$strImage = "http://".$serverName.$db_img_arr["SRC"];
			else
				$strImage = $db_img_arr["SRC"];

			if ($yandex)
			{
				$strRes .= "<yandex:logo>".$strImage."</yandex:logo>\n";
				$squareSize = min($db_img_arr["WIDTH"], $db_img_arr["HEIGHT"]);
				if ($squareSize > 0)
				{
					$squarePicture = CFile::ResizeImageGet(
						$db_img_arr,
						array("width" => $squareSize, "height" => $squareSize),
						BX_RESIZE_IMAGE_EXACT
					);
					if ($squarePicture)
					{
						if(substr($squarePicture["src"], 0, 1) == "/")
							$squareImage = "http://".$serverName.$squarePicture["src"];
						else
							$squareImage = $squarePicture["src"];
						$strRes .= "<yandex:logo type=\"square\">".$squareImage."</yandex:logo>\n";
					}
				}
			}
			else
			{
				$strRes .= "<image>\n";
				$strRes .= "<title>".htmlspecialcharsbx($arIBLOCK["NAME"])."</title>\n";
				$strRes .= "<url>".$strImage."</url>\n";
				$strRes .= "<link>http://".$serverName."</link>\n";
				$strRes .= "<width>".$db_img_arr["WIDTH"]."</width>\n";
				$strRes .= "<height>".$db_img_arr["HEIGHT"]."</height>\n";
				$strRes .= "</image>\n";
			}
		}

		$arNodes = array();
		$db_res = $DB->Query("SELECT NODE, NODE_VALUE FROM b_iblock_rss WHERE IBLOCK_ID = ".intval($arIBLOCK["ID"]), false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		while ($db_res_arr = $db_res->Fetch())
		{
			$arNodes[$db_res_arr["NODE"]] = $db_res_arr["NODE_VALUE"];
		}

		CTimeZone::Disable();

		$strSql = "
			SELECT
				BE.*,
				".$DB->DateToCharFunction("BE.TIMESTAMP_X")." TIMESTAMP_X,
				BE.ACTIVE_FROM, ".$DB->DateToCharFunction("BE.ACTIVE_FROM", "FULL")." ACTIVE_FROM,
				".$DB->DateToCharFunction("BE.ACTIVE_TO", "FULL")." ACTIVE_TO,
				L.DIR LANG_DIR,
				B.DETAIL_PAGE_URL,
				B.LIST_PAGE_URL, B.LID, L.SERVER_NAME
			FROM
				b_iblock_element BE,
				b_iblock B,
				b_lang L
			WHERE
				BE.IBLOCK_ID = B.ID
			and BE.WF_STATUS_ID = 1
			and BE.WF_PARENT_ELEMENT_ID is null
			and B.LID = L.LID
			and BE.IBLOCK_ID = ".intval($arIBLOCK["ID"])."
			and (
					(BE.ACTIVE_TO >= ".$DB->GetNowFunction()." or BE.ACTIVE_TO IS NULL) and
					(BE.ACTIVE_FROM <= ".$DB->GetNowFunction()." or BE.ACTIVE_FROM IS NULL)
				)
			and BE.ACTIVE = 'Y'
			and exists(
					SELECT
						'x'
					FROM
						b_iblock_group IBG
					WHERE
						IBG.IBLOCK_ID=B.ID
						and IBG.GROUP_ID IN (2)
						and IBG.PERMISSION>='R'
						and (IBG.PERMISSION='X' or B.ACTIVE='Y')
					)
			";

		if ($LIMIT_DAY!==false)
		{
			$stmp = AddToTimeStamp(array("DD" => -intval($LIMIT_DAY)), time());
			$date = ConvertTimeStamp($stmp, "FULL");
			$strSql .= " and (BE.ACTIVE_FROM >= ".$DB->CharToDateFunction($date, "FULL")." or BE.ACTIVE_FROM IS NULL) ";
		}
		$strSql .= "ORDER BY BE.ACTIVE_FROM DESC, BE.SORT ASC ";

		$res = $DB->Query($strSql);
		
		CTimeZone::Enable();

		$items = new CIBlockResult($res->result);
		if ($LIMIT_NUM!==False && IntVal($LIMIT_NUM)>0)
			$items->NavStart($LIMIT_NUM);

		while ($arItem = $items->GetNext())
		{
			$props = CIBlockElement::GetProperty($arIBLOCK["ID"], $arItem["ID"], "sort", "asc", Array("ACTIVE"=>"Y", "NON_EMPTY"=>"Y"));
			$arProps = Array();
			while ($arProp = $props->Fetch())
			{
				if (strlen($arProp["CODE"])>0)
					$arProps[$arProp["CODE"]] = Array("NAME"=>htmlspecialcharsbx($arProp["NAME"]), "VALUE"=>htmlspecialcharsex($arProp["VALUE"]));
				else
					$arProps[$arProp["ID"]] = Array("NAME"=>htmlspecialcharsbx($arProp["NAME"]), "VALUE"=>htmlspecialcharsex($arProp["VALUE"]));
			}

			$arLinkProp = $arProps["DOC_LINK"];

			$strRes .= "<item>\n";
			if (strlen($arNodes["title"])>0)
			{
				$strRes .= "<title>".htmlspecialcharsbx(CIBlockRSS::ExtractProperties($arNodes["title"], $arProps, $arItem))."</title>\n";
			}
			else
			{
				$strRes .= "<title>".htmlspecialcharsbx($arItem["~NAME"])."</title>\n";
			}
			if (strlen($arNodes["link"])>0)
			{
				$strRes .= "<link>".CIBlockRSS::ExtractProperties($arNodes["link"], $arProps, $arItem)."</link>\n";
			}
			else
			{
				$strRes .= "<link>http://".$serverName.(($arLinkProp["VALUE"]) ? $arLinkProp["VALUE"] : $arItem["DETAIL_PAGE_URL"])."</link>\n";
			}
			if (strlen($arNodes["description"])>0)
			{
				$strRes .= "<description>".htmlspecialcharsbx(CIBlockRSS::ExtractProperties($arNodes["description"], $arProps, $arItem))."</description>\n";
			}
			else
			{
				$strRes .= "<description>".(($arItem["PREVIEW_TEXT"] || $yandex) ? htmlspecialcharsbx($arItem["PREVIEW_TEXT"]) : htmlspecialcharsbx($arItem["DETAIL_TEXT"]))."</description>\n";
			}
			if (strlen($arNodes["enclosure"])>0)
			{
				$strRes .= "<enclosure url=\"".htmlspecialcharsbx(CIBlockRSS::ExtractProperties($arNodes["enclosure"], $arProps, $arItem))."\" length=\"".htmlspecialcharsbx(CIBlockRSS::ExtractProperties($arNodes["enclosure_length"], $arProps, $arItem))."\" type=\"".htmlspecialcharsbx(CIBlockRSS::ExtractProperties($arNodes["enclosure_type"], $arProps, $arItem))."\"/>\n";
			}
			else
			{
				$db_img_arr = CFile::GetFileArray($arItem["PREVIEW_PICTURE"]);
				if ($db_img_arr)
				{
					if(substr($db_img_arr["SRC"], 0, 1) == "/")
						$strImage = "http://".$serverName.$db_img_arr["SRC"];
					else
						$strImage = $db_img_arr["SRC"];

					$strRes .= "<enclosure url=\"".$strImage."\" length=\"".$db_img_arr["FILE_SIZE"]."\" type=\"".$db_img_arr["CONTENT_TYPE"]."\" width=\"".$db_img_arr["WIDTH"]."\" height=\"".$db_img_arr["HEIGHT"]."\"/>\n";
				}
			}
			if (strlen($arNodes["category"])>0)
			{
				$strRes .= "<category>".htmlspecialcharsbx(CIBlockRSS::ExtractProperties($arNodes["category"], $arProps, $arItem))."</category>\n";
			}
			else
			{
				$strPath = "";
				$nav = CIBlockSection::GetNavChain($arIBLOCK["ID"], $arItem["IBLOCK_SECTION_ID"]);
				while($ar_nav = $nav->GetNext())
				{
					$strPath .= $ar_nav["NAME"]."/";
				}
				if (strlen($strPath)>0)
				{
					$strRes .= "<category>".htmlspecialcharsbx($strPath)."</category>\n";
				}
			}
			if ($yandex)
			{
				$strRes .= "<yandex:full-text>".htmlspecialcharsbx($arItem["DETAIL_TEXT"])."</yandex:full-text>\n";
			}
			if (strlen($arNodes["pubDate"])>0)
			{
				$strRes .= "<pubDate>".htmlspecialcharsbx(CIBlockRSS::ExtractProperties($arNodes["pubDate"], $arProps, $arItem))."</pubDate>\n";
			}
			else
			{
				if (strlen($arItem["ACTIVE_FROM"])>0)
				{
					$strRes .= "<pubDate>".date("r", MkDateTime($DB->FormatDate($arItem["ACTIVE_FROM"], Clang::GetDateFormat("FULL"), "DD.MM.YYYY H:I:S"), "d.m.Y H:i:s"))."</pubDate>\n";
				}
				else
				{
					$strRes .= "<pubDate>".date("r")."</pubDate>\n";
				}
			}
			$strRes .= "</item>\n";
		}
		$strRes .= "</channel>\n";
		return $strRes;
	}


	// Agent
	function PreGenerateRSS($IBLOCK_ID, $yandex = true)
	{
		global $DB;

		$IBLOCK_ID = intval($IBLOCK_ID);
		$strSql = "
			SELECT
				B.*,
				C.CHARSET,
				S.SERVER_NAME,
				".$DB->DateToCharFunction("B.TIMESTAMP_X")." as TIMESTAMP_X
			FROM
				b_iblock B
				LEFT JOIN b_lang S ON S.LID=B.LID
				LEFT JOIN b_culture C ON C.ID=S.CULTURE_ID
			WHERE
				B.ID = $IBLOCK_ID
			and exists(
				SELECT
					'x'
				FROM
					b_iblock_group IBG
				WHERE
					IBG.IBLOCK_ID=B.ID
				and IBG.GROUP_ID IN (2)
				and IBG.PERMISSION>='R'
				and (IBG.PERMISSION='X' or B.ACTIVE='Y')
				)
			";
		//echo "<pre>$strSql</pre>";
		$dbr = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		$bAccessable = False;
		if (($arIBlock = $dbr->GetNext()) && ($arIBlock["RSS_FILE_ACTIVE"]=="Y" && !$yandex || $arIBlock["RSS_YANDEX_ACTIVE"]=="Y" && $yandex))
			$bAccessable = True;

		if (!$bAccessable) return "";

		$strRes = "";
		$strRes .= "<"."?xml version=\"1.0\" encoding=\"".$arIBlock["CHARSET"]."\"?".">\n";
		$strRes .= "<rss version=\"2.0\"";
//		$strRes .= "<rss version=\"2.0\" xmlns=\"http://backend.userland.com/rss2\"";
		if ($yandex) $strRes .= " xmlns:yandex=\"http://news.yandex.ru\"";
		$strRes .= ">\n";

		$limit_num = false;
		$limit_day = 2;
		if (!$yandex)
		{
			$limit_num = false;
			if (strlen($arIBlock["RSS_FILE_LIMIT"])>0 && IntVal($arIBlock["RSS_FILE_LIMIT"])>0)
				$limit_num = IntVal($arIBlock["RSS_FILE_LIMIT"]);

			$limit_day = false;
			if (strlen($arIBlock["RSS_FILE_DAYS"])>0 && IntVal($arIBlock["RSS_FILE_DAYS"])>0)
				$limit_day = IntVal($arIBlock["RSS_FILE_DAYS"]);
		}
		$strRes .= CIBlockRSS::GetRSSText($arIBlock, $limit_num, $limit_day, $yandex);

		$strRes .= "</rss>\n";

		$rss_file = $_SERVER["DOCUMENT_ROOT"].COption::GetOptionString("iblock", "path2rss", "/upload/");
		if ($yandex)
			$rss_file .= "yandex_rss_".IntVal($arIBlock["ID"]).".xml";
		else
			$rss_file .= "iblock_rss_".IntVal($arIBlock["ID"]).".xml";
		$fp = fopen($rss_file, "w");
		fwrite($fp, $strRes);
		fclose($fp);

		global $pPERIOD;
		$pPERIOD = IntVal($arIBlock["RSS_TTL"])*60*60;
		return "CIBlockRSS::PreGenerateRSS(".$IBLOCK_ID.", ".($yandex?"true":"false").");";
	}

}
?>