<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

//$componentTemplate = $USER->IsAdmin() ? "uikit_v3-admin" : "uikit_v3";
$componentTemplate = "uikit_v3-admin";

$APPLICATION->IncludeComponent(
    "aofx:experts.signal.service.list",
    $componentTemplate,
    $arParams,
    $component
);

//deb($arParams);
//deb($arResult);
