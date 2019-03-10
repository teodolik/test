<?php

/**
 * namespace AOFX
 */

namespace AOFX;

/**
 * Class CExpert
 * @package AOFX
 */

class CExpert {

    /**
     * @var array $expertGroup
     */

    var $expertGroup = array(5);

    /**
     * @param $expertID
     * @param int $nPageSize
     * @return mixed
     * @throws \Bitrix\Main\LoaderException
     */

    public function getExpertStats($expertID, $nPageSize = 10){
        $arResult["ID"] = $expertID;

        if(\Bitrix\Main\Loader::IncludeModule("iblock")){

            $CUtils = new \AOFX\CUtils();
            $i=0;
            $arSelect = Array("ID", "ACTIVE_FROM", "PREVIEW_TEXT", "NAME", "DETAIL_PAGE_URL", "SHOW_COUNTER", "PROPERTY_relevant_to", "PROPERTY_expert", "SECTION_ID");
            $arFilter = Array("IBLOCK_ID" => 1, "ACTIVE" => "Y", "PROPERTY_expert" => $arResult["ID"], "SECTION_ID" => 100, "INCLUDE_SUBSECTIONS" => "Y");
            $res = \CIBlockElement::GetList(Array("ACTIVE_FROM" => "DESC"), $arFilter, false, array("nPageSize" => $nPageSize), $arSelect);
            while ($ob = $res->GetNextElement()) {
                $arFields = $ob->GetFields();
                $date["CLASS"] = "";

                $date["DATE"] = ConvertDateTime($arFields["ACTIVE_FROM"], "DD.MM.YYYY", "ru");
                if($date["DATE"] == date("d.m.Y")){
                    $date["DATE"] = "Сегодня";
                    $date["CLASS"] = "today";
                }else{
                    $date["DATE"] = $CUtils->editData($arFields["ACTIVE_FROM"], true);
                }
                $date["TIME"] = ConvertDateTime($arFields["ACTIVE_FROM"], "HH:MI", "ru");
                $arResult["ELEMENT"][$i] = $arFields;
                $arResult["ELEMENT"][$i]["DATE"] = $date;
                $arResult["ELEMENT"][$i]["LOCK"] = false;
                if(isset($arFields["PROPERTY_RELEVANT_TO_VALUE"]))
                    $arResult["ELEMENT"][$i]["LOCK"] = true;

                $arResult["ELEMENT"][$i]["PREVIEW_TEXT"] = strip_tags($arFields["PREVIEW_TEXT"]);
                $arResult["ELEMENT"][$i]["EXPERT_PHOTO"] = $this->getExpertData($arFields["PROPERTY_EXPERT_VALUE"])["PERSONAL_PHOTO"];
                $arResult["ELEMENT"][$i]["SECTION_CODE"] = \CIBlockSection::GetByID($arFields["IBLOCK_SECTION_ID"])->Fetch()["IBLOCK_SECTION_ID"];
                $arResult["ELEMENT"][$i]["TOP_SECTION_CODE"] = $arResult["ELEMENT"][$i]["SECTION_CODE"];
                $arResult["ELEMENT"][$i]["TOP_SECTION_CODE"] = $arResult["SECTIONS"][$arResult["ELEMENT"][$i]["TOP_SECTION_CODE"]]["CODE"];

                $i++;
            }
        }

        return $arResult;
    }


    /**
     * @param $expertID
     * @return mixed
     * @throws \Bitrix\Main\LoaderException
     */

