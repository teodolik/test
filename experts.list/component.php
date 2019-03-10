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

$arResult = $CExpert->All(true);

foreach ($arResult["EXPERTS"] as $key => $arExpert){
//    $expertPhoto = \CFile::GetPath($arExpert["PICTURE"]);
    $expertPhoto = \CFile::ResizeImageGet($arExpert["PICTURE"], Array("width" => 120, "height" => 120), BX_RESIZE_IMAGE_PROPORTIONAL_ALT)["src"];

    $arResult["EXPERTS"][$key]["PICTURE"] = $expertPhoto;
}

$this->IncludeComponentTemplate();