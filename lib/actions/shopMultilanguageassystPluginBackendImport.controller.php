<?php
// Copyright (c) 2026 Владислав, the88788.com.
// SPDX-License-Identifier: LGPL-3.0-only
class shopMultilanguageassystPluginBackendImportController extends waJsonController
{
    public function execute()
    {
        shopMultilanguageassystContent::assertPost();
        $fileName=shopMultilanguageassystJournal::fileName(waRequest::post('file_name','','string'));
        try {
        $this->response = shopMultilanguageassystExchange::import(waRequest::post('translation_json', '', 'string'),
            (bool)waRequest::post('overwrite', false), (bool)waRequest::post('preview', false));
        $this->response['file_name']=$fileName;
        shopMultilanguageassystJournal::record(waRequest::post('preview',false)?shopMultilanguageassystBackendLocale::t('Проверка файла'):shopMultilanguageassystBackendLocale::t('Импорт'),'success',$this->response['language'].shopMultilanguageassystBackendLocale::t(': объектов ').$this->response['objects'].shopMultilanguageassystBackendLocale::t(', полей ').$this->response['fields'].shopMultilanguageassystBackendLocale::t(', замен ').$this->response['overwrites'],$fileName);
        } catch (waException $e) { shopMultilanguageassystJournal::record(waRequest::post('preview',false)?shopMultilanguageassystBackendLocale::t('Проверка файла'):shopMultilanguageassystBackendLocale::t('Импорт'),'error',$e->getMessage(),$fileName); $this->errors[] = $e->getMessage(); }
    }
}