    public static function getExpertAnalytics($expertID){
        $arResult["EXPERT_ID"] = $expertID;

        global $USER;
        $arResult["CURRENT_USER_ID"] = $USER->GetID();

        if(\Bitrix\Main\Loader::IncludeModule("iblock") && \Bitrix\Main\Loader::IncludeModule("catalog")) {

            $i=0;
            $arSelect = Array("ID", "NAME", "DETAIL_PAGE_URL", "PROPERTY_EXPERT", "PREVIEW_TEXT", "DETAIL_PICTURE");
            $arFilter = Array("IBLOCK_ID" => 77, "ACTIVE" => "Y", "ACTIVE_DATE" => "Y", "PROPERTY_EXPERT" => $arResult["EXPERT_ID"]);
            $res = \CIBlockElement::GetList(Array("ID" => "ASC"), $arFilter, false, false, $arSelect);
            while ($ob = $res->GetNextElement()) {
                $arFields = $ob->GetFields();
                $arResult["ANALYTICS"][$i] = array(
                    "ID" => $arFields["ID"],
                    "NAME" => $arFields["NAME"],
                    "DETAIL_PAGE_URL" => $arFields["DETAIL_PAGE_URL"],
                    "SUBSCRIBED" => false
                );

                if(\Bitrix\Main\Loader::IncludeModule("highloadblock")) {
                    $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById(1)->fetch();
                    $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
                    $entity_data_class = $entity->getDataClass();

                    $rsData = $entity_data_class::getList(array(
                        "select" => array("ID", "UF_TEMA_CURRENT", "UF_DATATO_CURRENT"),
                        "order" => array("ID" => "ASC"),
                        "filter" => array('UF_TEMA_CURRENT' => $arFields["ID"], ">UF_DATATO_CURRENT" => date("d.m.Y H:i:s"), "UF_USER_CURRENT" => $arResult["CURRENT_USER_ID"])
                    ));

                    while($arData = $rsData->Fetch())
                    {
                        $arResult["ANALYTICS"][$i]["SUBSCRIBED"] = true;
                        $arResult["ANALYTICS"][$i]["SUB_DATA"] = $arData["UF_DATATO_CURRENT"]->ToString();
                    }
                }

                $i++;
            }
        }

        return $arResult;
    }

    /**
     * @param $expertID
     * @return mixed
     * @throws \Bitrix\Main\LoaderException
     */

