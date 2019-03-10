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

$arDefaultUrlTemplates404 = array(
    "list" => "index.php",
    "expert" => "/experts/#EXPERT_CODE#/"
);

$arDefaultVariableAliases404 = array();

$arDefaultVariableAliases = array();

$arComponentVariables = array("EXPERT_CODE", "EXPERT_ID");


$SEF_FOLDER = "";
$arUrlTemplates = array();

if ($arParams["SEF_MODE"] == "Y")
{
    $arVariables = array();

    $arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
    $arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);

    $engine = new CComponentEngine($this);

    $arUrlTemplates = Array(
        'list' => '',
        'expert' => '#EXPERT_CODE#/',
        "signal.list" => "#EXPERT_CODE#/trading_signals/",
        "signal.service.list" => "#EXPERT_CODE#/trading_signals/#SERVICE#/",
        "report.list" => "#EXPERT_CODE#/reports/",
        "report.detail" => "#EXPERT_CODE#/reports/#REPORT_CODE#/",
        "video.list" => "#EXPERT_CODE#/video/",
        "video.detail" => "#EXPERT_CODE#/video/#VIDEO_CODE#/",
        "stats.detail" => "#EXPERT_CODE#/stats/"
    );

    $componentPage = $engine->guessComponentPath(
        $arParams["SEF_FOLDER"],
        $arUrlTemplates,
        $arVariables
    );

    if (StrLen($componentPage) <= 0)
        $componentPage = "list";

    CComponentEngine::InitComponentVariables($componentPage,
        $arComponentVariables,
        $arVariableAliases,
        $arVariables);

    $SEF_FOLDER = $arParams["SEF_FOLDER"];
}

$arResult = array(
    "FOLDER" => $SEF_FOLDER,
    "URL_TEMPLATES" => $arUrlTemplates,
    "VARIABLES" => $arVariables,
    "ALIASES" => $arVariableAliases,
    "PAGE" => $componentPage,
    "URL" => \Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest()->getRequestedPage()
);

$arParams["VARIABLES"] = $arVariables;

if(!count($arResult["VARIABLES"]) && $arResult["URL"] != "/experts/index.php"){
    CHTTP::SetStatus("404 Not Found");
    @define("ERROR_404", "Y");
}

if($arVariables["EXPERT_CODE"]) {

    global $USER;
    $filter = Array(
        "GROUPS_ID" => Array(5),
        "ACTIVE" => "Y",
        "LOGIN_EQUAL" => $arVariables["EXPERT_CODE"]
    );
    $rsUsers = \CUser::GetList($by = "UF_SORT", $order = "ASC", $filter);
    while ($arUser = $rsUsers->Fetch()) {
        if($arUser["LOGIN"] == $arVariables["EXPERT_CODE"])
            $arParams["EXPERT_ID"] = $arUser["ID"];
    }

    $arParams["EXPERT_CODE"] = $arVariables["EXPERT_CODE"];

    if(!$arParams["EXPERT_ID"]){
        \CHTTP::SetStatus("404 Not Found");
        @define("ERROR_404", "Y");
    }
}
if($arVariables["EXPERT_CODE"] && $arVariables["REPORT_CODE"]) {
    $arParams["EXPERT_CODE"] = $arVariables["EXPERT_CODE"];
    $arParams["REPORT_CODE"] = $arVariables["REPORT_CODE"];
}

//$CUtils->deb($arResult);

$this->IncludeComponentTemplate($componentPage);
