<?php
// Copyright (c) 2026 Владислав, the88788.com.
// SPDX-License-Identifier: LGPL-3.0-only
return array(
    'name' => 'МультиязыкАссист',
    'img' => 'img/icon-16.png',
    'icon' => array(16 => 'img/icon-16.png', 48 => 'img/icon-48.png'),
    'description' => 'Ручное заполнение переводов и экспорт/импорт языковых файлов.',
    'version' => '0.6.18',
    'author' => 'the88788.com',
    'vendor' => 1341445,
    'shop_settings' => true,
    'custom_settings' => true,
    'handlers' => array(
        'frontend_products' => 'frontendProducts',
        'routing' => 'routing',
        'view_helper_call' => 'viewHelperCall',
        'frontend_head' => 'frontendHead',
        'frontend_nav' => 'frontendPrepare',
        'frontend_homepage' => 'frontendPrepare',
        'frontend_product' => 'frontendPrepare',
        'frontend_category' => 'frontendPrepare',
        'frontend_search' => 'frontendPrepare',
        'frontend_compare' => 'frontendCompare',
        'frontend_cart' => 'frontendPrepare',
        'frontend_order' => 'frontendPrepare',
        'frontend_checkout' => 'frontendPrepare',
        'frontend_my' => 'frontendPrepare',
        'frontend_my_order' => 'frontendPrepare',
        'frontend_my_orders' => 'frontendPrepare',
        'backend_menu' => 'backendMenu',
        'backend_extended_menu' => 'backendExtendedMenu',
    ),
);