    public function getExpertServices($expertID){
        global $USER;
        $arResult["ID"] = $expertID;
        $arResult["EXPERT"] = $this->getExpertData($expertID);
        $arResult["CURRENT_USER_ID"] = $USER->GetID();
        $arResult["SECTIONS"][0] = array(
            "ID" => 0,
            "CODE" => "all",
            "NAME" => "Все услуги",
        );

        if(\Bitrix\Main\Loader::IncludeModule("iblock") && \Bitrix\Main\Loader::IncludeModule("catalog")) {
            $CIBlockElement = new \CIBlockElement();

            // ОБУЧЕНИЕ
            $i=0;
            $arSelect = Array("ID", "NAME", "DETAIL_PAGE_URL", "PROPERTY_EXPERT", "PREVIEW_TEXT", "DETAIL_PICTURE", "IBLOCK_CODE", "IBLOCK_ID");
            $arFilter = Array("IBLOCK_TYPE" => "training", "ACTIVE" => "Y", "ACTIVE_DATE" => "Y", "=PROPERTY_EXPERT" => $arResult["ID"]);
            $res = $CIBlockElement->GetList(Array("ID" => "ASC"), $arFilter, false, false, $arSelect);
            while ($ob = $res->GetNextElement()) {
                $arFields = $ob->GetFields();
                $arResult["SERVICES"][$i] = array(
                    "ID" => $arFields["ID"],
                    "NAME" => $arFields["NAME"],
                    "CODE" => $arFields["CODE"],
                    "TEXT" => $arFields["PREVIEW_TEXT"],
                    "EXPERT" => $arFields["PROPERTY_EXPERT_VALUE"],
                    "PRICE" => \CPrice::GetBasePrice($arFields["ID"])["PRICE"],
                    "TOP_SECTION_CODE" => $arFields["IBLOCK_CODE"],
//                    "DATA" => $arFields
                );

                $arResult["SECTIONS"][$arFields["IBLOCK_ID"]]["ID"] = $arFields["IBLOCK_ID"];
                $arResult["SECTIONS"][$arFields["IBLOCK_ID"]]["CODE"] = $arFields["IBLOCK_CODE"];
                $arResult["SECTIONS"][$arFields["IBLOCK_ID"]]["NAME"] = \CIBlock::GetByID($arFields["IBLOCK_ID"])->Fetch()["NAME"];
                $arResult["SECTIONS"][$arFields["IBLOCK_ID"]]["COUNT"] ++;

                $arResult["SERVICES"][$i]["PRICE"] = number_format($arResult["SERVICES"][$i]["PRICE"], 0, ".", " ");

                $resMarketType = $CIBlockElement->GetProperty($arFields["IBLOCK_ID"], $arFields["ID"], "sort", "asc", array("CODE" => "TYPE"));
                while ($obType = $resMarketType->GetNext()) {
                    if (count($obType) > 0) {
                        $arResult["SERVICES"][$i][$obType["CODE"]][] = $obType["VALUE_XML_ID"];
                    }
                }

                $i++;
            }

            // УСЛУГИ ЭКСПЕРТА
            $arSelect = Array("ID", "NAME", "CODE", "DETAIL_PAGE_URL", "PROPERTY_EXPERT", "PREVIEW_TEXT", "DETAIL_PICTURE", "IBLOCK_CODE", "IBLOCK_ID");
            $arFilter = Array("IBLOCK_ID" => 77, "ACTIVE" => "Y", "ACTIVE_DATE" => "Y", "PROPERTY_EXPERT" => $arResult["ID"]);
            $res = $CIBlockElement->GetList(Array("ID" => "ASC"), $arFilter, false, false, $arSelect);
            while ($ob = $res->GetNextElement()) {
                $arFields = $ob->GetFields();
                $arResult["SERVICES"][$i] = array(
                    "ID" => $arFields["ID"],
                    "NAME" => $arFields["NAME"],
                    "CODE" => $arFields["CODE"],
                    "TEXT" => $arFields["PREVIEW_TEXT"],
                    "EXPERT" => $arFields["PROPERTY_EXPERT_VALUE"],
                    "TOP_SECTION_CODE" => $arFields["IBLOCK_CODE"],
//                    "DATA" => $arFields
                );

                $resMarketType = $CIBlockElement->GetProperty($arFields["IBLOCK_ID"], $arFields["ID"], "sort", "asc", array("CODE" => "SERVICE_TYPE"));
                while ($obType = $resMarketType->GetNext())
                {
                    if(count($obType) > 0) {
//                        $arResult["SERVICES"][$i]["SERVICE_TYPE"] = $obType;

                        $arResult["SECTIONS"][$obType["VALUE"]]["ID"] = $obType["VALUE"];
                        $arResult["SECTIONS"][$obType["VALUE"]]["CODE"] = strtolower($obType["VALUE_XML_ID"]);
                        $arResult["SERVICES"][$i]["TOP_SECTION_CODE"] = strtolower($obType["VALUE_XML_ID"]);
                        $arResult["SECTIONS"][$obType["VALUE"]]["NAME"] = $obType["VALUE_ENUM"];
                        $arResult["SECTIONS"][$obType["VALUE"]]["COUNT"] ++;
                    }
                }

                $resMarketType = $CIBlockElement->GetProperty($arFields["IBLOCK_ID"], $arFields["ID"], "sort", "asc", array("CODE" => "TYPE"));
                while ($obType = $resMarketType->GetNext()) {
                    if (count($obType) > 0) {
                        $arResult["SERVICES"][$i][$obType["CODE"]][] = $obType["VALUE_XML_ID"];
                    }
                }

                $arResult["SERVICES"][$i]["PRICE"] = number_format($arResult["SERVICES"][$i]["PRICE"], 0, ".", " ");

                $i++;
            }
        }

        foreach ($arResult["SERVICES"] as $i => $arService){

            switch ($arService["TOP_SECTION_CODE"]){
                case "trading_signals":
                    $arResult["SERVICES"][$i]["DETAIL_PAGE_URL"] = "/experts/".$arResult["EXPERT"]["LOGIN"]."/".$arService["TOP_SECTION_CODE"]."/".$arResult["SERVICES"][$i]["CODE"]."/";
                    break;

                case "reports":
                    $arResult["SERVICES"][$i]["DETAIL_PAGE_URL"] = "/experts/".$arResult["EXPERT"]["LOGIN"]."/".$arService["TOP_SECTION_CODE"]."/";
                    break;

                default:
                    $arResult["SERVICES"][$i]["DETAIL_PAGE_URL"] = "/".strtolower($arService["TYPE"][0])."/training/".$arService["TOP_SECTION_CODE"]."_".$arService["CODE"]."/";
                    break;
            }
        }

//        sort($arResult["SERVICES"]);
//        shuffle($arResult["SERVICES"]);

        return $arResult;
    }

