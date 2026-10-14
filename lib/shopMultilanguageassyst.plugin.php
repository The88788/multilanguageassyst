<?php
// Copyright (c) 2026 Владислав, the88788.com.
// SPDX-License-Identifier: LGPL-3.0-only
class shopMultilanguageassystPlugin extends shopPlugin
{
    private static $language;
    private static $dictionary;
    public static function language()
    {
        if (self::$language === null) {
            self::$language = shopMultilanguageassystLanguages::source();
            if (wa()->getEnv() !== 'frontend') { return self::$language; }
            $requested = waRequest::get('multilanguageassyst_lang', null, 'string');
            $language = $requested !== null ? $requested : waRequest::cookie('shop_multilanguageassyst', shopMultilanguageassystLanguages::source());
            if (isset(shopMultilanguageassystLanguages::all()[$language])) {
                self::$language = $language;
                if ($requested !== null) {
                    setcookie('shop_multilanguageassyst', $language, array(
                        'expires' => time() + 31536000, 'path' => '/', 'secure' => waRequest::isHttps(),
                        'httponly' => true, 'samesite' => 'Lax',
                    ));
                }
            }
        }
        return self::$language;
    }
    public static function t($text, $fallback = null)
    {
        if (!self::enabled() || self::language() === shopMultilanguageassystLanguages::source()) { return $fallback !== null ? $fallback : $text; }
        if (self::$dictionary === null) {
            self::$dictionary = shopMultilanguageassystInterface::dictionary(self::language());
        }
        return isset(self::$dictionary[$text]) ? self::$dictionary[$text] : ($fallback !== null ? $fallback : $text);
    }
    public static function enabled()
    {
        $plugins = wa('shop')->getConfig()->getPlugins();
        return !empty($plugins['multilanguageassyst']);
    }
    public static function publicLabel($text)
    {
        return self::t($text,_wp($text));
    }
    public static function translatedMarkup($markup)
    {
        if(self::language()===shopMultilanguageassystLanguages::source()){return $markup;}
        $markup=preg_replace_callback('/\[(s?)`([^`]+)`\]/u',function($match){return htmlspecialchars(self::t($match[2],$match[1]==='s'?_ws($match[2]):_wp($match[2])),ENT_QUOTES,'UTF-8');},$markup);
        $parts=preg_split('/(<[^>]*>|\{[^}]*\})/u',$markup,-1,PREG_SPLIT_DELIM_CAPTURE);
        foreach($parts as &$part){if($part===''||$part[0]==='<'||$part[0]==='{'){continue;}$text=html_entity_decode($part,ENT_QUOTES,'UTF-8');$translated=self::t(trim($text));if($translated!==trim($text)){$part=str_replace(trim($text),htmlspecialchars($translated,ENT_QUOTES,'UTF-8'),$text);}}unset($part);return implode('',$parts);
    }
    public static function themeSettings($settings)
    {
        if(!is_array($settings)||!self::enabled()){return $settings;}
        $settings=shopMultilanguageassystTheme::settings($settings,self::language());
        foreach($settings as $key=>&$value){if(is_string($value)&&preg_match('/title|note|text|description|copyright/',$key)){$value=self::t($value);}}unset($value);
        return $settings;
    }
    public static function entity($type, $entity)
    {
        if (!self::enabled() || self::language() === shopMultilanguageassystLanguages::source() || !$entity || empty($entity['id'])) { return $entity; }
        $rows = (new shopMultilanguageassystTranslationModel())->translations($type, array((int)$entity['id']), self::language());
        if (!isset($rows[$entity['id']])) { return $entity; }
        $translations = $rows[$entity['id']];
        $entity = shopMultilanguageassystContent::apply($type, $entity, $translations);
        if (isset($translations['name'])) {
            if ($type !== 'page') {
                $params = isset($entity['params']) ? $entity['params'] : array();
                $params['h1'] = $entity['name']; if($type==='category'&&!empty($params['menu'])){$params['menu']=$entity['name'];} $entity['params'] = $params;
            }
            if ($type === 'product') { $entity['seo_name'] = $entity['name']; }
        }
        return $entity;
    }
    public static function languageUrl($language)
    {
        if (!isset(shopMultilanguageassystLanguages::all()[$language])) { return ''; }
        $uri = waRequest::server('REQUEST_URI', '/');
        $parts = parse_url($uri);
        $path = isset($parts['path']) ? $parts['path'] : '/';
        // Keep the link relative to this storefront, even with a malformed Host/request.
        $path = '/'.ltrim($path, '/');
        $query = array();
        if (!empty($parts['query'])) { parse_str($parts['query'], $query); }
        unset($query['locale']);
        $query['multilanguageassyst_lang'] = $language;
        return $path.'?'.http_build_query($query);
    }
    public static function features(array $features)
    {
        if (!self::enabled() || self::language() === shopMultilanguageassystLanguages::source()) { return $features; }
        $ids = array();
        foreach ($features as $feature) { if (isset($feature['id'])) { $ids[] = (int)$feature['id']; } }
        $rows = (new shopMultilanguageassystTranslationModel())->translations('feature', $ids, self::language());
        foreach ($features as &$feature) {
            if (isset($feature['id'], $rows[$feature['id']]['name'])) {
                $feature['name'] = $rows[$feature['id']]['name']['value'];
            }
            if (isset($feature['values']) && is_array($feature['values'])) {
                $feature['values'] = self::translatedFeatureValue($feature, $feature['values']);
            }
        }
        unset($feature);
        return $features;
    }
    private static $featureValueMaps = array();
    private static function translatedFeatureValue($feature, $value)
    {
        if (self::language() === shopMultilanguageassystLanguages::source() || empty($feature['id']) || !in_array($feature['type'], array('varchar', 'text', 'color'), true)) { return $value; }
        $id = (int)$feature['id'];
        if (!isset(self::$featureValueMaps[$id])) {
            $source = shopMultilanguageassystContent::source('feature', $id);
            $rows = (new shopMultilanguageassystTranslationModel())->translations('feature', array($id), self::language());
            $map = array();
            foreach ($source['_value_labels'] as $field => $label) {
                if (isset($rows[$id][$field])) { $map[$source[$field]] = $rows[$id][$field]['value']; }
            }
            self::$featureValueMaps[$id] = $map;
        }
        if (is_array($value)) {
            foreach ($value as &$item) { $item = self::translatedFeatureValue($feature, $item); } unset($item);
            return $value;
        }
        if ($value instanceof shopColorValue) {
            if (isset(self::$featureValueMaps[$id][$value->value])) {
                $value = clone $value;
                $value->value = self::$featureValueMaps[$id][$value->value];
            }
            return $value;
        }
        if (is_string($value) && isset(self::$featureValueMaps[$id][$value])) { return self::$featureValueMaps[$id][$value]; }
        return $value;
    }
    public static function featureValues(array $values, array $features)
    {
        if (!self::enabled() || self::language() === shopMultilanguageassystLanguages::source()) { return $values; }
        foreach ($features as $feature) { if (!empty($feature['code'])) { $features[$feature['code']] = $feature; } }
        foreach ($values as $code => &$value) {
            if (isset($features[$code])) { $value = self::translatedFeatureValue($features[$code], $value); }
        } unset($value);
        return $values;
    }
    public static function productsFeatureValues(array $products, array $features)
    {
        foreach ($products as &$product) {
            if (!empty($product['features'])) { $product['features'] = self::featureValues($product['features'], $features); }
        } unset($product);
        return $products;
    }
    public static function productFeatureValues($product)
    {
        if (!self::enabled() || self::language() === shopMultilanguageassystLanguages::source() || !$product) { return $product; }
        $values = isset($product['features']) ? $product['features'] : array();
        $skus = isset($product['skus']) ? $product['skus'] : array();
        $codes = is_array($values) ? array_keys($values) : array();
        foreach ($skus as $sku) { if (!empty($sku['features'])) { $codes = array_merge($codes, array_keys($sku['features'])); } }
        if (!$codes) { return $product; }
        $features = (new shopFeatureModel())->getByCode(array_unique($codes));
        if (is_object($product)) { $product = clone $product; }
        if (is_array($values)) { $product['features'] = self::featureValues($values, $features); }
        foreach ($skus as &$sku) { if (!empty($sku['features'])) { $sku['features'] = self::featureValues($sku['features'], $features); } } unset($sku);
        if ($skus) { $product['skus'] = $skus; }
        return $product;
    }
    public function frontendProducts(&$params)
    {
        if(wa()->getEnv()==='frontend' && self::enabled()){shopMultilanguageassystInterface::prepare();}
        if (self::language() === shopMultilanguageassystLanguages::source() || empty($params['products'])) { return; }
        $ids = array();
        foreach ($params['products'] as $product) { $ids[] = (int)$product['id']; }
        $model = new shopMultilanguageassystTranslationModel();
        $translations = $model->translations('product', $ids, self::language());
        foreach ($params['products'] as &$product) {
            $id = $product['id'];
            if (isset($translations[$id])) {
                $product = shopMultilanguageassystContent::apply('product', $product, $translations[$id]);
            }
        }
        unset($product);
    }
    public function frontendPrepare(&$params = null)
    {
        self::prepareStorefront();
        return null;
    }
    public function frontendCompare(&$params)
    {
        $this->frontendProducts($params);
        if(is_array($params['features']??null)){
            $params['products']=self::productsFeatureValues($params['products']??array(),$params['features']);
            $params['features']=self::features($params['features']);
        }
        self::prepareStorefront();
        return null;
    }
    public static function prepareStorefront()
    {
        if (wa()->getEnv() !== 'frontend' || !self::enabled()) { return ''; }
        shopMultilanguageassystInterface::prepare();
        self::prepareFeatureTranslations();
        if (self::language() !== shopMultilanguageassystLanguages::source()) {
            $view = wa()->getView();
            foreach (array('product', 'category', 'page') as $type) {
                $entity = $view->getVars($type);
                if ($entity) { $view->assign($type, self::entity($type, $entity)); }
            }
            $category = $view->getVars('category');
            if (is_array($category) && !empty($category['subcategories'])) {
                $category['subcategories'] = self::categories($category['subcategories']);
                $view->assign('category', $category);
            }
            $breadcrumbs = $view->getVars('breadcrumbs');
            if (is_array($breadcrumbs)) { $view->assign('breadcrumbs', self::categoryBreadcrumbs($breadcrumbs)); }
            foreach (array('categories', 'subcategories') as $name) {
                $items = $view->getVars($name);
                if (is_array($items)) { $view->assign($name, self::categories($items)); }
            }
            $parents = $view->getVars('page_parents');
            if (is_array($parents)) {
                $parents = self::pages($parents);
                $view->assign('page_parents', $parents);
                $breadcrumbs = $view->getVars('breadcrumbs');
                if (is_array($breadcrumbs)) {
                    foreach ($breadcrumbs as &$crumb) {
                        foreach ($parents as $parent) {
                            $url = wa()->getAppUrl(null, true).$parent['full_url'];
                            if (!empty($crumb['url']) && rtrim(parse_url($crumb['url'], PHP_URL_PATH), '/') === rtrim(parse_url($url, PHP_URL_PATH), '/')) { $crumb['name'] = $parent['name']; }
                        }
                    }
                    unset($crumb);
                    $view->assign('breadcrumbs', $breadcrumbs);
                }
            }
            foreach (array('shop_pages', 'subpages') as $name) {
                $items = $view->getVars($name);
                if (is_array($items)) { $view->assign($name, self::pages($items)); }
            }
            $order=$view->getVars('order');
            if(is_array($order)){$view->assign('order',self::displayOrder($order));}
            $orders=$view->getVars('orders');
            if(is_array($orders)){foreach($orders as &$order){if(is_array($order)){$order=self::displayOrder($order);}}unset($order);$view->assign('orders',$orders);}
            $info=$view->getVars('user_info');
            if(is_array($info)){foreach($info as &$field){if(isset($field['name'])){$field['name']=self::t($field['name']);}}unset($field);$view->assign('user_info',$info);}
            foreach(array('thankyou_header','thankyou_content') as $name){$text=$view->getVars($name);if(is_string($text)){$view->assign($name,self::t($text));}}
        }
        return '';
    }
    private static function prepareFeatureTranslations()
    {
        if (self::language() === shopMultilanguageassystLanguages::source()) { return; }
        $view = wa()->getView();
        $product = $view->getVars('product');
        if ($product) { $view->assign('product', self::productFeatureValues($product)); }
        foreach (array('features', 'sku_features', 'features_selectable', 'filters') as $name) {
            $features = $view->getVars($name);
            if (is_array($features)) { $view->assign($name, self::features($features)); }
        }
    }
    public static function prepareMetadata()
    {
        if (!self::enabled() || self::language() === shopMultilanguageassystLanguages::source()) { return ''; }
        $response=wa()->getResponse();$view=wa()->getView();
        $response->setTitle(self::t($response->getTitle()));
        foreach(array('description','keywords') as $field){$text=$response->getMeta($field);if(is_string($text)){$response->setMeta($field,self::t($text));}}
        $model=new shopMultilanguageassystTranslationModel();
        foreach(array('product','category','page') as $type){
            $entity=$view->getVars($type);if(!$entity||empty($entity['id'])){continue;}
            $rows=$model->translations($type,array((int)$entity['id']),self::language());
            if(empty($rows[$entity['id']])){continue;}$translated=$rows[$entity['id']];
            $titleField=$type==='page'?'title':'meta_title';
            $title=!empty($translated[$titleField]['value'])?$translated[$titleField]['value']:(!empty($translated['name']['value'])?$translated['name']['value']:null);
            if($title!==null){$response->setTitle($title);$response->setOGMeta('og:title',$title);}
            foreach(array('description','keywords') as $meta){$field=$type==='page'?$meta:'meta_'.$meta;
                if(isset($translated[$field])){$response->setMeta($meta,$translated[$field]['value']);if($meta==='description'){$response->setOGMeta('og:description',$translated[$field]['value']);}}
            }
        }
        return '';
    }
    public function frontendHead()
    {
        self::prepareStorefront();
        self::prepareMetadata();
        $language = self::language();
        $view = wa()->getView();
        $view->assign('multilanguageassyst_language', $language);
        $choices=array();foreach(shopMultilanguageassystLanguages::all() as $code=>$name){$choices[]=array($code,$name);}
        $ui = $language !== shopMultilanguageassystLanguages::source() ? '<script type="application/json" id="multilanguageassyst-ui-dictionary">'.json_encode(shopMultilanguageassystInterface::dictionary($language),JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT).'</script><script defer src="'.wa()->getRootUrl().'wa-apps/shop/plugins/multilanguageassyst/js/storefront.js?v=0.6.17"></script>' : '';
        return $ui.'<style>.multilanguageassyst-selector{position:fixed;bottom:16px;left:16px;z-index:10000;display:flex;gap:12px;background:#fff;border:1px solid #ccc;border-radius:8px;padding:10px 14px;font:14px sans-serif;box-shadow:0 2px 12px #0002}.multilanguageassyst-selector a{color:#222}.multilanguageassyst-selector [aria-current]{font-weight:bold}</style>'
            .'<script>document.addEventListener("DOMContentLoaded",function(){document.documentElement.lang="'.strtolower(strtok($language, '_')).'";if(document.querySelector(".multilanguageassyst-theme-language")){return;}var nav=document.createElement("nav");nav.className="multilanguageassyst-selector";nav.setAttribute("aria-label","Выбор языка / Тілді таңдау");'
            .''.json_encode($choices,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT).'.forEach(function(item){var a=document.createElement("a");var u=new URL(location.href);u.searchParams.set("multilanguageassyst_lang",item[0]);a.href=u.toString();a.textContent=item[1];if(item[0]==="'. $language .'"){a.setAttribute("aria-current","true");}nav.appendChild(a);});document.body.appendChild(nav);});</script>';
    }
    public function backendExtendedMenu(&$params)
    {
        if (!wa()->getUser()->getRights('shop','settings') && !wa()->getUser()->isAdmin('shop')) { return; }
        shopMainMenu::createSection($params['menu'],'multilanguageassyst',shopMultilanguageassystBackendLocale::t('Переводы'),array('insert_after'=>'plugins','url'=>'?plugin=multilanguageassyst&module=backend','icon'=>'<i class="fas fa-language"></i>'));
    }
    public static function sectionVisible($section,$language=null)
    {
        $language=$language===null?self::language():$language;
        if($language===shopMultilanguageassystLanguages::source()){return true;}
        $hidden=json_decode((new waAppSettingsModel())->get(array('shop','multilanguageassyst'),'hidden_sections','{}'),true);
        return !isset($hidden[$language])||!in_array($section,$hidden[$language],true);
    }
    public static function checkSection()
    {
        $uri=parse_url(waRequest::server('REQUEST_URI','/'),PHP_URL_PATH);
        if((wa()->getApp()==='blog'&&!self::sectionVisible('blog'))||(preg_match('~/tag/~',$uri)&&!self::sectionVisible('tags'))){throw new waException(shopMultilanguageassystBackendLocale::t('Раздел недоступен на выбранном языке'),404);}
        if(wa()->getApp()==='blog'){
            shopMultilanguageassystInterface::prepare();$view=wa()->getView();
            $posts=$view->getVars('posts');if(is_array($posts)){$view->assign('posts',self::blogPosts($posts));}
            $post=$view->getVars('post');if(is_array($post)&&isset($post['id'])){$post=self::entity('blog_post',$post);$view->assign('post',$post);wa()->getResponse()->setTitle(!empty($post['meta_title'])?$post['meta_title']:$post['title']);foreach(array('description','keywords') as $meta){if(isset($post['meta_'.$meta])){wa()->getResponse()->setMeta($meta,$post['meta_'.$meta]);}}}
        }
        return '';
    }
    public static function categories(array $categories)
    {
        foreach($categories as &$category){if(!is_array($category)){continue;}$category=self::entity('category',$category);foreach(array('childs','children','subcategories') as $key){if(isset($category[$key])&&is_array($category[$key])){$category[$key]=self::categories($category[$key]);}}}unset($category);return $categories;
    }
    public static function pages(array $pages)
    {
        foreach ($pages as &$page) {
            if (!is_array($page)) { continue; }
            $page = self::entity('page', $page);
            foreach (array('childs', 'children', 'subpages') as $key) {
                if (isset($page[$key]) && is_array($page[$key])) { $page[$key] = self::pages($page[$key]); }
            }
        }
        unset($page);
        return $pages;
    }
    public static function displayOrder(array $order)
    {
        if(!self::enabled() || self::language()===shopMultilanguageassystLanguages::source()){return $order;}
        foreach(array('shipping_name','payment_name') as $field){
            if(isset($order[$field])){$order[$field]=self::t($order[$field]);}
            if(isset($order['params'][$field])){$order['params'][$field]=self::t($order['params'][$field]);}
        }
        $ids=array();foreach($order['items']??array() as $item){if(!empty($item['product_id'])&&($item['type']??'product')==='product'){$ids[]=(int)$item['product_id'];}}
        if($ids){
            $originals=array();foreach((new shopProductModel())->getByField('id',array_unique($ids),true) as $product){$originals[$product['id']]=$product['name'];}
            $rows=(new shopMultilanguageassystTranslationModel())->translations('product',array_unique($ids),self::language());
            $skuIds=array();foreach($order['items'] as $item){if(!empty($item['sku_id'])){$skuIds[]=(int)$item['sku_id'];}}
            $skus=array();if($skuIds){foreach((new shopProductSkusModel())->getByField('id',array_unique($skuIds),true) as $sku){$skus[$sku['id']]=$sku;}}
            foreach($order['items'] as &$item){$id=$item['product_id']??0;
                if(($item['type']??'product')!=='product'||!isset($originals[$id])){continue;}
                $sku=$skus[$item['sku_id']??0]??null;
                $suffix=$sku && (int)$sku['product_id']===(int)$id && strlen($sku['name'])?' ('.$sku['name'].')':'';
                $name=$rows[$id]['name']['value']??$originals[$id];
                if($item['name']===$originals[$id]){$item['name']=$name;}
                elseif($suffix!==''&&$item['name']===$originals[$id].$suffix){$item['name']=$name.' ('.self::t($sku['name']).')';}
            }unset($item);
        }
        return $order;
    }
    public static function categoryBreadcrumbs(array $items)
    {
        if(self::language()===shopMultilanguageassystLanguages::source()){return $items;}
        $categories=(new shopCategoryModel())->select('id,name,url,full_url')->fetchAll();$map=array();
        $translations=(new shopMultilanguageassystTranslationModel())->translations('category',array_column($categories,'id'),self::language());
        foreach($categories as $category){$url=wa()->getRouteUrl('shop/frontend/category',array('category_url'=>$category['full_url']?:$category['url']));if(!empty($translations[$category['id']]['name']['value'])){$category['name']=$translations[$category['id']]['name']['value'];}$map[rtrim(parse_url($url,PHP_URL_PATH),'/')]=$category;}
        foreach($items as &$item){if(!is_array($item)||empty($item['url'])){continue;}$path=rtrim(parse_url($item['url'],PHP_URL_PATH),'/');if(isset($map[$path])){$item['name']=$map[$path]['name'];}}unset($item);return $items;
    }
    public static function tags(array $tags,$type='tag')
    {
        if(!self::sectionVisible('tags')){return array();}
        foreach($tags as &$tag){if(is_array($tag)){$tag=self::entity($type,$tag);}}unset($tag);return $tags;
    }
    public static function tagName($name)
    {
        static $names=null;if($names===null){$names=array();$model=new shopMultilanguageassystPublicContentModel('tag');$rows=$model->select('*')->fetchAll();$translated=self::tags($rows);foreach($rows as $key=>$row){$names[$row['name']]=$translated[$key]['name']??$row['name'];}}
        return $names[$name]??$name;
    }
    public static function blogPosts(array $posts)
    {
        if(!self::sectionVisible('blog')){return array();}
        foreach($posts as &$post){if(is_array($post)){$post=self::entity('blog_post',$post);if(!empty($post['cutted'])&&!empty($post['text_before_cut'])){$post['text']=$post['text_before_cut'];}}}unset($post);return $posts;
    }
    public static function navigation(array $items)
    {
        foreach($items as $key=>&$item){if(!is_array($item)){continue;}$url=$item['url']??'';
            if((!self::sectionVisible('blog')&&preg_match('~/blog(?:/|$)~',$url))||(!self::sectionVisible('tags')&&preg_match('~/tag/~',$url))){unset($items[$key]);continue;}
            foreach(array('childs','children') as $children){if(isset($item[$children])&&is_array($item[$children])){$item[$children]=self::navigation($item[$children]);}}
        }unset($item);return $items;
    }
    public function routing($route=array())
    {
        return array('htmlmaps/?'=>array('plugin'=>'multilanguageassyst','module'=>'frontend','action'=>'sitemap'));
    }
    public function viewHelperCall(&$params)
    {
        if($params['name']==='multilanguageassystBootstrap'){self::prepareStorefront();return true;}
        if($params['name']==='multilanguageassystContext'){
            self::prepareStorefront();
            $context=array();$view=wa()->getView();
            foreach(array('product','category','page','features','sku_features','features_selectable','filters','breadcrumbs','page_parents','categories','subcategories','shop_pages','subpages','order','orders','user_info','thankyou_header','thankyou_content') as $name){
                $value=$view->getVars($name);if($value!==null){$context[$name]=$value;}
            }
            return $context;
        }
        return null;
    }
    public function backendMenu()
    {
        if (!wa()->getUser()->getRights('shop', 'settings') && !wa()->getUser()->isAdmin('shop')) { return array(); }
        return array('aux_li' => '<li><a href="?plugin=multilanguageassyst&module=backend">'.htmlspecialchars(shopMultilanguageassystBackendLocale::t('МультиязыкАссист'), ENT_QUOTES, 'UTF-8').'</a></li>');
    }
}









