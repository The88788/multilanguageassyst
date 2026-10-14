<?php
// Copyright (c) 2026 Владислав, the88788.com.
// SPDX-License-Identifier: LGPL-3.0-only
class shopMultilanguageassystPublicContentModel extends waModel
{
    public function __construct($type) {
        $tables=array('tag'=>'shop_tag','blog_post'=>'blog_post','blog_tag'=>'blog_tag');
        if(!isset($tables[$type])){throw new waException(shopMultilanguageassystBackendLocale::t('Неизвестный тип содержимого'),400);}
        $this->table=$tables[$type];parent::__construct();
    }
}