    public function getExpertPhotos($expertID = 0, $resize = false, $width = 500){
        $arResult["EXPERT_ID"] = $expertID;
        $arResult["RESIZE"] = $resize;
        $arResult["WIDTH"] = $width;

        if(isset($arResult["EXPERT_ID"])){

            $arResult["FILTER"] = array(
                "GROUPS_ID" => $this->expertGroup,
                "ACTIVE" => "Y",
                "ID" => $arResult["EXPERT_ID"]
            );

            $rsUsers = \CUser::GetList($by = "UF_SORT", $order = "ASC", $arResult["FILTER"], array("SELECT" => array("UF_PHOTOS")));
            while ($arUser = $rsUsers->Fetch()) {
                $arResult["GET"] = array(
                    "ID" => $arUser["ID"],
                    "PHOTOS" => $arUser["UF_PHOTOS"]
                );
            }
        }

        if($arResult["RESIZE"]){
            foreach ($arResult["GET"]["PHOTOS"] as $key => $arPhoto){
                $arResult["GET"]["PHOTOS"][$key] = array(
                    "ID" => $arPhoto,
                    "PATH" => \CFile::GetPath($arPhoto),
                    "RESIZED" => \CFile::ResizeImageGet($arPhoto, array("width" => $arResult["WIDTH"], "height" => $arResult["WIDTH"]), BX_RESIZE_IMAGE_PROPORTIONAL_ALT, true, false, true)["src"]
                );
            }
        }

        return $arResult["GET"]["PHOTOS"];
    }

    /**
     * @param $expertID
     * @return mixed
     * @throws \Bitrix\Main\LoaderException
     */

    public static function getExpertTopic($expertID){
        global $USER;

        $arResult["ID"] = $expertID;
        $arResult["CURRENT_USER_ID"] = $USER->GetID();

        if(\Bitrix\Main\Loader::IncludeModule("iblock")){
            $i=0;
            $arSelect = Array("ID", "NAME", "DETAIL_PAGE_URL", "PROPERTY_SECTION_PUBLISH");
            $arFilter = Array("IBLOCK_ID" => 3, "ACTIVE" => "Y", "PROPERTY_EXPERT_CATALOG" => $arResult["ID"]);
            $res = \CIBlockElement::GetList(Array("ID" => "ASC"), $arFilter, false, false, $arSelect);
            while ($ob = $res->GetNextElement()) {
                $arFields = $ob->GetFields();
                $arResult["ELEMENT"][$i] = $arFields;

                $arResult["ELEMENT"][$i]["SUBSCRIBE"] = false;
                if($arResult["CURRENT_USER_ID"]) {
                    $entity_data_class = \AOFX\CUtils::GetEntityDataClass(1);
                    $rsData = $entity_data_class::getList(array(
                        "select" => array("UF_TEMA_CURRENT", "UF_DATATO_CURRENT", "ID"),
                        "order" => array("UF_DATATO_CURRENT" => "ASC"),
                        "limit" => 10000,
                        "filter" => array(
                            "UF_TEMA_CURRENT" => $arFields["ID"],
                            ">UF_DATATO_CURRENT" => date("d.m.Y H:i:s"),
                            "UF_USER_CURRENT" => $arResult["CURRENT_USER_ID"]
                        )
                    ));
                    while ($el = $rsData->fetch()) {
                        $arResult["ELEMENT"][$i]["SUBSCRIBE"] = true;
                    }
                }

                $arResult["ELEMENT"][$i]["POSTS_COUNT"] = \CIBlockSection::GetSectionElementsCount($arFields["PROPERTY_SECTION_PUBLISH_VALUE"], Array("CNT_ACTIVE"=>"Y"));

                $i++;
            }
        }

        return $arResult;
    }

