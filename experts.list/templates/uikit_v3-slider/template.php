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
$CUtils = new \AOFX\CUtils();
$CTags = new \AOFX\CTags();
?>
    <div class="block yellow" style="background-image: url('/local/images/block_round_bg.png')">
        <div class="uk-container uk-container-large">
            <h2>Наши эксперты</h2>
            <div id="expertListSlider" class="expertList <?if(!$arParams["EXPERT_ID"]):?>rightBorder<?endif;?>" uk-filter="target: .uk-slider > .uk-slider-items">
                <div uk-slider="autoplay: true; autoplay-interval: 5000; finite: true" class="uk-slider">
                    <ul class="uk-slider-items uk-child-width-1-2@s uk-child-width-1-3@l <?if(!$arParams["EXPERT_ID"]):?>uk-child-width-1-3@xl<?else:?>uk-child-width-1-3@xl<?endif;?> uk-grid">
                        <?foreach ($arResult["EXPERTS"] as $arExpert):?>
                            <li>
                                <a class="element" href="/experts/<?=$arExpert["CODE"]?>/">
                                    <div class="uk-overflow-hidden picture uk-border-circle">
                                        <img src="<?=$CUtils->getWebPImage($arExpert["PICTURE"])["IMG_SRC"]?>" alt="<?=$arExpert["NAME"]?>">
                                    </div>
                                    <div class="name"><?=$arExpert["LAST_NAME"]?> <?=$arExpert["NAME"]?> <?=$arExpert["SECOND_NAME"]?></div>
                                    <div class="workPosition"><?=$arExpert["WORK_POSITION"]?></div>

                                    <div class="marketTypes"><?=$CTags->getColoredTag($arExpert["TYPE"])?></div>
                                </a>
                            </li>
                        <?endforeach;?>
                    </ul>

                    <a class="arrow" href="#" uk-slider-item="previous"><span uk-icon="icon: chevron-left; ratio: 1.5"></span></a>
                    <a class="arrow" href="#" uk-slider-item="next"><span uk-icon="icon: chevron-right; ratio: 1.5"></span></a>

                    <a class="more" href="/experts/">Показать все</a>
                </div>
            </div>
        </div>
    </div>
<?php
//$CUtils->deb($arParams);
//$CUtils->deb($arResult);