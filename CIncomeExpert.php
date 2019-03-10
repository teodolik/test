<?php

/**
 * namespace AOFX
 */

namespace AOFX;

/**
 * Class CExpert
 * @package AOFX
 */

class CIncomeExpert {

    var $now = "";
    var $endSubscribe = 0;
    public function __construct(){
    }
    /**
     *
     * @return array
     * @throws \Bitrix\Main\LoaderException
     */

    public function getGroup(){

        $arResult = array();
        $rsGroups = \CGroup::GetList(
            $by = "ID",
            $order = "ASC",
            array("NAME" => "Сигналы%")
        );
        while($arGroups = $rsGroups->Fetch())
        {
            $arResult["GROUP_ID"][] = $arGroups["ID"];
            $arResult["GROUP"][$arGroups["ID"]] = array(
                "ID" => $arGroups["ID"],
                "NAME" => $arGroups["NAME"],
            );
        }
        return $arResult;
    }


    public function subscribeExperts($arGroups,$arParams){
        $CUtils = new \AOFX\CUtils();
        $now = time();
        $filter = array(
            "GROUPS_ID" => $arGroups,
//            "LOGIN" => "polevoy"
//            "LOGIN" => "wessels"

        );
        $rsUsers = \CUser::GetList($by="personal_country", $order="desc", $filter); // выбираем пользователей
        $i=0;
        while ($arUser = $rsUsers->Fetch()) {
            $arResult["USER"][$i] = array(
                "ID" => $arUser["ID"],
                "LOGIN" => $arUser["LOGIN"],
                "EMAIL" => $arUser["EMAIL"],
                "NAME" => $arUser["NAME"],
                "LAST_NAME" => $arUser["LAST_NAME"],
            );

            $res = \CUser::GetUserGroupList($arResult["USER"][$i]["ID"]);

            $q=0;

            while ($arGroup = $res->Fetch()){

                if(!empty($arGroup["DATE_ACTIVE_TO"]) && in_array($arGroup["GROUP_ID"], $arGroups)){

                    $arResult["USER"][$i]["GROUPS"][$q] = $arGroup;
                    $dateActiveFROM = MakeTimeStamp($arGroup["DATE_ACTIVE_TO"]);
                    $interval = ($dateActiveFROM-$now)/86400;
                    $interval = round($interval);

                    if($interval <= 14 && $interval > 0){
                        $arResult["USER"][$i]["GROUPS"][$q]["INTERVAL"] = $interval;
                        $arResult["USER"][$i]["GROUPS"][$q]["GROUP_NAME"] = $arParams[$arResult["USER"][$i]["GROUPS"][$q][GROUP_ID]][NAME];

                    }else{
                        unset($arResult["USER"][$i]["GROUPS"][$q]);
                    }

                }

                $q++;
            }

            if(!is_array($arResult["USER"][$i]["GROUPS"]) || !count($arResult["USER"][$i]["GROUPS"])){
                unset($arResult["USER"][$i]);
            }
            $i++;
        }
        return $arResult;
    }
    public function getExpertGroupId($arGroups){
        $CUtils = new \AOFX\CUtils();

        $arResult["GROUP_ID"] = $arGroups;

        if (\CModule::IncludeModule("iblock") && \CModule::IncludeModule("catalog")){
            $CCatalogProductGroups = new \CCatalogProductGroups();


            $arOrder = array();
            $arFilter = array("GROUP_ID" => $arResult["GROUP_ID"]);
            $arSelectFields = array();
            $resProductGroup = $CCatalogProductGroups->GetList($arOrder, $arFilter, false, false, $arSelectFields);
            $i=0;
            while ($arElement = $resProductGroup->Fetch()){
                if($element_id = \CCatalogSku::GetProductInfo($arElement["PRODUCT_ID"])){
                    $dbEl = \CIBlockElement::GetList(Array(), Array("ID"=>$element_id[ID]));
                    $ebEl = \CIBlockElement::GetList(Array(), Array("ID"=>$element_id[ID]));
                    if($obEl = $dbEl->GetNextElement()) {
                        $props = $obEl->GetProperties();
                        $filter = Array("ID" => $props[EXPERT][VALUE]);
                        $rsUsers = \CUser::GetList(($by = "NAME"), ($order = "desc"), $filter);
                        while ($arUser = $rsUsers->Fetch()) {

                            $arResult["ELEMENTS"][$arElement["GROUP_ID"]] = array(
                                "ID" => $element_id["ID"],
                                "NAME" => $arUser[NAME],
                                "LAST_NAME" => $arUser[LAST_NAME],
                                "EMAIL" => $arUser[EMAIL],
                                "EXPERT_LOGIN" => $arUser[LOGIN],
                                "GROUP_ID" => $arElement["GROUP_ID"],
                                "TYPE" =>  $props[TYPE][VALUE_XML_ID],
                            );
                        }
                    }
                    while($ar_fields = $ebEl->GetNext())
                    {
                        $arResult["ELEMENTS"][$arElement["GROUP_ID"]]["HREF"]  = $ar_fields[CODE];

                    }
                }
                $i++;
            }

        }
        return $arResult;
    }
    public function ExpertIncome($arResult,$arElement){
        $CUtils = new \AOFX\CUtils();

        $now = date("d.m.Y H:i:s");
        $now2 = time();
//        $CUtils->deb($arResult);
//        $CUtils->deb($arElement);
        if (\CModule::IncludeModule('highloadblock')) {
            $arHLBlock = \Bitrix\Highloadblock\HighloadBlockTable::getById(8)->fetch();
            $obEntity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($arHLBlock);
            $strEntityDataClass = $obEntity->getDataClass();
            $arResult= array_values($arResult);
            for ($i=0;$i<count($arResult);$i++){
                $arResult[$i]["GROUPS"]= array_values($arResult[$i]["GROUPS"]);

                for ($j=0;$j<count($arResult[$i]["GROUPS"]);$j++){

                    $arResult[$i]["GROUPS"][$j]["EXPERT"] = $arElement[$arResult[$i]["GROUPS"][$j]["GROUP_ID"]]["EXPERT_LOGIN"];
                    $arResult[$i]["GROUPS"][$j]["TYPE"] = $arElement[$arResult[$i]["GROUPS"][$j]["GROUP_ID"]]["TYPE"];
                    $arResult[$i]["GROUPS"][$j]["NAME"] = $arElement[$arResult[$i]["GROUPS"][$j]["GROUP_ID"]]["NAME"];
                    $arResult[$i]["GROUPS"][$j]["LAST_NAME"] = $arElement[$arResult[$i]["GROUPS"][$j]["GROUP_ID"]]["LAST_NAME"];
                    $arResult[$i]["GROUPS"][$j]["EMAIL"] = $arElement[$arResult[$i]["GROUPS"][$j]["GROUP_ID"]]["EMAIL"];

                    $arResult[$i]["GROUPS"][$j]["HREF"] = "https://ao24.io/experts/".
                        $arResult[$i]["GROUPS"][$j]["EXPERT"].
                        "/trading_signals/".$arElement[$arResult[$i]["GROUPS"][$j]["GROUP_ID"]]["HREF"]."/";


                    $rsData = $strEntityDataClass::getList(array(
                        'select' => array('ID','UF_EXPERT_LOGIN','UF_ORDER_TYPE','UF_DATE_OPEN','UF_DATE_CLOSE', 'UF_DATE_CLOSE', 'UF_TAKE_PROFIT','UF_OPEN_PRICE','UF_CLOSE_PRICE', 'UF_TYPE'),
                        'order' => array('ID' => 'ASC'),
                        'filter' =>array(
                            '!=UF_EXPERT_LOGIN' => "",
                            '!=UF_DATE_CLOSE' => "",
                            '<=UF_DATE_CLOSE' => $now,
                            '!=UF_DATE_OPEN' => "",
                            '>=UF_DATE_OPEN' => $arResult[$i]["GROUPS"][$j]["DATE_ACTIVE_FROM"],
                            '=UF_EXPERT_LOGIN'=>$arResult[$i]["GROUPS"][$j]["EXPERT"],

                        ),
                    ));
                    while ($arItem = $rsData->Fetch()) {
                        switch ($arResult[$i]["GROUPS"][$j]["TYPE"]) {
                            case "FOREX":
                                $number = 6;
                                $sprint = $number - strlen(round($arItem["UF_OPEN_PRICE"]));
                                $arItem["UF_OPEN_PRICE"] = $arItem["UF_OPEN_PRICE"] ? sprintf("%01." . $sprint . "f", $arItem["UF_OPEN_PRICE"]) : null;
                                $arItem["UF_CLOSE_PRICE"] = $arItem["UF_CLOSE_PRICE"] ? sprintf("%01." . $sprint . "f", $arItem["UF_CLOSE_PRICE"]) : null;
                                break;
                            case "STOCKS":
                                $number = 5;
                                if (round($arItem["UF_OPEN_PRICE"]) < 1) $number = 5;
                                if (round($arItem["UF_OPEN_PRICE"]) >= 1) $number = 4;
                                if (round($arItem["UF_OPEN_PRICE"]) >= 100) $number = 5;
                                if (round($arItem["UF_OPEN_PRICE"]) >= 1000) $number = 6;

                                $sprint = $number - strlen(round($arItem["UF_OPEN_PRICE"]));
                                $arItem["UF_OPEN_PRICE"] = $arItem["UF_OPEN_PRICE"] ? sprintf("%01." . $sprint . "f", $arItem["UF_OPEN_PRICE"]) : null;
                                $arItem["UF_CLOSE_PRICE"] = $arItem["UF_CLOSE_PRICE"] ? sprintf("%01." . $sprint . "f", $arItem["UF_CLOSE_PRICE"]) : null;
                                break;
                            case "CRYPTO":
                                $arItem["UF_OPEN_PRICE"] = $arItem["UF_OPEN_PRICE"] ? sprintf("%01.8f", $arItem["UF_OPEN_PRICE"]) : null;
                                $arItem["UF_CLOSE_PRICE"] = $arItem["UF_CLOSE_PRICE"] ? sprintf("%01.8f", $arItem["UF_CLOSE_PRICE"]) : null;
                                break;
                            default:
                                break;
                        }
                        switch ($arItem["UF_ORDER_TYPE"]) {
                            case "BUY":
                            case "BUY_LIMIT":
                            case "BUY_STOP":
                                if (isset($arItem["UF_TYPE"]) && isset($arItem["UF_OPEN_PRICE"]) && isset($arItem["UF_CLOSE_PRICE"])) {
                                    $arItem["PIPS"] = 0;
                                    $arItem["PERCENT"] = 0;
                                    $arItem["PERCENT"] = (100 / $arItem["UF_OPEN_PRICE"]) * $arItem["UF_CLOSE_PRICE"] - 100;
                                    $arItem["PIPS"] = intval(str_replace(".", "", $arItem["UF_CLOSE_PRICE"])) - intval(str_replace(".", "", $arItem["UF_OPEN_PRICE"]));
                                }
                                break;
                            case "SELL":
                            case "SELL_LIMIT":
                            case "SELL_STOP":
                                if ($arItem["UF_TYPE"] && $arItem["UF_OPEN_PRICE"] && $arItem["UF_CLOSE_PRICE"]) {
                                    $arItem["PIPS"] = 0;
                                    $arItem["PERCENT"] = 0;
                                    $arItem["PERCENT"] = (100 / $arItem["UF_OPEN_PRICE"]) * $arItem["UF_CLOSE_PRICE"] - 100;
                                    $arItem["PERCENT"] = $arItem["PERCENT"] * -1;
                                    $arItem["PIPS"] = intval(str_replace(".", "", $arItem["UF_OPEN_PRICE"])) - intval(str_replace(".", "", $arItem["UF_CLOSE_PRICE"]));
                                }
                                break;
                            default:
                                break;
                        }

//                      $arResult[$i]["GROUPS"][$j]["ORDERS"][] = $arItem;
                        if ( $arItem["PIPS"] > 0 ){
                            $arResult[$i]["GROUPS"][$j]["PCOUNT"]++;
                        }elseif($arItem["PIPS"] < 0){
                            $arResult[$i]["GROUPS"][$j]["NCOUNT"]++;
                        }
                        $arResult[$i]["GROUPS"][$j]["PIPS"] += $arItem["PIPS"];
                        $arResult[$i]["GROUPS"][$j]["PERCENT"] += round($arItem["PERCENT"], 2);
                    }
                    if(empty($arResult[$i]["GROUPS"][$j]["NCOUNT"])){
                        $arResult[$i]["GROUPS"][$j]["NCOUNT"] =0;
                    }
                    $arResult[$i]["MESSAGE"][$j] = array(
                        "FROM" => $arResult[$i]["GROUPS"][$j]["EMAIL"],
                        "TO" => $arResult[$i]["EMAIL"],
                        "MESSAGE_SUBJECT" => $arResult[$i]["NAME"].", сигналы перестанут приходить мене чем через 2 недели (продлите подписку с выгодой).",
                        "NAME" => $arResult[$i]["NAME"],
                        "EXPERT_NAME" => $arResult[$i]["GROUPS"][0]["NAME"] . " " . $arResult[$i]["GROUPS"][0]["LAST_NAME"],
                        "PRODUCT_NAME" => $arResult[$i]["GROUPS"][$j]["GROUP_NAME"],
                        "DATE_END_SUBSCRIBE" => $CUtils->editData($arResult[$i]["GROUPS"][$j]["DATE_ACTIVE_TO"], true),
                        "DATE_START_SUBSCRIBE" => $CUtils->editData($arResult[$i]["GROUPS"][$j]["DATE_ACTIVE_FROM"], true),
                        "PIPSINCOME" => $arResult[$i]["GROUPS"][$j]["PIPS"],
                        "PCOUNT" => $arResult[$i]["GROUPS"][$j]["PCOUNT"],
                        "NCOUNT" => $arResult[$i]["GROUPS"][$j]["NCOUNT"],
                        "INCOME" => number_format($arResult[$i]["GROUPS"][$j]["PIPS"]/10, 0, false, " "),
                        "INCOME_RUB" => number_format(($arResult[$i]["GROUPS"][$j]["PIPS"]/10)*65, 0, false, " "),
                        "PROMO" => "SL6AFC",
                        "HREF" => $arResult[$i]["GROUPS"][$j]["HREF"],
                    );
                    $var =  MakeTimeStamp( $arResult[$i]["GROUPS"][$j]["DATE_ACTIVE_FROM"]);
                    $diff = ($now2 - $var)/86400;
                    $diff = round($diff);
                    if(($diff >= 25) && ($arResult[$i]["GROUPS"][$j]["PIPS"] > 1000)){

//                           \CEvent::SendImmediate("SUBSCRIPTION_RENEWAL", "d1", $arResult[$i]["MESSAGE"][$j]);
                    }
                }

            }

        }
       
        #FROM#
        #TO#
        #MESSAGE_SUBJECT#
        #NAME#
        #HREF#
        #EXPERT_NAME#
        #PRODUCT_NAME#
        #DATE_END_SUBSCRIBE#z
        #DATE_START_SUBSCRIBE#
        #PCOUNT#¬
        #NCOUNT#
        #PIPSINCOME#
        #INCOME#
        #INCOME_RUB#
        #PROMO#

        $CUtils->deb($arResult);
        return $arResult;

    }

}