    /**
     * @param $expertID
     * @param int $nPageSize
     * @param $type
     * @return mixed
     * @throws \Bitrix\Main\LoaderException
     */

    public function getExpertNewsList($expertID, $nPageSize = 0, $type){
        $arResult["ID"] = $expertID;

        if(\Bitrix\Main\Loader::IncludeModule("iblock")){

            $CIBlockElement = new \CIBlockElement();

            $arResult["SECTIONS"][0] = array(
                "ID" => 0,
                "CODE" => "all",
                "NAME" => "Вся аналитика",
            );

            $CUtils = new \AOFX\CUtils();
            $i=0;
            $arSelect = Array("ID", "ACTIVE_FROM", "PREVIEW_TEXT", "NAME", "DETAIL_PAGE_URL", "SHOW_COUNTER", "PROPERTY_EXPERT", "IBLOCK_ID");
            $arFilter = Array("IBLOCK_TYPE" => "ANALYTICS_UIKIT", "!IBLOCK_ID" => array(77, 78, 84), "ACTIVE" => "Y", "ACTIVE_DATE" => "Y", "PROPERTY_EXPERT" => $arResult["ID"]);

            $res = \CIBlockElement::GetList(Array("ACTIVE_FROM" => "DESC"), $arFilter, false, array("nPageSize" => $nPageSize), $arSelect);
            while ($ob = $res->GetNextElement()) {
                $arFields = $ob->GetFields();

                $resMarketType = $CIBlockElement->GetProperty($arFields["IBLOCK_ID"], $arFields["ID"], "sort", "asc", array("CODE" => "TYPE"));
                while ($obType = $resMarketType->GetNext())
                {
                    if($obType["VALUE"])
                        $arResult["ELEMENT"][$i][$obType["CODE"]] = $obType["VALUE_XML_ID"];
                }
                if(isset($type) && strtolower($type) != strtolower($arResult["ELEMENT"][$i]["TYPE"])){
                    unset($arResult["ELEMENT"][$i]);
                    continue;
                }


                $arResult["SECTIONS"][strtolower($arFields["IBLOCK_CODE"])] = array(
                    "ID" => $arFields["IBLOCK_ID"],
                    "CODE" => strtolower($arFields["IBLOCK_CODE"]),
                    "NAME" => \CIBlock::GetByID($arFields["IBLOCK_ID"])->Fetch()["NAME"],
                );

                $date["CLASS"] = "";

                $date["DATE"] = ConvertDateTime($arFields["ACTIVE_FROM"], "DD.MM.YYYY", "ru");
                if($date["DATE"] == date("d.m.Y")){
                    $date["DATE"] = "Сегодня";
                    $date["CLASS"] = "today";
                }else{
                    $date["DATE"] = $CUtils->editData($arFields["ACTIVE_FROM"], true);
                }
                $date["TIME"] = ConvertDateTime($arFields["ACTIVE_FROM"], "HH:MI", "ru");

                $arExpert = $this->getExpertData($arFields["PROPERTY_EXPERT_VALUE"]);

                $arResult["ELEMENT"][$i] = array(
                    "ID" => $arFields["ID"],
                    "ACTIVE_FROM" => $arFields["ACTIVE_FROM"],
                    "PREVIEW_TEXT" => $arFields["PREVIEW_TEXT"],
                    "NAME" => $arFields["NAME"],
                    "CODE" => $arFields["CODE"],
                    "SHOW_COUNTER" => $arFields["SHOW_COUNTER"],
                    "IBLOCK_CODE" => $arFields["IBLOCK_CODE"],
                    "LOCK" => \AOFX\CElement::getElementPropValue($arFields["ID"], "LOCK", "VALUE")["VALUE"],
                );
                $arResult["ELEMENT"][$i]["DATE"] = $date;

                $arResult["ELEMENT"][$i]["PREVIEW_TEXT"] = $arFields["PREVIEW_TEXT"];
                $arResult["ELEMENT"][$i]["EXPERT"]["PHOTO"] = $arExpert["PERSONAL_PHOTO"];
                $arResult["ELEMENT"][$i]["EXPERT"]["PHOTO_RESIZE"] = $arExpert["PERSONAL_PHOTO_RESIZE"];
                $arResult["ELEMENT"][$i]["EXPERT"]["CODE"] = $arExpert["LOGIN"];
                $arResult["ELEMENT"][$i]["EXPERT"]["FIO"] = $arExpert["FIO"];
                $arResult["ELEMENT"][$i]["TOP_SECTION_CODE"] = strtolower($arFields["IBLOCK_CODE"]);

                $resMarketType = $CIBlockElement->GetProperty($arFields["IBLOCK_ID"], $arFields["ID"], "sort", "asc", array("CODE" => "EXPERT_SERVICE"));
                while ($obType = $resMarketType->GetNext())
                {
                    if($obType["VALUE"])
                        $arResult["ELEMENT"][$i][$obType["CODE"]] = \CIBlockElement::GetByID($obType["VALUE"])->Fetch()["CODE"];
                }

                $arResult["ELEMENT"][$i]["DETAIL_PAGE_URL"] = "/experts/" . $arExpert["LOGIN"] . "/" . strtolower($arFields["IBLOCK_CODE"]) . "/";

                if(strtolower($arFields["IBLOCK_CODE"]) == "reports") {
                    $arResult["ELEMENT"][$i]["DETAIL_PAGE_URL"] .= $arFields["CODE"]."/";
                }
                if(strtolower($arFields["IBLOCK_CODE"]) == "video") {
                    $arResult["ELEMENT"][$i]["DETAIL_PAGE_URL"] .= $arFields["CODE"]."/";
                }
                if(strtolower($arFields["IBLOCK_CODE"]) == "trading_signals") {
                    $arResult["ELEMENT"][$i]["DETAIL_PAGE_URL"] .= $arResult["ELEMENT"][$i]["EXPERT_SERVICE"]."/";
                }

                $i++;
            }
        }

        return $arResult;
    }

