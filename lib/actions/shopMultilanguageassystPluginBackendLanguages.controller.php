<?php
// Copyright (c) 2026 Владислав, the88788.com.
// SPDX-License-Identifier: LGPL-3.0-only
class shopMultilanguageassystPluginBackendLanguagesController extends waJsonController
{
    public function execute() {
        shopMultilanguageassystContent::assertPost();
        try {
            if(waRequest::post('operation','','string')==='visibility') {
                $sections=waRequest::post('sections',array());$hidden=array();
                foreach(shopMultilanguageassystLanguages::targets() as $code=>$name){foreach(array('blog','tags') as $section){if(empty($sections[$code][$section])){$hidden[$code][]=$section;}}}
                (new waAppSettingsModel())->set(array('shop','multilanguageassyst'),'hidden_sections',json_encode($hidden));
                shopMultilanguageassystJournal::record(shopMultilanguageassystBackendLocale::t('Разделы'),'success',shopMultilanguageassystBackendLocale::t('Доступность блога и тегов сохранена'));$this->response=array('saved'=>true);return;
            }
            if(waRequest::post('operation','','string')==='remove') {
                $code=waRequest::post('code','','string');
                shopMultilanguageassystLanguages::remove($code);
                shopMultilanguageassystJournal::record(shopMultilanguageassystBackendLocale::t('Языки'),'success',shopMultilanguageassystBackendLocale::t('Язык ').$code.shopMultilanguageassystBackendLocale::t(' удалён из витрины; переводы сохранены'));
                $this->response=array('saved'=>true,'removed'=>true);return;
            }
            shopMultilanguageassystLanguages::save(waRequest::post('code','','string'),waRequest::post('name','','string'),waRequest::post('source_code','','string'),waRequest::post('source_name','','string'));
            shopMultilanguageassystJournal::record(shopMultilanguageassystBackendLocale::t('Языки'),'success',shopMultilanguageassystBackendLocale::t('Настройки языков сохранены'));$this->response=array('saved'=>true);
        } catch(waException $e) { $this->errors[]=$e->getMessage(); }
    }
}
