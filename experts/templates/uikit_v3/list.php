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

$componentName = "aofx:experts.list_v2";
$templateName = "uikit_v3";

//if($USER->IsAdmin()) $componentName = "aofx:experts.list_v2";

$APPLICATION->IncludeComponent(
    $componentName,
    $templateName,
    $arParams,
	$component
);

//deb($arParams);
//deb($arResult);