    /**
     * @param $expertID
     * @return string
     * @throws \Bitrix\Main\LoaderException
     */

    public static function getExpertPostsGraph($expertID){
        $arResult["ID"] = $expertID;

        if(\Bitrix\Main\Loader::IncludeModule("iblock")){

            $cache = \Bitrix\Main\Data\Cache::createInstance();

            if ($cache->initCache(0, "CExpert::getExpertPostsGraph(".$expertID.")", "CExpert")) { // проверяем кеш и задаём настройки
                $arResult = $cache->getVars(); // достаем переменные из кеша
            }
            elseif ($cache->startDataCache()) {

                $arSelect = Array("ID", "ACTIVE_FROM");
                $arFilter = Array("IBLOCK_ID" => 1, "ACTIVE" => "Y", "PROPERTY_expert" => $arResult["ID"]);
                $res = \CIBlockElement::GetList(Array("ACTIVE_FROM" => "ASC"), $arFilter, false, false, $arSelect);
                while ($ob = $res->GetNextElement()) {
                    $arFields = $ob->GetFields();
                    $arResult["ELEMENT"][] = $arFields;
                }

                foreach ($arResult["ELEMENT"] as $arItem){
                    if($arItem["ACTIVE_FROM"] != "00.00.0000") {
                        $key = false;
                        $key = ConvertDateTime($arItem["ACTIVE_FROM"], "MM.YYYY", "ru");
                        if(!$arResult["TEMP"][$key]){
                            $arResult["TEMP"][$key]["VALUE"] = 0;
                            $arResult["TEMP"][$key]["DATE"] = ConvertDateTime($arItem["ACTIVE_FROM"], "01.MM.YYYY", "ru");
                        }
                        $arResult["TEMP"][$key]["VALUE"] += 1;
                    }
                }

                foreach ($arResult["TEMP"] as $arItem){
                    $arResult["GRAPH"][] = $arItem;
                }

//                sort($arResult["GRAPH"]);

                $cache->endDataCache($arResult);
            }
        }

        return json_encode($arResult["GRAPH"]);
    }

