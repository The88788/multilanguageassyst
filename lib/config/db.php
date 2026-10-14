<?php
// Copyright (c) 2026 Владислав, the88788.com.
// SPDX-License-Identifier: LGPL-3.0-only
return array('shop_multilanguageassyst_translation' => array(
    'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
    'entity_type' => array('varchar', 32, 'null' => 0),
    'entity_id' => array('int', 11, 'null' => 0),
    'language' => array('varchar', 16, 'null' => 0),
    'field' => array('varchar', 32, 'null' => 0),
    'value' => array('mediumtext', 'null' => 0, 'charset' => 'utf8mb4'),
    'source_hash' => array('varchar', 64, 'null' => 0),
    'updated_at' => array('datetime', 'null' => 0),
    ':options' => array('engine' => 'InnoDB', 'charset' => 'utf8mb4'),
    ':keys' => array(
        'PRIMARY' => 'id',
        'entity_language_field' => array('entity_type', 'entity_id', 'language', 'field', 'unique' => 1),
    ),
));


