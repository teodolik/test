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

$CExpert = new \AOFX\CExpert();
$CUtils = new \AOFX\CUtils();

$arResult["EXPERT_ID"] = $arParams["EXPERT_ID"];
$arResult["EXPERT_CODE"] = $arParams["EXPERT_CODE"];

$arResult["EXPERT"] = $CExpert->getExpertData($arResult["EXPERT_ID"]);

foreach ($arResult["EXPERT"]["CERTS"] as $key => $arCert){
    $arResult["EXPERT"]["CERTS"][$key] = array(
        "THUMB" => \CFile::ResizeImageGet($arCert, array('width' => 600, 'height' => 600), BX_RESIZE_IMAGE_PROPORTIONAL, true)["src"],
        "MAX" => \CFile::ResizeImageGet($arCert, array('width' => 1600, 'height' => 1600), BX_RESIZE_IMAGE_PROPORTIONAL, true)["src"],
);
}

$this->IncludeComponentTemplate();