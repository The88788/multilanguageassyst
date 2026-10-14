<?php
// Copyright (c) 2026 Владислав, the88788.com.
// SPDX-License-Identifier: LGPL-3.0-only
class shopMultilanguageassystTheme
{
    private static $catalog;
    public static function catalog() {
        if(self::$catalog!==null){return self::$catalog;}
        self::$catalog=array();
        foreach(array('site','shop') as $app){
          foreach(array('insales','default') as $themeName){
            if(!is_file(wa()->getDataPath('themes/'.$themeName.'/theme.xml',true,$app,false))){continue;}
            $theme=new waTheme($themeName,$app,false,true);
            foreach($theme->getSettings() as $key=>$setting){
                $value=isset($setting['value'])?$setting['value']:null;
                if(!in_array(isset($setting['control_type'])?$setting['control_type']:'',array('text','textarea'),true)||!is_string($value)||trim(strip_tags($value))===''||!preg_match('/\p{L}/u',strip_tags($value))){continue;}
                if(!preg_match('/(?:title|text|desc|name|sign|copy|address|mode|note)/',$key)||preg_match('/(?:link|url|email|phone_num|code|micro_|price_range)/',$key)){continue;}
                $checkedValue=$themeName==='default'?str_replace('{$date}','',$value):$value;
                if(preg_match('/\{|\[(?:s)?`|<\s*(script|style|iframe|svg|object|embed)\b|\bon[a-z]+\s*=|(?:javascript|vbscript|data)\s*:/iu',$checkedValue)){continue;}
                $id=hexdec(substr(hash('sha256',$app.':'.$themeName.':'.$key),0,7))+1;
                if(isset(self::$catalog[$id])){throw new waException(shopMultilanguageassystBackendLocale::t('Конфликт идентификаторов блоков темы'),500);}
                self::$catalog[$id]=array('id'=>$id,'app'=>$app,'theme'=>$themeName,'setting'=>$key,'name'=>$app.' / '.$key,'content'=>$value);
            }
        }
        }
        return self::$catalog;
    }
    public static function settings(array $settings,$language) {
        if($language===shopMultilanguageassystLanguages::source()){return $settings;}
        $catalog=self::catalog();$rows=(new shopMultilanguageassystTranslationModel())->translations('theme',array_keys($catalog),$language);
        foreach($catalog as $id=>$item){
            $key=$item['setting'];if(isset($settings[$key],$rows[$id]['content'])&&$settings[$key]===$item['content']&&$rows[$id]['content']['source_hash']===shopMultilanguageassystContent::hash($item['content'])){$settings[$key]=$rows[$id]['content']['value'];}
        }
        return $settings;
    }
}
