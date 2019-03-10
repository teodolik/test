<?php
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$CUtils = new \AOFX\CUtils();
$CIncomeExpert = new \AOFX\CIncomeExpert();
$USER = new \CUser();

$arResult["DATA"] = array();

$arResult["GROUPS"] = $CIncomeExpert->getGroup();
//$CUtils->deb($arResult);
$arResult["USERS"]= $CIncomeExpert->subscribeExperts($arResult["GROUPS"]["GROUP_ID"],$arResult["GROUPS"]["GROUP"]);
$arResult["EXPERTS"]= $CIncomeExpert->getExpertGroupId($arResult["GROUPS"]["GROUP_ID"]);


$arResult["DATA"]["GROUPS"] = $arResult["GROUPS"]["GROUP"];
$arResult["DATA"]["ELEMENT"] = $arResult["EXPERTS"]["ELEMENTS"];
$arResult["DATA"]["USERS"] = $arResult["USERS"]["USER"];
$arResult["DATA"] = $CIncomeExpert->ExpertIncome($arResult["DATA"]["USERS"],$arResult["EXPERTS"]["ELEMENTS"]);
unset($arResult["DATA"]["GROUPS"]);
unset($arResult["DATA"]["USERS"]);
unset($arResult ["EXPERTS"]);
unset($arResult ["USERS"]);
unset($arResult ["GROUPS"]);

$CUtils->deb($arResult);
$this->IncludeComponentTemplate();