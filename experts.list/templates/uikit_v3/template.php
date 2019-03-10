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
$CExpert = new \AOFX\CExpert();
$CTags = new \AOFX\CTags();
$CSubscribe = new \AOFX\CSubscribe();
?>

<div class="block grey">
    <div class="uk-container uk-container-large">

        <h1>Аналитика Онлайн - Эксперты</h1>

        <div id="experts" uk-grid class="uk-grid">
            <?foreach ($arResult["EXPERTS"] as $arExpert):?>
            <div class="uk-width-1-3@l">
                <div class="expert uk-box-shadow-medium">
                    <div class="avatar" style="background-image: url('<?=$arExpert["PICTURE"]?>')"></div>
                    <div class="name">
                        <a href="/experts/<?=$arExpert["CODE"]?>/"><?=$arExpert["LAST_NAME"]?> <?=$arExpert["NAME"]?> <?=$arExpert["SECOND_NAME"]?></a>
                    </div>
                    <div class="description"><?=$arExpert["WORK_POSITION"]?></div>
                    <div class="marketTypes"><?=$CTags->getColoredTag($arExpert["TYPE"])?></div>
                    <div uk-grid class="info uk-grid-small uk-flex-right">
                        <div class="uk-width-1-3@l uk-hidden"><span uk-icon="icon: file-edit; ratio: 0.8"></span> Публикаций: <?=$CExpert->postsCount($arExpert["ID"])["COUNT"]?></div>
                        <div class="uk-width-1-3@l"><span uk-icon="icon: users; ratio: 0.8"></span> Подписчики: <?=$CSubscribe->checkExpertSubscribe($arExpert["ID"])["USER_COUNT"]?></div>
                    </div>
                </div>
            </div>
            <?endforeach;?>
        </div>

    </div>
</div>

<div class="block">
    <div class="uk-container uk-container-large">
        <h2>Обучение торговле от ведущих экспертов рынка Форекс</h2>

        <div uk-grid class="uk-grid">
            <div class="uk-width-1-2@l">
                Аналитики Академии трейдинга - это высококвалифицированные профессионалы, которые любят и знают финансовые рынки, и стараются помочь каждому начинающему трейдеру добиться успехов в торговле и получить опыт и достигнуть финансовой независимости.            </div>

            <div class="uk-width-1-2@l">
                Многие из аналитиков компании являются экспертами уникального уровня, предоставляющие избыточные знания по конкретным инструментам. Это не только позволяет выбрать занятия с определенными преподавателями, но и дает возможность заполнить пробел в знаниях.            </div>
        </div>

    </div>
</div>

<?php
//deb($arParams);
//$CUtils->deb($arResult);