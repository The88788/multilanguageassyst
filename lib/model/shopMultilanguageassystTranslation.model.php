<?php
// Copyright (c) 2026 Владислав, the88788.com.
// SPDX-License-Identifier: LGPL-3.0-only
class shopMultilanguageassystTranslationModel extends waModel
{
    protected $table = 'shop_multilanguageassyst_translation';
    public function translations($type, array $ids, $language)
    {
        if (!$ids) { return array(); }
        $rows = $this->getByField(array(
            'entity_type' => $type, 'entity_id' => $ids, 'language' => $language
        ), true);
        $result = array();
        foreach ($rows as $row) { $result[$row['entity_id']][$row['field']] = $row; }
        return $result;
    }
}
