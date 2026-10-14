<?php
// Copyright (c) 2026 Владислав, the88788.com.
// SPDX-License-Identifier: LGPL-3.0-only
class shopMultilanguageassystInterface
{
    private static $catalog;
    public static function catalog() {
        if(self::$catalog===null){
            self::$catalog=json_decode(file_get_contents(dirname(__FILE__).'/../config/interface.json'),true);
            $sources=array();
            $routingFile=wa()->getConfig()->getRootPath().'/wa-config/routing.php';
            $routes=is_file($routingFile)?include $routingFile:array();
            foreach($routes as $domainRoutes){if(!is_array($domainRoutes)){continue;}foreach($domainRoutes as $route){
                if(!is_array($route)||!isset($route['app'])||$route['app']!=='shop'){continue;}
                foreach(array('title','meta_description') as $field){if(isset($route[$field])&&is_string($route[$field])){$sources[]=$route[$field];}}
            }}
            // Public navigation labels; no page bodies, customer data or orders.
            $model=new shopMultilanguageassystTranslationModel();
            // Optional callback form: only public labels, never requests or contact details.
            $plugins=wa('shop')->getConfig()->getPlugins();
            if(!empty($plugins['skcallback'])&&class_exists('shopSkcallbackDefinesModel')&&class_exists('shopSkcallbackControlsModel')){
                $defines=(new shopSkcallbackDefinesModel())->getDefines();
                foreach(array('title','text','button','title_success','text_success') as $key){foreach(preg_split('/<[^>]*>/', $defines[$key]??'') as $text){$sources[]=html_entity_decode($text,ENT_QUOTES,'UTF-8');}}
                foreach((new shopSkcallbackControlsModel())->getControlsWithTypes() as $control){
                    $sources[]=$control['title'];
                    if(in_array($control['type_name'],array('checkbox','string'),true)&&is_string($control['additional'])){foreach(preg_split('/<[^>]*>/', $control['additional']) as $text){$sources[]=html_entity_decode($text,ENT_QUOTES,'UTF-8');}}
                }
            }
            $checkoutFile=wa()->getConfig()->getRootPath().'/wa-config/apps/shop/checkout2.php';
            $collect=function($values)use(&$collect,&$sources){foreach($values as $key=>$value){if(is_array($value)){$collect($value);}elseif(is_string($value)&&in_array($key,array('name','title','label','block_name','description','hint','placeholder','empty_text','text','thankyou_header','thankyou_content','courier_name','pickuppoint_name','post_name'),true)){$sources[]=$value;}}};
            if(is_file($checkoutFile)){$collect(include $checkoutFile);}
            $defaults=wa()->getAppPath('lib/config/checkout2/default.php','shop');
            if(is_file($defaults)){$collect(include $defaults);}
            foreach(array('wa_country','wa_region') as $table){
                foreach($model->query('SELECT DISTINCT name FROM '.$table)->fetchAll() as $row){$sources[]=$row['name'];}
            }
            foreach((new shopWorkflow())->getAllStates() as $state){$sources[]=$state->getName();}
            foreach(glob(dirname(__FILE__).'/../../locale/*.php') as $file){foreach(array_keys(include $file) as $label){$sources[]=$label;}}
            foreach(waContactFields::getAll('person',true) as $field){$sources[]=$field->getName('ru_RU');}
            foreach($model->query('SELECT name,description FROM shop_plugin')->fetchAll() as $row){$sources[]=$row['name'];foreach(preg_split('/<[^>]*>/', $row['description']??'') as $text){$sources[]=html_entity_decode($text,ENT_QUOTES,'UTF-8');}}
            foreach(array('Покупатель','Телефон','Улица, дом, квартира','Информация о доставке','Информации о доставки','Регион','Сегодня','Завтра','Валюта','Российский рубль','Доллар США','Евро','Казахстанский тенге','Operation mode:','All rights reserved') as $label){$sources[]=$label;}
            foreach(array('shop_page','site_page','menus_menu_fields','shop_service','shop_service_variants','shop_product_skus') as $table){
                if($model->query('SHOW TABLES LIKE s:table',array('table'=>$table))->fetch()){
                    foreach($model->query('SELECT name FROM '.$table.' LIMIT 2000')->fetchAll() as $row){$sources[]=$row['name'];}
                }
            }
            // Initial adapter for the supported insales theme: short display labels only.
            foreach(array('site','shop','blog') as $app){
                if(is_file(wa()->getDataPath('themes/insales/theme.xml',true,$app,false))){
                    $theme=new waTheme('insales',$app,false,true);
                    foreach($theme->getSettings() as $key=>$setting){
                        if(in_array($key,array('header_phone_call','footer_contact_mode','footer_bottom_copy','checkout_success_text','checkout_success_contact'),true)&&is_string($setting['value']??null)){foreach(preg_split('/(<[^>]*>|\{[^}]*\})/u',$setting['value']) as $text){$sources[]=html_entity_decode($text,ENT_QUOTES,'UTF-8');}}
                        if((preg_match('/(?:_title|_text|_name)$/',$key)||in_array($key,array('modal_button_close','modal_button_cart'),true))
                            && in_array(isset($setting['control_type'])?$setting['control_type']:'',array('text','textarea'),true)
                            && isset($setting['value']) && is_string($setting['value'])){$sources[]=$setting['value'];}
                    }
                }
            }
            // Blog templates are compiled before the shared site layout.
            // Expose their public labels for the same manual translation workflow.
            $blogTheme=wa()->getDataPath('themes/insales',true,'blog',false);
            if(is_dir($blogTheme)){
                foreach(glob($blogTheme.'/*.html') as $file){
                    preg_match_all('/\[(?:s?)`([^`]+)`\]/u',file_get_contents($file),$matches);
                    foreach($matches[1] as $label){$sources[]=$label;}
                }
            }
            foreach((new shopViewHelper(wa('shop')))->promos('link') as $banner){foreach(array('title','body') as $field){if(is_string($banner[$field]??null)){$sources[]=$banner[$field];}}}
            $known=array();foreach(self::$catalog as $item){$known[$item['source']]=true;$known[$item['key']]=true;}
            foreach($sources as $source){
                $source=trim($source);if($source===''||mb_strlen($source)>240||preg_match('/[<>{}\r\n]|https?:|\[(?:s)?`|[\w.+-]+@[\w.-]+/u',$source)||!preg_match('/\p{L}/u',$source)||isset($known[$source])){continue;}
                $id=hexdec(substr(hash('sha256',$source),0,7))+1;
                if(isset(self::$catalog[$id])){throw new waException('Конфликт идентификаторов фраз интерфейса',500);}
                self::$catalog[$id]=array('key'=>$source,'source'=>$source);$known[$source]=true;
            }
        }
        return self::$catalog;
    }
    public static function dictionary($language) {
        if(!is_string($language)||!preg_match('/^[a-z]{2,3}_[A-Z]{2}$/',$language)||!isset(shopMultilanguageassystLanguages::all()[$language])){
            throw new waException('Неизвестный язык',400);
        }
        $file=dirname(__FILE__).'/../../locale/'.$language.'.php';
        $dictionary=is_file($file)?include $file:array();
        $catalog=self::catalog();
        foreach($catalog as $item){if(isset($dictionary[$item['key']])){$dictionary[$item['source']]=$dictionary[$item['key']];}}
        $rows=(new shopMultilanguageassystTranslationModel())->translations('interface',array_keys($catalog),$language);
        foreach($rows as $id=>$fields){if(isset($catalog[$id],$fields['text'])&&$fields['text']['value']!==''){$dictionary[$catalog[$id]['key']]=$fields['text']['value'];$dictionary[$catalog[$id]['source']]=$fields['text']['value'];}}
        $labels=$dictionary;foreach($labels as $key=>$translation){foreach(array(_wp($key),_ws($key)) as $label){if(!isset($dictionary[$label])){$dictionary[$label]=$translation;}}}
        foreach((new shopServiceModel())->getAll() as $service){$source=shopMultilanguageassystContent::source('service',$service['id']);$translations=(new shopMultilanguageassystTranslationModel())->translations('service',array($service['id']),$language);foreach($translations[$service['id']]??array() as $field=>$row){if(isset($source[$field])&&$source[$field]!==''&&$row['value']!==''){$dictionary[$source[$field]]=$row['value'];}}}
        return $dictionary;
    }
    public static function prefilter($source,$smarty) {
        return preg_replace_callback('/\[(s?)`([^`]+)`\]/u',function($match){
            $fallback=$match[1]==='s'?_ws($match[2]):_wp($match[2]);$value=shopMultilanguageassystPlugin::t($match[2],$fallback);
            if(in_array($match[2],array('Made on <a href="https://www.webasyst.com">Webasyst</a>','Powered by <a href="https://www.webasyst.com">Webasyst</a>','Shopping platform by <a href="https://www.shop-script.com">Shop-Script</a>'),true)){return $fallback;}
            return $value!==$fallback?htmlspecialchars($value,ENT_QUOTES,'UTF-8'):$fallback;
        },$source);
    }
    public static function prepare() {
        if(wa()->getEnv()!=='frontend'){return;}
        $view=wa()->getView();
        if($view instanceof waSmarty3View){
            $language=shopMultilanguageassystPlugin::language();
            $dictionary=self::dictionary($language);
            $view->setOptions(array('compile_id'=>wa()->getLocale().'_multilanguageassyst_043_'.substr(sha1_file(__FILE__),0,8).'_'.$language.'_'.substr(sha1(json_encode($dictionary)),0,12)));
            $view->smarty->unregisterFilter('pre','smarty_prefilter_translate');
            $view->smarty->registerFilter('pre',array(__CLASS__,'prefilter'));
        }
    }
}
