<?php
// Copyright (c) 2026 Владислав, the88788.com.
// SPDX-License-Identifier: LGPL-3.0-only
class shopMultilanguageassystPluginBackendAction extends waViewAction
{
    public function execute()
    {
        shopMultilanguageassystContent::assertAdmin();
        if (!waRequest::isXMLHttpRequest()) {
            $this->setLayout(new shopMultilanguageassystPluginBackendLayout());
            $this->getResponse()->setTitle(shopMultilanguageassystBackendLocale::t('МультиязыкАссист - переводы витрины'));
        }
        $targets = shopMultilanguageassystLanguages::targets();
        $language = waRequest::get('target_language', null, 'string');
        if (!isset($targets[$language])) { $keys = array_keys($targets); $language = $keys ? reset($keys) : ''; }
        $languages=shopMultilanguageassystLanguages::all();
        $this->view->assign(array('target_language'=>$language,'target_languages'=>$targets,'target_name'=>isset($languages[$language])?$languages[$language]:'','languages'=>$languages,'source_language'=>shopMultilanguageassystLanguages::source(),'source_name'=>$languages[shopMultilanguageassystLanguages::source()],'journal'=>shopMultilanguageassystJournal::entries(),'plugin_version'=>'0.6.19'));
        $type = waRequest::get('entity_type', 'product', 'string');
        $id = $language !== '' ? waRequest::get('entity_id', 0, 'int') : 0;
        $fields = shopMultilanguageassystContent::fields($type);
        $query = waRequest::get('q', '', 'string');
        $listing = shopMultilanguageassystContent::listing($type, $query, waRequest::get('list_page', 1, 'int'));
        $base = wa()->getAppUrl('shop').'?plugin=multilanguageassyst&module=backend&entity_type='.urlencode($type).'&q='.urlencode($query).'&target_language='.urlencode($language);
        foreach ($listing['rows'] as &$item) { $item['edit_url'] = $base.'&list_page='.$listing['page'].'&entity_id='.$item['id']; }
        unset($item);
        $this->view->assign(array('listing'=>$listing,'query'=>$query,'editor_url'=>wa()->getAppUrl('shop'),
            'previous_url'=>$base.'&list_page='.max(1,$listing['page']-1),
            'next_url'=>$base.'&list_page='.($listing['page']+1)));
        $source = array(); $rows = array(); $hashes = array();
        if ($id > 0) {
            $source = shopMultilanguageassystContent::source($type, $id);
            $fields = shopMultilanguageassystContent::fields($type, $id);
            $model = new shopMultilanguageassystTranslationModel();
            $all = $model->translations($type, array($id), $language);
            $rows = isset($all[$id]) ? $all[$id] : array();
            foreach ($fields as $field) {
                $hashes[$field] = shopMultilanguageassystContent::hash(isset($source[$field]) ? $source[$field] : '');
            }
        }
        $this->view->assign(array('export_mode'=>waRequest::get('panel','','string')==='export','product_features'=>$type==='product'&&$id>0?shopMultilanguageassystContent::productFeatures($id,$language):array(),'entity_type' => $type, 'entity_id' => $id,
            'fields' => $fields, 'source' => $source, 'translations' => $rows, 'hashes' => $hashes));
        $template = waRequest::isXMLHttpRequest() && waRequest::get('list_only', 0, 'int')
            ? 'Browser.html' : 'Backend.html';
        if(waRequest::isXMLHttpRequest()){ $panel=waRequest::get('panel','','string');if($panel==='export'){$template='Browser.html';}elseif($panel==='edit'){$template='Editor.html';}elseif($panel==='logs'){$template='Journal.html';}elseif($panel==='languages'){$template='Languages.html';} }
        $this->setTemplate(wa()->getAppPath('plugins/multilanguageassyst/templates/actions/backend/'.$template, 'shop'));
    }
}