    /**
     * @param $expertID
     * @return mixed
     */

    public function getExpertData($expertID){
        $arResult["ID"] = $expertID;

        $rsUser = \CUser::GetByID($arResult["ID"]);
        $arUser = $rsUser->Fetch();

        $arUserTypes = array();
        if($arUser["UF_TYPE"]) {
            $obEnum = new \CUserFieldEnum;
            $rsEnum = $obEnum->GetList(array(), array("ID" => $arUser["UF_TYPE"]));
            while ($arEnum = $rsEnum->GetNext()) {
                $arUserTypes[] = $arEnum["VALUE"];
            }
        }

        $arResult = array(
            "ID" => $arUser["ID"],
            "LOGIN" => $arUser["LOGIN"],
            "FIO" => $arUser["NAME"]." ".$arUser["LAST_NAME"],
            "PERSONAL_PHOTO" => \CFile::GetPath($arUser["PERSONAL_PHOTO"]),
            "PERSONAL_PHOTO_RESIZE" => \CFile::ResizeImageGet($arUser["PERSONAL_PHOTO"], Array("width" => 120, "height" => 120), BX_RESIZE_IMAGE_PROPORTIONAL_ALT)["src"],
            "PROFILE_PHOTO" => \CFile::GetPath($arUser["UF_PHOTO"]),
            "WORK_POSITION" => $arUser["WORK_POSITION"],
            "WORK_NOTES" => $arUser["WORK_NOTES"],
            "UF_EXPIRIENCE" => $arUser["UF_EXPIRIENCE"],
            "UF_EDUCATION" => $arUser["UF_EDUCATION"],
            "SHOW_STATS" => $arUser["UF_SHOW_STATS"],
            "UF_HOBBY" => $arUser["UF_HOBBY"],
            "TYPE" => $arUserTypes,
            "CERTS" => $arUser["UF_CERTS"]
        );

        return $arResult;
    }

    /**
     * @param $expertID
     * @param string $sort
     * @return bool|string
     * @throws \Bitrix\Main\LoaderException
     */

    public static function getPostDate($expertID, $sort = "DESC"){
        $arResult["ID"] = $expertID;

        if(\Bitrix\Main\Loader::IncludeModule("iblock")){
            $arSelect = array("ID", "NAME", "ACTIVE_FROM");
            $arFilter = array("IBLOCK_ID" => 1, "ACTIVE" => "Y", "PROPERTY_expert" => $arResult["ID"]);
            $res = \CIBlockElement::GetList(array("ACTIVE_FROM" => $sort), $arFilter, false, array("nPageSize" => 1), $arSelect);
            while($ob = $res->GetNextElement())
            {
                $arFields = $ob->GetFields();
                $arResult["RESULT"] = $arFields["ACTIVE_FROM"];
            }
        }

        if($arResult["RESULT"] != "00.00.0000") {
            return ConvertDateTime($arResult["RESULT"], "DD.MM.YYYY HH:MI", "ru");
        }else{
            return $arResult["RESULT"];
        }
    }

    /**
     * @param $expertID
     * @return mixed
     * @throws \Bitrix\Main\LoaderException
     */

