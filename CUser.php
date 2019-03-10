<?php
namespace AOFX;

class CUser {
    public static function getChatUserData(){
        global $USER;

        if($arResult["USER_ID"] = $USER->GetID()) {

            $rsUser = \CUser::GetByID($arResult["USER_ID"]);
            $arResult["USER"] = $rsUser->Fetch();
        }

        return $arResult;
    }

    public static function sendLead(&$arFields){
        $CUtils = new \AOFX\CUtils();

        if($arFields["EXTERNAL_AUTH_ID"] == "socservices"){
            $method = "crm.lead.add";
            $arFields = array(
                "fields" => array(
                    "TITLE" => "Регистрация пользователя на сайте https://ao24.io",
                    "NAME" => $arFields["NAME"],
                    "LAST_NAME" => $arFields["LAST_NAME"],
                    "BIRTHDATE" => $arFields["PERSONAL_BIRTHDAY"],
                    "UF_AUTH" => $arFields["ID"],
                    "STATUS_ID" => "NEW",
                    "EMAIL" => array(array("VALUE" => $arFields["EMAIL"], "VALUE_TYPE" => "WORK")),
                    "PHONE" => array(array("VALUE" => $arFields["PHONE"], "VALUE_TYPE" => "WORK")),

                    "IM" => array(array("VALUE" => $arFields["PERSONAL_WWW"], "VALUE_TYPE" => "WORK")),
                    "UF_CRM_1378628502" => date("d.m.Y H:i:s"),

//                    "UTM_SOURCE" => $arResult["FORM"]["UTM_SOURCE"],
//                    "UTM_MEDIUM" => $arResult["FORM"]["UTM_MEDIUM"],
//                    "UTM_CAMPAIGN" => $arResult["FORM"]["UTM_CAMPAIGN"],
//                    "UTM_TERM" => $arResult["FORM"]["UTM_TERM"],
//                    "UTM_CONTENT" => $arResult["FORM"]["UTM_CONTENT"],

                    "UF_IP" => $_SERVER["REMOTE_ADDR"],
                    "UF_CRM_1517301692" => "https://ao24.io".$_SERVER["REQUEST_URI"]
                ),
                "params" => array(
                    "REGISTER_SONET_EVENT" => "Y"
                )
            );
            \AOFX\CCRMWebHook::GetRequest($method, $arFields);
        }

        $CUtils->LogFile(array($arFields, $_SESSION), "OnAfterUserAdd", true);
    }

    public static function GetByID($ID)
    {
        global $USER;

        $userID = (is_object($USER)? intval($USER->GetID()): 0);
        $ID = intval($ID);
        if($userID > 0 && $ID == $userID && is_array(self::$CURRENT_USER))
        {
            $rs = new CDBResult;
            $rs->InitFromArray(self::$CURRENT_USER);
        }
        else
        {
            $rs = CUser::GetList(($by="id"), ($order="asc"), array("ID_EQUAL_EXACT"=>intval($ID)), array("SELECT"=>array("UF_*")));
            if($userID > 0 && $ID == $userID)
            {
                self::$CURRENT_USER = array($rs->Fetch());
                $rs = new CDBResult;
                $rs->InitFromArray(self::$CURRENT_USER);
            }
        }
        return $rs;
    }

}