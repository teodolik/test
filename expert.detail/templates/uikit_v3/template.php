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

$marketString = (count($arResult["EXPERT"]["TYPE"]) > 0) ? implode(", ", $arResult["EXPERT"]["TYPE"]) : "";
$arTile = $arResult["EXPERT"]["FIO"].", ведущий финансовый эксперт; специализация по рынкам – ".$marketString." | Аналитика Онлайн";
$arDescription = $arResult["EXPERT"]["FIO"]." – один из самых опытных финансовых экспертов компании. ".$arResult["EXPERT"]["FIO"]." специализируется на следующих финансовых рынках: ".$marketString.". Здесь вы можете посмотреть последние публикации эксперта, а также подписаться на торговые сигналы и пройти обучение у аналитика.";

$APPLICATION->SetTitle($arTile);
$APPLICATION->SetPageProperty("title", $arTile);
$APPLICATION->SetPageProperty("description", str_replace("?", "", $arDescription));

$CExpert = new \AOFX\CExpert();
$CUtils = new \AOFX\CUtils();
$CTags = new \AOFX\CTags();

$APPLICATION->AddChainItem("Эксперт ".$arResult["EXPERT"]["FIO"]);
?>


    <div id="expert" class="block">
        <div class="uk-container uk-container-large">
            <div uk-grid class="uk-grid-small uk-flex uk-flex-middle">
                <div class="uk-width-2-3@l">
                    <?php
                    $APPLICATION->IncludeComponent("bitrix:breadcrumb","",Array(
                            "START_FROM" => "0",
                            "PATH" => "",
                            "SITE_ID" => "d1"
                        )
                    );
                    ?>
                    <h1><?=$arResult["EXPERT"]["FIO"]?></h1>
                    <div class="workPosition"><?=$arResult["EXPERT"]["WORK_POSITION"]?></div>
                    <div class="description"><?=$arResult["EXPERT"]["WORK_NOTES"]?></div>
                    <div class="marketTypes"><?=$CTags->getColoredTag($arResult["EXPERT"]["TYPE"])?></div>
                </div>
                <div class="uk-width-1-3@l">
                    <img class="expertPhoto" src="<?=$arResult["EXPERT"]["PROFILE_PHOTO"]?>">
                </div>
            </div>
        </div>
    </div>

<?php
$APPLICATION->IncludeComponent(
    "aofx:expert.news.list",
    "uikit_v3-slider",
    array(
        "EXPERT_ID" => $arResult["EXPERT"]["ID"],
        "SHOW_FILTER" => true,
        "COUNT" => 300,
        "TITLE" => "Лента публикаций эксперта",
        "BLOCK_CLASS" => "grey"
    )
);
?>

<?php
$APPLICATION->IncludeComponent(
    "aofx:expert.trading.stats",
    "uikit_v3-widget",
    array(
        "EXPERT_ID" => $arResult["EXPERT"]["ID"]
    )
);
?>
<?php
$APPLICATION->IncludeComponent(
    "aofx:banners.expert.book",
    "uikit_v3",
    array(),
    false
);
?>

<?php
$APPLICATION->IncludeComponent(
    "aofx:expert.service.list",
    "uikit_v3",
    array(
        "EXPERT_ID" => $arResult["EXPERT"]["ID"]
    )
);
?>
<?if(count($arResult["EXPERT"]["CERTS"]) > 0):?>
    <div class="block">
        <div class="uk-container uk-container-large">
            <h2>Сертификаты эксперта</h2>

            <div id="expertCertificate" uk-grid class="uk-grid-math uk-flex uk-flex-middle uk-flex-center">
                <?foreach ($arResult["EXPERT"]["CERTS"] as $key => $arCert):?>
                    <div class="uk-width-1-4@m">
                        <div class="background">
                            <a class="" href="#modal-media-certificate-<?=$key?>" uk-toggle style="background-color: transparent !important; border: 0">
                                <img src="<?=$arCert["THUMB"]?>">
                            </a>

                            <div id="modal-media-certificate-<?=$key?>" class="uk-flex-top" uk-modal>
                                <div class="uk-modal-dialog uk-width-auto uk-margin-auto-vertical">
                                    <button class="uk-modal-close-outside" type="button" uk-close></button>
                                    <img src="<?=$arCert["MAX"]?>">
                                </div>
                            </div>
                        </div>
                    </div>
                <?endforeach;?>
            </div>
        </div>
    </div>
<?endif;?>

<?php
$APPLICATION->IncludeComponent(
    "aofx:reviews.list",
    "uikit_v3-slider-course",
    array(
        "EXPERT_ID" => $arResult["EXPERT"]["ID"],
        "COUNT" => 50
    )
);
?>

<?php
//$CUtils->deb($arResult);