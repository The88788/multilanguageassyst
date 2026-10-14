<?php
// Copyright (c) 2026 Владислав, the88788.com.
// SPDX-License-Identifier: LGPL-3.0-only
class shopMultilanguageassystLanguages
{
    private static function settings() { return new waAppSettingsModel(); }
    public static function source() { return self::settings()->get(array('shop','multilanguageassyst'),'source_language','ru_RU'); }
    public static function all() {
        $saved=json_decode(self::settings()->get(array('shop','multilanguageassyst'),'languages',''),true);
        return is_array($saved) && $saved ? $saved : array('ru_RU'=>'Русский','kk_KZ'=>'Қазақша');
    }
    public static function targets() { $all=self::all();unset($all[self::source()]);return $all; }
    public static function target($code=null) { if($code===null){$keys=array_keys(self::targets());$code=$keys?reset($keys):null;}self::assertTarget($code);return $code; }
    public static function assertTarget($code) { if(!is_string($code)||!isset(self::targets()[$code])){throw new waException(shopMultilanguageassystBackendLocale::t('Добавьте язык перевода во вкладке «Языки»'),400);} }
    public static function remove($code) {
        shopMultilanguageassystContent::assertNotTemplate();
        $all=self::all();
        if($code===self::source()){throw new waException(shopMultilanguageassystBackendLocale::t('Нельзя удалить язык оригинала'),400);}
        if(!is_string($code)||!isset($all[$code])){throw new waException(shopMultilanguageassystBackendLocale::t('Язык уже удалён или не найден'),400);}
        unset($all[$code]);
        self::settings()->set(array('shop','multilanguageassyst'),'languages',json_encode($all));
    }
    public static function save($code,$name,$sourceCode,$sourceName) {
        shopMultilanguageassystContent::assertNotTemplate();
        foreach(array($code,$sourceCode) as $locale){if(!is_string($locale)||!preg_match('/^[a-z]{2,3}_[A-Z]{2}$/',$locale)){throw new waException(shopMultilanguageassystBackendLocale::t('Код языка должен иметь вид en_US или kk_KZ'),400);}}
        foreach(array($name,$sourceName) as $label){if(!is_string($label)||trim($label)===''||mb_strlen($label)>80){throw new waException(shopMultilanguageassystBackendLocale::t('Укажите название языка до 80 символов'),400);}}
        if($code===$sourceCode){throw new waException(shopMultilanguageassystBackendLocale::t('Язык оригинала и перевода должны различаться'),400);}
        $all=self::all();if(!isset($all[$code])&&count($all)>=30){throw new waException(shopMultilanguageassystBackendLocale::t('Максимум 30 языков'),400);}
        $all[$code]=trim($name);$all[$sourceCode]=trim($sourceName);
        self::settings()->set(array('shop','multilanguageassyst'),'languages',json_encode($all));
        self::settings()->set(array('shop','multilanguageassyst'),'source_language',$sourceCode);
    }
}
