<?php
namespace AOFX;

class CUtils {

    /**
     * CUtils constructor.
     * @param bool $ignoreComposite
     */

    function __construct($ignoreComposite = false){

    }

    /**
     * @param string $image
     * @param bool $rewrite
     * @param bool $return_img
     * @return mixed
     */

    public function getWebPImage($image = "", $rewrite = false, $return_img = false){

        if(!empty($image)) {
            $arResult["IMG_SRC"] = $image;
            $arResult["FILE"] = \CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"] . $arResult["IMG_SRC"]);
            $arResult["FILE"]["file_dir"] = str_replace($arResult["FILE"]["name"], "", $arResult["FILE"]["tmp_name"]);

//            $arResult["FILE"]["new_name"] = explode(".", $arResult["FILE"]["name"]);
//            $arResult["FILE"]["new_name"][1] = "webp";
//            $arResult["FILE"]["new_name"] = implode(".", $arResult["FILE"]["new_name"]);
            $arResult["FILE"]["new_name"] = $arResult["FILE"]["name"].".webp";

            $arResult["FILE"]["webp"] = $arResult["IMG_SRC"];

            if (!file_exists($arResult["FILE"]["file_dir"] . $arResult["FILE"]["new_name"]) || $rewrite) {
                switch ($arResult["FILE"]["type"]){
                    case "image/png";
                        $imgFromFile = imagecreatefrompng($arResult["FILE"]["tmp_name"]);
                        imagealphablending( $imgFromFile, true );
                        imagesavealpha( $imgFromFile, true );
                        break;
                    case "image/jpg";
                    case "image/jpeg";
                        $imgFromFile = imagecreatefromjpeg($arResult["FILE"]["tmp_name"]);
                        break;
                }
//                $backing = imagecreatetruecolor(imagesx($imgFromFile), imagesy($imgFromFile));
//                imagecopy($backing, $imgFromFile, 0, 0, 0, 0, imagesx($imgFromFile), imagesy($imgFromFile));

                if ($arResult["FILE"]["webp"] = imagewebp($imgFromFile, $arResult["FILE"]["file_dir"] . $arResult["FILE"]["new_name"])) {
                    imagedestroy($imgFromFile);
//                    imagedestroy($backing);
                    $arResult["FILE"]["webp"] = $arResult["FILE"]["file_dir"] . $arResult["FILE"]["new_name"];
                }
            } else {
                $arResult["FILE"]["webp"] = $arResult["FILE"]["file_dir"] . $arResult["FILE"]["new_name"];
            }

            $arResult["FILE"]["webp"] = str_replace("/home/bitrix/www", "", $arResult["FILE"]["webp"]);
        }

        return $arResult;
    }

    /**
     * @param string $DATA
     * @param bool $dropTime
     * @return string
     */

    public function editData ($DATA = "", $dropTime = false){
        if($dropTime && count(explode(" ", $DATA)) >= 2)
            $DATA = explode(" ", $DATA)[0];

        $MES = array(
            "01" => "Января",
            "02" => "Февраля",
            "03" => "Марта",
            "04" => "Апреля",
            "05" => "Мая",
            "06" => "Июня",
            "07" => "Июля",
            "08" => "Августа",
            "09" => "Сентября",
            "10" => "Октября",
            "11" => "Ноября",
            "12" => "Декабря"
        );
        $arData = explode(".", $DATA);
        $d = ($arData[0] < 10) ? substr($arData[0], 1) : $arData[0];

        $newData = $d." ".$MES[$arData[1]]." ".$arData[2];
        return $newData;
    }

    public static function getUserData($userID = false){
        global $USER;
        if(!$userID) $userID = $USER->GetID();
        $rsUser = \CUser::GetByID($userID);
        $arResult = $rsUser->Fetch();

        return $arResult;
    }

    /**
     * @param int $symbols
     * @return string
     */

    public static function genCode($symbols = 4){
        $arr = array(
            '1','2','3','4','5','6',
            '7','8','9','0');
        $pass = "";
        for($i = 0; $i < $symbols; $i++)
        {
            $index = rand(0, count($arr) - 1);
            $pass .= $arr[$index];
        }
        return $pass;
    }

    /**
     * @param $phone
     * @param $message
     * @return mixed
     */

    public function SendSMS($phone, $message){
        $smsru = new \SMSRU('6D72763F-92D6-54F3-9BEC-EDC185DD49E0'); // Ваш уникальный программный ключ, который можно получить на главной странице

        $data = new \stdClass();
        $data->from = 'AO24.IO'; // Если у вас уже одобрен буквенный отправитель, его можно указать здесь, в противном случае будет использоваться ваш отправитель по умолчанию
        $data->text = $message; // Текст сообщения

        $arResult["MESSAGE"] = $message;

        $arResult["PHONES"] = $phone;
        $arResult["PHONES_COUNT"] = count($arResult["PHONES"]);


        if(is_array($arResult["PHONES"]) && $arResult["PHONES_COUNT"] > 100){
            $i = 0;
            foreach ($arResult["PHONES"] as $key => $phone){
                $arResult["PHONES_LIST"][$i][] = $phone;
                if(($key+1) % 100 == 0) {
                    $i++;
                }
            }

            foreach ($arResult["PHONES_LIST"] as $key => $arSend){
                $arResult["SENDER_LIST"][$key] = implode(",", $arSend);
                $data->to = $arResult["SENDER_LIST"][$key];

                $arCost = $smsru->getCost($data);

                $arResult["COST"][] = array(
                    "TOTAL_COST" => $arCost->total_cost,
                    "TOTAL_SMS" => $arCost->total_sms
                );

                $sms = $smsru->send_one($data); // Отправка сообщения и возврат данных в переменную
                $arResult["SEND_STATUS"][$key] = $sms->status;
            }
        }elseif(is_array($arResult["PHONES"]) && $arResult["PHONES_COUNT"] <= 100){
            $arResult["SENDER_LIST"][0] = implode(",", $arResult["PHONES"]);
            $data->to = $arResult["SENDER_LIST"][0];

            $arCost = $smsru->getCost($data);

            $arResult["COST"][] = array(
                "TOTAL_COST" => $arCost->total_cost,
                "TOTAL_SMS" => $arCost->total_sms
            );

            $sms = $smsru->send_one($data); // Отправка сообщения и возврат данных в переменную
            $arResult["SEND_STATUS"][] = $sms->status;
        }elseif(is_string($arResult["PHONES"])){
            $arResult["SENDER_LIST"][0] = $arResult["PHONES"];
            $data->to = $arResult["SENDER_LIST"][0];

            $arCost = $smsru->getCost($data);

            $arResult["COST"][] = array(
                "TOTAL_COST" => $arCost->total_cost,
                "TOTAL_SMS" => $arCost->total_sms
            );

            $sms = $smsru->send_one($data); // Отправка сообщения и возврат данных в переменную
            $arResult["SEND_STATUS"][] = $sms->status;
            if($arResult["SEND_STATUS"][0] == "OK"){
                $arResult["CODE"] = 100;
            }
        }

        unset($arResult["PHONES"]);


        return $arResult;
    }

