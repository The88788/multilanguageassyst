<?php
// Copyright (c) 2026 Владислав, the88788.com.
// SPDX-License-Identifier: LGPL-3.0-only
class shopMultilanguageassystContent
{
    public static function fields($type, $id = null)
    {
        $fields = array(
            'product' => array('name', 'summary', 'description', 'meta_title', 'meta_description', 'meta_keywords'),
            'category' => array('name', 'description', 'meta_title', 'meta_description', 'meta_keywords'),
            'page' => array('name', 'title', 'content', 'description', 'keywords'),
            'feature' => array('name'),
            'service' => array('name'),
            'interface' => array('text'),
            'theme' => array('content'),
            'tag' => array('name'),
            'blog_tag' => array('name'),
            'blog_post' => array('title','text','text_before_cut','cut_link_label','meta_title','meta_description','meta_keywords'),
        );
        if (!isset($fields[$type])) { throw new waException(shopMultilanguageassystBackendLocale::t('Неизвестный тип содержимого'), 400); }
        if (in_array($type,array('feature','service'),true) && $id !== null) {
            $source = self::source($type, $id);
            return array_merge($fields[$type], array_keys($source['_value_labels']));
        }
        return $fields[$type];
    }
    public static function listing($type, $query, $page)
    {
        self::fields($type);
        if($type==='theme'){
            $rows=array();foreach(shopMultilanguageassystTheme::catalog() as $id=>$item){if($query===''||mb_stripos($item['name'].' '.strip_tags($item['content']),$query)!==false){$rows[]=array('id'=>$id,'name'=>$item['name']);}}
            $total=count($rows);$pages=max(1,(int)ceil($total/100));$page=max(1,min((int)$page,$pages));return array('rows'=>array_slice($rows,($page-1)*100,100),'total'=>$total,'page'=>$page,'pages'=>$pages);
        }
        if($type==='interface'){
            $rows=array();foreach(shopMultilanguageassystInterface::catalog() as $id=>$item){if($query===''||mb_stripos($item['source'],$query)!==false||mb_stripos($item['key'],$query)!==false){$rows[]=array('id'=>$id,'name'=>$item['source']);}}
            usort($rows,function($a,$b){return strcmp($a['name'],$b['name']);});$total=count($rows);$pages=max(1,(int)ceil($total/100));$page=max(1,min((int)$page,$pages));
            return array('rows'=>array_slice($rows,($page-1)*100,100),'total'=>$total,'page'=>$page,'pages'=>$pages);
        }
        $classes = array('product'=>'shopProductModel','category'=>'shopCategoryModel',
            'page'=>'shopPageModel','feature'=>'shopFeatureModel','service'=>'shopServiceModel');
        if(in_array($type,array('tag','blog_post','blog_tag'),true)&&!(new shopMultilanguageassystTranslationModel())->query('SHOW TABLES LIKE s:table',array('table'=>$type==='tag'?'shop_tag':$type))->fetch()){return array('rows'=>array(),'total'=>0,'page'=>1,'pages'=>1);}
        if(in_array($type,array('tag','blog_post','blog_tag'),true)){$model=new shopMultilanguageassystPublicContentModel($type);}
        else{$class = $classes[$type]; $model = new $class();}
        $nameField=$type==='blog_post'?'title':'name';

        $condition = '1';
        if ($query !== '') {
            $condition = $nameField." LIKE '%".$model->escape($query, 'like')."%'";
        }
        $total = (int)$model->select('COUNT(*)')->where($condition)->fetchField();
        $page = max(1, min((int)$page, max(1, (int)ceil($total / 100))));
        $rows = $model->select('id, '.$nameField.' AS name')->where($condition)->order(in_array($type,array('product','blog_post'),true) ? 'id DESC' : 'id')->limit((($page-1)*100) . ',100')->fetchAll();
        return array('rows'=>$rows,'total'=>$total,'page'=>$page,'pages'=>max(1,(int)ceil($total/100)));
    }
    public static function source($type, $id)
    {
        self::fields($type);
        if($type==='interface'){$catalog=shopMultilanguageassystInterface::catalog();if(!isset($catalog[$id])){throw new waException(shopMultilanguageassystBackendLocale::t('Фраза интерфейса не найдена'),404);}return array('id'=>$id,'name'=>$catalog[$id]['source'],'text'=>$catalog[$id]['source']);}
        if($type==='theme'){$catalog=shopMultilanguageassystTheme::catalog();if(!isset($catalog[$id])){throw new waException(shopMultilanguageassystBackendLocale::t('Блок темы не найден'),404);}return $catalog[$id];}
        $models = array('product' => 'shopProductModel', 'category' => 'shopCategoryModel',
            'page' => 'shopPageModel', 'feature' => 'shopFeatureModel','service'=>'shopServiceModel');
        if(in_array($type,array('tag','blog_post','blog_tag'),true)&&!(new shopMultilanguageassystTranslationModel())->query('SHOW TABLES LIKE s:table',array('table'=>$type==='tag'?'shop_tag':$type))->fetch()){throw new waException(shopMultilanguageassystBackendLocale::t('Раздел не установлен в этом магазине'),404);}
        if(in_array($type,array('tag','blog_post','blog_tag'),true)){$model=new shopMultilanguageassystPublicContentModel($type);}
        else{$class = $models[$type];$model = new $class();}
        $row = $model->getById($id);
        if (!$row) { throw new waException(shopMultilanguageassystBackendLocale::t('Объект не найден'), 404); }
        if($type==='blog_post'){$row['name']=$row['title'];}
        if($type==='service'){$row['_value_labels']=array();foreach((new shopServiceVariantsModel())->getByField('service_id',$id,true) as $variant){$field='variant_'.(int)$variant['id'];$row[$field]=(string)$variant['name'];$row['_value_labels'][$field]=shopMultilanguageassystBackendLocale::t('Вариант услуги: ').$variant['name'];}}
        if ($type === 'feature') {
            $row['_value_labels'] = array();
            if (in_array($row['type'], array('varchar', 'text', 'color'), true)) {
                $values = shopFeatureModel::getValuesModel($row['type'])->getByField('feature_id', $id, true);
                foreach ($values as $value) {
                    $field = 'value_'.(int)$value['id'];
                    $row[$field] = (string)$value['value'];
                    $row['_value_labels'][$field] = shopMultilanguageassystBackendLocale::t('Значение: ').$value['value'];
                }
            }
        }
        return $row;
    }
    public static function hash($source) { return hash('sha256', (string)$source); }
    public static function productFeatures($id, $language) {
        self::source('product',$id);
        $model=new shopMultilanguageassystTranslationModel();
        $links=$model->query('SELECT feature_id,feature_value_id FROM shop_product_features WHERE product_id=i:id',array('id'=>(int)$id))->fetchAll();
        $used=array();foreach($links as $link){$used[(int)$link['feature_id']]['value_'.(int)$link['feature_value_id']]=true;}
        $translated=$used?$model->translations('feature',array_keys($used),$language):array();
        $result=array();foreach($used as $featureId=>$valueFields){
            $source=self::source('feature',$featureId);$fields=array('name');
            foreach($valueFields as $field=>$unused){if(isset($source['_value_labels'][$field])){$fields[]=$field;}}
            $hashes=array();foreach($fields as $field){$hashes[$field]=self::hash($source[$field]);}
            $result[$featureId]=array('source'=>$source,'fields'=>$fields,'hashes'=>$hashes,'translations'=>isset($translated[$featureId])?$translated[$featureId]:array());
        }
        return $result;
    }
    public static function saveProduct($id,$language,array $values,array $hashes,array $features) {
        self::assertNotTemplate();
        $allowed=self::productFeatures($id,$language);
        self::save('product',$id,$language,$values,$hashes,false,true);
        foreach($features as $featureId=>$entry){
            if(!isset($allowed[$featureId])||!is_array($entry)||!isset($entry['translations'],$entry['hashes'])||!is_array($entry['translations'])||!is_array($entry['hashes'])){throw new waException(shopMultilanguageassystBackendLocale::t('Некорректная характеристика товара'),400);}
            foreach($entry['translations'] as $field=>$value){if(!in_array($field,$allowed[$featureId]['fields'],true)){throw new waException(shopMultilanguageassystBackendLocale::t('Значение не относится к этому товару'),400);}}
            self::save('feature',$featureId,$language,$entry['translations'],$entry['hashes'],false,true);
        }
        $model=new shopMultilanguageassystTranslationModel();$model->query('START TRANSACTION');
        try {
            self::save('product',$id,$language,$values,$hashes,false);
            foreach($features as $featureId=>$entry){self::save('feature',$featureId,$language,$entry['translations'],$entry['hashes'],false);}
            $model->query('COMMIT');
        }catch(Exception $e){$model->query('ROLLBACK');throw $e;}
    }
    public static function apply($type, $entity, array $translations)
    {
        foreach (self::fields($type) as $field) {
            if (isset($translations[$field]) && $translations[$field]['value'] !== '') {
                $entity[$field] = $translations[$field]['value'];
            }
        }
        return $entity;
    }
    public static function assertAdmin()
    {
        $user = wa()->getUser();
        if (!$user->isAuth() || (!$user->isAdmin('shop') && !$user->getRights('shop', 'settings'))) {
            throw new waRightsException(shopMultilanguageassystBackendLocale::t('Нужны права управления настройками магазина'));
        }
    }
    public static function assertNotTemplate()
    {
        if (waConfig::get('is_template')) {
            throw new waException(shopMultilanguageassystBackendLocale::t('Запись данных из шаблона запрещена'), 403);
        }
    }
    public static function assertPost()
    {
        self::assertAdmin();
        $token = waRequest::post('_csrf');
        $cookie = waRequest::cookie('_csrf');
        if (waRequest::method() !== 'post' || !is_string($token) || !is_string($cookie) || $cookie === '' || !hash_equals($cookie, $token)) {
            throw new waException(shopMultilanguageassystBackendLocale::t('Недействительный запрос'), 403);
        }
    }
    private static function preserveSourceEmbeds($value, $source, &$embeds)
    {
        return preg_replace_callback('~<(svg|iframe)\b[^>]*>.*?</\1\s*>~is', function ($match) use ($source, &$embeds) {
            $html = $match[0];
            if (preg_match('~\bon[a-z]+\s*=|(?:javascript|vbscript|data)\s*:|\{~iu', $html)) { return $html; }
            $matchesSource = strpos($source, $html) !== false;
            if (!$matchesSource && strtolower($match[1]) === 'iframe') {
                // Only the accessible title may change; all other markup must match the source.
                $withoutTitle = function ($embed) {
                    return preg_replace('~\s+title="[^"<>]*"~i', '', $embed, 1);
                };
                if (preg_match_all('~\btitle\s*=~i', $html) <= 1) {
                    preg_match_all('~<iframe\b[^>]*>.*?</iframe\s*>~is', $source, $originals);
                    foreach ($originals[0] as $original) {
                        if ($withoutTitle($original) === $withoutTitle($html)) { $matchesSource = true; break; }
                    }
                }
            }
            if (!$matchesSource) { return $html; }
            $safe = false;
            if (strtolower($match[1]) === 'svg') {
                $safe = preg_match('~\A<svg(?:\s+(?:class|width|height|viewBox|aria-hidden)="[^"<>]*")*\s*>\s*<use\s+xlink:href="#[a-z0-9_-]+"\s*>\s*</use>\s*</svg>\z~i', $html);
            } elseif (preg_match('~\bsrc="([^"]+)"~i', $html, $src)) {
                $url = parse_url(html_entity_decode($src[1], ENT_QUOTES, 'UTF-8'));
                $safe = isset($url['scheme'], $url['host'], $url['path']) && $url['scheme'] === 'https'
                    && $url['host'] === 'yandex.ru' && strpos($url['path'], '/map-widget/') === 0
                    && !isset($url['user']) && !isset($url['pass']);
                if (!$safe) {
                    $safe = isset($url['host'], $url['path'])
                        && (!isset($url['scheme']) || $url['scheme'] === 'https')
                        && in_array($url['host'], array('www.youtube.com','youtube.com','www.youtube-nocookie.com','youtube-nocookie.com'), true)
                        && preg_match('~\A/embed/[A-Za-z0-9_-]{11}\z~', $url['path'])
                        && !isset($url['user']) && !isset($url['pass']) && !isset($url['port']);
                }
                if (!$safe) {
                    $safe = isset($url['host'], $url['path'])
                        && (!isset($url['scheme']) || $url['scheme'] === 'https')
                        && in_array($url['host'], array('www.youtube.com','youtube.com','www.youtube-nocookie.com','youtube-nocookie.com'), true)
                        && preg_match('~\A/embed/[A-Za-z0-9_-]{11}\z~', $url['path'])
                        && !isset($url['user']) && !isset($url['pass']) && !isset($url['port']);
                }
            }
            if (!$safe) { return $html; }
            $token = 'ML_EMBED_'.bin2hex(random_bytes(16));
            $embeds[$token] = $html;
            return $token;
        }, $value);
    }
    public static function save($type, $id, $language, array $values, array $sourceHashes, $transaction = true, $validateOnly = false)
    {
        self::assertNotTemplate();
        shopMultilanguageassystLanguages::assertTarget($language);
        $source = self::source($type, $id);
        $fields = self::fields($type, $id);
        $model = new shopMultilanguageassystTranslationModel();
        $embeds = array();
        foreach ($values as $field => $value) {
            if (!in_array($field, $fields, true) || !is_string($value) || strlen($value) > 2000000) {
                throw new waException(shopMultilanguageassystBackendLocale::t('Недопустимое поле или размер перевода'), 400);
            }
            if (!isset($sourceHashes[$field]) || !is_string($sourceHashes[$field]) || !hash_equals(self::hash(isset($source[$field]) ? $source[$field] : ''), $sourceHashes[$field])) {
                throw new waException(shopMultilanguageassystBackendLocale::t('Исходный текст изменился. Обновите редактор.'), 409);
            }
            if($type==='interface'&&$value!==''){
                preg_match_all('/(?<!%)%(?!%)(?:[0-9]+\$)?[-+0-9.*]*[A-Za-z]/',$source[$field],$originalTokens);
                preg_match_all('/(?<!%)%(?!%)(?:[0-9]+\$)?[-+0-9.*]*[A-Za-z]/',$value,$translatedTokens);
                if($originalTokens[0]!==$translatedTokens[0]){throw new waException(shopMultilanguageassystBackendLocale::t('Фраза интерфейса #').(int)$id.shopMultilanguageassystBackendLocale::t(': сохраните подстановки %s, %d и другие параметры в исходном порядке'),400);}
            }
            if ($type === 'theme' && strpos((string)$source[$field], '{$date}') !== false) {
                if (substr_count($value, '{$date}') !== substr_count($source[$field], '{$date}')) {
                    throw new waException(shopMultilanguageassystBackendLocale::t('Сохраните переменную {$date} в переводе блока темы'), 400);
                }
                $token = 'ML_EMBED_'.bin2hex(random_bytes(16));
                $embeds[$token] = '{$date}';
                $value = str_replace('{$date}', $token, $value);
                $values[$field] = $value;
            }
            if ((in_array($field, array('description', 'summary', 'content'), true) || ($type === 'blog_post' && in_array($field, array('text','text_before_cut'), true)))) {
                // Legacy tooltip links need no JavaScript URL; retain their text and title.
                $value = preg_replace_callback('~<a\b[^>]*\s+href\s*=\s*(["\'])javascript:void\(0\);?\1[^>]*>(.*?)</a\s*>~is', function ($match) use (&$embeds) {
                    if (preg_match('~\bon[a-z]+\s*=|<\s*(script|iframe|object|embed)\b~i', $match[0])) { return $match[0]; }
                    preg_match('~\btitle\s*=\s*(["\'])(.*?)\1~is', $match[0], $title);
                    $escape = function ($text) { return htmlspecialchars(html_entity_decode($text, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8'); };
                    $html = '<span'.(isset($title[2]) ? ' title="'.$escape($title[2]).'"' : '').'>'.$escape(strip_tags($match[2])).'</span>';
                    $token = 'ML_EMBED_'.bin2hex(random_bytes(16));$embeds[$token]=$html;return $token;
                }, $value);
                $value = self::preserveSourceEmbeds($value, (string)$source[$field], $embeds);
                $values[$field] = $value;
            }
            if (preg_match('~<\s*(script|iframe|object|embed|style|svg|math|form|meta|link)\b|\bon[a-z]+\s*=|(?:javascript|vbscript|data)\s*:|\{~iu', $value, $unsupported)) {
                throw new waException(shopMultilanguageassystBackendLocale::t('Объект ').$type.' #'.(int)$id.shopMultilanguageassystBackendLocale::t(', поле ').$field.shopMultilanguageassystBackendLocale::t(': неподдерживаемая разметка «').$unsupported[0].shopMultilanguageassystBackendLocale::t('». Используйте текст, абзацы, списки и изображения.'), 400);
            }
        }
        $sanitizer = new waHtmlSanitizer();
        foreach ($values as $field => &$value) {
            if ((in_array($field, array('description', 'summary', 'content'), true) || ($type === 'blog_post' && in_array($field, array('text','text_before_cut'), true)))) {
                $value = strtr($sanitizer->sanitize($value), $embeds);
            } elseif (preg_match('~<\s*(?:/?[a-z][a-z0-9:_-]*\b|!|\?)~iu', $value)) {
                throw new waException(shopMultilanguageassystBackendLocale::t('Объект ').$type.' #'.(int)$id.shopMultilanguageassystBackendLocale::t(', поле ').$field.shopMultilanguageassystBackendLocale::t(': в текстовом поле нельзя использовать HTML'), 400);
            }
        }
        unset($value);
        if ($validateOnly) { return; }
        if ($transaction) { $model->query('START TRANSACTION'); }
        try {
            $embeds = array();
        foreach ($values as $field => $value) {
                $key = array('entity_type' => $type, 'entity_id' => $id, 'language' => $language, 'field' => $field);
                if ($value === '') {
                    $model->deleteByField($key);
                } else {
                    $model->insert($key + array('value' => $value,
                        'source_hash' => $sourceHashes[$field], 'updated_at' => date('Y-m-d H:i:s')), 1);
                }
            }
            if ($transaction) { $model->query('COMMIT'); }
        } catch (Exception $e) { if ($transaction) { $model->query('ROLLBACK'); } throw $e; }
    }
}









