<?php
// Copyright (c) 2026 Владислав, the88788.com.
// SPDX-License-Identifier: LGPL-3.0-only
class shopMultilanguageassystPluginBackendSaveController extends waJsonController
{
    public function execute()
    {
        shopMultilanguageassystContent::assertPost();
        $type = waRequest::post('entity_type', '', 'string');
        $id = waRequest::post('entity_id', 0, 'int');
        $values = waRequest::post('translations', array());
        $hashes = waRequest::post('source_hashes', array());
        if (!is_array($values) || !is_array($hashes) || $id <= 0) { throw new waException(shopMultilanguageassystBackendLocale::t('Некорректный запрос'), 400); }
        try {
        $language=shopMultilanguageassystLanguages::target(waRequest::post('target_language',null,'string'));
        $features=waRequest::post('features',array());
        if(!is_array($features)){throw new waException(shopMultilanguageassystBackendLocale::t('Некорректные характеристики'),400);}
        if($type==='product'){shopMultilanguageassystContent::saveProduct($id,$language,$values,$hashes,$features);}
        else{shopMultilanguageassystContent::save($type,$id,$language,$values,$hashes);}
        shopMultilanguageassystJournal::record(shopMultilanguageassystBackendLocale::t('Сохранение'),'success',$type.' #'.$id.shopMultilanguageassystBackendLocale::t(': перевод сохранён'));
        $this->response = array('saved' => true);
        } catch (waException $e) { shopMultilanguageassystJournal::record(shopMultilanguageassystBackendLocale::t('Сохранение'),'error',$e->getMessage()); $this->errors[] = $e->getMessage(); }
    }
}

