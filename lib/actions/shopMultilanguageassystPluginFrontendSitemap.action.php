<?php
// Copyright (c) 2026 Владислав, the88788.com.
// SPDX-License-Identifier: LGPL-3.0-only
class shopMultilanguageassystPluginFrontendSitemapAction extends waViewAction
{
    public function execute() {
        $categories=(new shopCategoryModel())->select('*')->where('status=1')->order('left_key')->fetchAll();
        foreach($categories as &$category){$category['url']=wa()->getRouteUrl('shop/frontend/category',array('category_url'=>$category['full_url']?:$category['url']));}unset($category);
        $pages=(new shopPageModel())->select('*')->where('status=1')->order('sort')->fetchAll();
        $host=wa()->getRouting()->getDomain();$pages=array_filter($pages,function($page)use($host){return $page['domain']===$host;});
        foreach($pages as &$page){$page['url']=wa()->getAppUrl('shop').ltrim($page['full_url'],'/');$page=shopMultilanguageassystPlugin::entity('page',$page);}unset($page);
        $this->view->assign(array('ml_sitemap_categories'=>shopMultilanguageassystPlugin::categories($categories),'ml_sitemap_pages'=>$pages));
        wa()->getResponse()->setTitle(shopMultilanguageassystPlugin::t('Карта сайта'));
        $this->setTemplate(wa()->getAppPath('plugins/multilanguageassyst/templates/actions/frontend/Sitemap.html','shop'));
        $this->setLayout(new shopFrontendLayout());
    }
}
