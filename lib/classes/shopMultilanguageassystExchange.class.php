<?php
// Copyright (c) 2026 Владислав, the88788.com.
// SPDX-License-Identifier: LGPL-3.0-only
class shopMultilanguageassystExchange
{
    public static function export($type, array $ids, $language = null)
    {
        $language=shopMultilanguageassystLanguages::target($language);
        $fields = shopMultilanguageassystContent::fields($type);
        if (!$ids || count($ids) > 100) { throw new waException(shopMultilanguageassystBackendLocale::t('Выберите от 1 до 100 объектов'), 400); }
        $translations = (new shopMultilanguageassystTranslationModel())->translations($type, $ids, $language);
        $items = array();
        foreach (array_unique($ids) as $id) {
            $source = shopMultilanguageassystContent::source($type, $id);
            $fields = shopMultilanguageassystContent::fields($type, $id);
            $item = array('entity_type' => $type, 'entity_id' => (int)$id, 'fields' => array());
            if ($type === 'feature') { $item['feature_code'] = $source['code']; $item['feature_type'] = $source['type']; }
            if ($type === 'theme') { $item['theme_app']=$source['app'];$item['theme_id']=$source['theme'];$item['setting_key']=$source['setting']; }
            foreach ($fields as $field) {
                $text = isset($source[$field]) ? (string)$source[$field] : '';
                $translation = isset($translations[$id][$field]) ? $translations[$id][$field] : null;
                $item['fields'][$field] = array(
                    'source' => $text, 'source_hash' => shopMultilanguageassystContent::hash($text),
                    'translation' => $translation ? $translation['value'] : '',
                    'needs_review' => $translation && $translation['source_hash'] !== shopMultilanguageassystContent::hash($text),
                );
            }
            $items[] = $item;
        }
        return array('format' => 'shop-multilanguageassyst/1', 'source_language' => shopMultilanguageassystLanguages::source(),
            'target_language' => $language, 'items' => $items);
    }
    public static function import($json, $confirmOverwrite = false, $dryRun = false)
    {
        shopMultilanguageassystContent::assertNotTemplate();
        if (!is_string($json) || strlen($json) > 5000000) { throw new waException(shopMultilanguageassystBackendLocale::t('Файл слишком большой: максимум 5 МБ'), 400); }
        $data = json_decode($json, true);
        if (!is_array($data) || !isset($data['format'], $data['target_language'], $data['items'])
            || $data['format'] !== 'shop-multilanguageassyst/1'
            || !is_array($data['items']) || !$data['items'] || count($data['items']) > 100) {
            throw new waException(shopMultilanguageassystBackendLocale::t('Неверный формат файла перевода'), 400);
        }
        $language=shopMultilanguageassystLanguages::target($data['target_language']);
        if(isset($data['source_language'])&&$data['source_language']!==shopMultilanguageassystLanguages::source()){throw new waException(shopMultilanguageassystBackendLocale::t('Язык оригинала в файле не совпадает с настройками магазина'),409);}
        $model = new shopMultilanguageassystTranslationModel();
        $prepared = array(); $seen = array(); $changes = 0; $overwrites = 0;
        foreach ($data['items'] as $item) {
            if (!is_array($item) || !isset($item['entity_type'], $item['entity_id'], $item['fields'])
                || !is_string($item['entity_type']) || (!is_int($item['entity_id']) && !is_string($item['entity_id'])) || !preg_match('/^[1-9][0-9]*$/', (string)$item['entity_id'])
                || (int)$item['entity_id'] <= 0 || !is_array($item['fields'])) {
                throw new waException(shopMultilanguageassystBackendLocale::t('Некорректный объект перевода'), 400);
            }
            $type = $item['entity_type']; $id = (int)$item['entity_id'];
            if($type==='theme'){
                $source=shopMultilanguageassystContent::source($type,$id);
                if(!isset($item['theme_app'],$item['theme_id'],$item['setting_key'])||$item['theme_app']!==$source['app']||$item['theme_id']!==$source['theme']||$item['setting_key']!==$source['setting']){throw new waException(shopMultilanguageassystBackendLocale::t('Блок темы не совпадает с исходным. Выполните новый экспорт.'),409);}
            }
            if ($type === 'feature' && (isset($item['feature_code']) || isset($item['feature_type']))) {
                $source = shopMultilanguageassystContent::source($type, $id);
                if (!isset($item['feature_code'], $item['feature_type']) || $item['feature_code'] !== $source['code'] || $item['feature_type'] !== $source['type']) {
                    throw new waException(shopMultilanguageassystBackendLocale::t('Характеристика #').$id.shopMultilanguageassystBackendLocale::t(': код или тип не совпадает с оригиналом. Экспортируйте файл из этого магазина.'), 409);
                }
            }
            $key = $type.':'.$id;
            if (isset($seen[$key])) { throw new waException(shopMultilanguageassystBackendLocale::t('Повторяющийся объект в файле'), 400); }
            $seen[$key] = true;
            $values = array(); $hashes = array();
            $existing = $model->translations($type, array($id), $language);
            foreach ($item['fields'] as $field => $row) {
                if (!is_array($row) || !isset($row['translation'], $row['source_hash'])
                    || !is_string($row['translation']) || !is_string($row['source_hash'])) {
                    throw new waException(shopMultilanguageassystBackendLocale::t('Некорректное поле перевода'), 400);
                }
                if ($row['translation'] === '') { continue; }
                $old = isset($existing[$id][$field]) ? $existing[$id][$field]['value'] : null;
                if ($old === $row['translation']) { continue; }
                if ($old !== null) { $overwrites++; }
                $values[$field] = $row['translation']; $hashes[$field] = $row['source_hash']; $changes++;
            }
            shopMultilanguageassystContent::save($type, $id, $language, $values, $hashes, false, true);
            $prepared[] = array($type, $id, $values, $hashes);
        }
        $summary = array('objects' => count($prepared), 'fields' => $changes, 'overwrites' => $overwrites, 'language'=>$language);
        if ($dryRun) { return $summary; }
        if ($overwrites && !$confirmOverwrite) { throw new waException(shopMultilanguageassystBackendLocale::t('Для замены существующих переводов нужно явное подтверждение'), 409); }
        $model->query('START TRANSACTION');
        try {
            foreach ($prepared as $row) {
                shopMultilanguageassystContent::save($row[0], $row[1], $language, $row[2], $row[3], false);
            }
            $model->query('COMMIT');
        } catch (Exception $e) { $model->query('ROLLBACK'); throw $e; }
        return $summary;
    }
}