    /**
     * @param string $name
     * @param string $content
     * @return void
     */

    public static function addOgMeta($name = "", $content = ""){
        if(isset($name) && isset($content)) {
            \Bitrix\Main\Page\Asset::getInstance()->addString('<meta property="og:' . $name . '" content="' . $content . '" />');
        }
    }

    public static function GetEntityDataClass($HlBlockId) {

        if (empty($HlBlockId) || $HlBlockId < 1)
        {
            return false;
        }
        if(\Bitrix\Main\Loader::includeModule('highloadblock')) {
            $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($HlBlockId)->fetch();
            $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);

            $entity_data_class = $entity->getDataClass();
            return $entity_data_class;
        }else{
            return false;
        }
    }

    /**
     * @param string $channel
     * @param string $event
     * @param array $array
     * @return array
     */

    public static function AOFXWssSendMessage($channel = "log", $event = "", $array = array()){
        $arResult["CHANNEL"] = $channel;
        $arResult["SEND_DATA"] = $array;

        $app_id = '458988';
        $app_key = '724d31017abee71fd518';
        $app_secret = '95ce3d16e6e1c5a53c0b';
        $app_cluster = 'mt1';

        $pusher = new \Pusher\Pusher( $app_key, $app_secret, $app_id, array('cluster' => $app_cluster) );

        $arResult["RESULT"] = $pusher->trigger( $channel, $event, $arResult["SEND_DATA"] );

        return $arResult;
    }

    public function deb($arResult, $public = false) {
        $arResult["MCT"] = CUtils::MCT();
        global $USER;
        if ($USER->IsAdmin() && !$public) {
            echo '<pre>';
            print_r($arResult);
            echo '</pre>';
        }
        if($public) {
            echo '<pre>';
            print_r($arResult);
            echo '</pre>';
        }
    }

    /**
     * Время выполнения кода
     * @author Peter Wessels | wessels@lgorithmfx.ru
     * @return float|int|mixed
     */

    public static function MCT(){
        if (defined("MICROTIME")) {
            $arResult = microtime(true) - MICROTIME;
            $arResult = round($arResult, 5);
        }else{
            $arResult = 0;
        }

        return $arResult;
    }

    /**
     * @author Peter Wessels | wessels@lgorithmfx.ru
     * @param $Array
     * @param $Name
     * @param bool $DeleteFile
     * @param string $scriptFile
     * @param int $scriptLine
     * @param string $scriptMethod
     * @return mixed
     */

    public static function LogFile($Array, $Name, $DeleteFile = false, $scriptFile = __FILE__, $scriptLine = __LINE__, $scriptMethod = __CLASS__."::".__FUNCTION__){

        $arResult["LOG"] = $Array;

        if($DeleteFile)
            unlink("/tmp/".$Name.".log");

        $cyrrentDate = date("d.m.Y H:i:s");

        $arResult["DATETIME"] = $cyrrentDate;

        $arResult["MCT"] = CUtils::MCT();
        $arResult["FILE"] = $scriptFile;
        $arResult["LINE"] = $scriptLine;
        $arResult["METHOD"] = $scriptMethod;

        $req_dump = print_r( $arResult, true );
        $arResult["FILE"] = $fp = file_put_contents( "/tmp/".$Name.".log", $req_dump, FILE_APPEND );

        if(!@copy("/tmp/".$Name.".log", $_SERVER["DOCUMENT_ROOT"]."/local/log/".$Name.".log")) {
            $arResult["ERROR"][] = error_get_last();
        }

        return $arResult;
    }

    public static function MessageTelegram($channel = "", $message = array()){
        $arResult["CHANNEL"] = $channel;
        $arResult["MESSAGE"] = $message;
        $arResult["URL"] = "https://api.telegram.org/bot516157745:AAEbTuBzSgAoHApdFrPhq-OocMDL2uwEh8Y/sendMessage?";

        $arResult["QUERY"] = array(
            "chat_id" => "@".$arResult["CHANNEL"],
            "text" => implode("\r\n", $arResult["MESSAGE"]),
            "parse_mode" => "HTML"
        );

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $arResult["URL"],
            CURLOPT_POSTFIELDS => http_build_query($arResult["QUERY"]),
        ));

        $arResult["RESULT"] = curl_exec($curl);
        curl_close($curl);

        return $arResult;
    }
}