    public static function getSubscribers($expertID){
        $arResult["ID"] = $expertID;
        $arResult["COUNT"] = 0;

        $cache = \Bitrix\Main\Data\Cache::createInstance();

        if ($cache->initCache(7200, "CExpert::getSubscribers(".$expertID.")", "CExpert")) { // проверяем кеш и задаём настройки
            $arResult = $cache->getVars(); // достаем переменные из кеша
        }
        elseif ($cache->startDataCache()) {

            if(\Bitrix\Main\Loader::IncludeModule("iblock")) {
                $arSelect = Array("ID", "NAME");
                $arFilter = Array("IBLOCK_ID" => 3, "ACTIVE" => "Y", "PROPERTY_EXPERT_CATALOG" => $arResult["ID"]);
                $res = \CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
                while ($ob = $res->GetNextElement()) {
                    $arFields = $ob->GetFields();
                    $arResult["ELEMENT"][] = $arFields["ID"];
                }
            }
            if(\Bitrix\Main\Loader::IncludeModule("highloadblock")) {
                $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById(1)->fetch();
                $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
                $entity_data_class = $entity->getDataClass();

                $rsData = $entity_data_class::getList(array(
                    "select" => array("ID", "UF_TEMA_CURRENT", "UF_DATATO_CURRENT"),
                    "order" => array("ID" => "ASC"),
                    "filter" => array('UF_TEMA_CURRENT' => $arResult["ELEMENT"], ">UF_DATATO_CURRENT" => date("d.m.Y H:i:s"))
                ));

                while($arData = $rsData->Fetch())
                {
                    $arResult["SUBSCRIBERS"][] = $arData;
                }

                $arResult["COUNT"] = count($arResult["SUBSCRIBERS"]);
                unset($arResult["SUBSCRIBERS"]);
            }
            $cache->endDataCache($arResult);
        }

        return $arResult;
    }

    /**
     * @param $expertID
     * @return mixed
     * @throws \Bitrix\Main\LoaderException
     */

    public static function postsCount($expertID){
        $arResult["ID"] = $expertID;
        $arResult["COUNT"] = 0;

        if(\Bitrix\Main\Loader::IncludeModule("iblock")){

            $cache = \Bitrix\Main\Data\Cache::createInstance();

            if ($cache->initCache(7200, "CExpert::postsCount(".$expertID.")", "CExpert")) { // проверяем кеш и задаём настройки
                $arResult = $cache->getVars(); // достаем переменные из кеша
            }
            elseif ($cache->startDataCache()) {

                $arSelect = Array("ID", "NAME", "PROPERTY_expert");
                $arFilter = Array("IBLOCK_ID" => 1, "ACTIVE" => "Y", "PROPERTY_expert" => $arResult["ID"]);
                $res = \CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
                while ($ob = $res->GetNextElement()) {
                    $arFields = $ob->GetFields();
                    $arResult["ELEMENT"][] = $arFields;
                }
                $arResult["COUNT"] = count($arResult["ELEMENT"]);
                unset($arResult["ELEMENT"]);

                $cache->endDataCache($arResult);
            }
        }

        return $arResult;
    }

    /**
     * @param bool $typeExperts
     * @return array
     */

    public function All($typeExperts = false){

        $arResult = array();

        $filter = Array(
            "GROUPS_ID" => $this->expertGroup,
            "ACTIVE" => "Y"
        );
        if($typeExperts){
            $filter["!UF_TYPE"] = null;
        }
        $rsUsers = \CUser::GetList($by = "UF_SORT", $order = "ASC", $filter, array("SELECT" => array("UF_TYPE")));
        while ($arUser = $rsUsers->Fetch()) {

            $arUserTypes = array();
            $obEnum = new \CUserFieldEnum;
            $rsEnum = $obEnum->GetList(array(), array("ID" => $arUser["UF_TYPE"]));
            while($arEnum = $rsEnum->GetNext()){
                $arUserTypes[] = $arEnum["VALUE"];
            }

            $arResult["EXPERTS"][] = array(
                "ID" => $arUser["ID"],
                "NAME" => $arUser["NAME"],
                "LAST_NAME" => $arUser["LAST_NAME"],
                "SECOND_NAME" => $arUser["SECOND_NAME"],
                "CODE" => ($arUser["LOGIN"]),
                "PICTURE" => $arUser["PERSONAL_PHOTO"],
                "WORK_POSITION" => $arUser["WORK_POSITION"],
                "WORK_NOTES" => $arUser["WORK_NOTES"],
                "TYPE" => $arUserTypes
            );
        }

        return $arResult;
    }

}