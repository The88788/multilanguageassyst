<?php
// Copyright (c) 2026 Владислав, the88788.com.
// SPDX-License-Identifier: LGPL-3.0-only
class shopMultilanguageassystPluginBackendExportController extends waController
{
    public function execute()
    {
        shopMultilanguageassystContent::assertAdmin();
        $type = waRequest::get('entity_type', 'product', 'string');
        $raw = waRequest::get('ids', array());
        if (is_array($raw)) {
            $ids = array();
            foreach ($raw as $id) {
                if (!is_scalar($id) || !preg_match('/^[1-9][0-9]*$/', (string)$id)) { throw new waException(shopMultilanguageassystBackendLocale::t('Некорректный выбор объектов'), 400); }
                $ids[] = (int)$id;
            }
        } elseif (is_string($raw) && preg_match('/^\d+(?:\s*,\s*\d+)*$/', $raw)) {
            $ids = array_map('intval', explode(',', $raw));
        } else { throw new waException(shopMultilanguageassystBackendLocale::t('Выберите объекты для экспорта'), 400); }
        $data = shopMultilanguageassystExchange::export($type, $ids, waRequest::get('target_language',null,'string'));
        $this->getResponse()->addHeader('Content-Type', 'application/json; charset=utf-8');
        $batch = max(1, waRequest::get('list_page', 1, 'int'));
        $this->getResponse()->addHeader('Content-Disposition', 'attachment; filename="multilanguageassyst-'.$type.'-batch-'.$batch.'-'.strtolower($data['target_language']).'.json"');
        $this->getResponse()->addHeader('Cache-Control', 'no-store');
        $this->getResponse()->sendHeaders();
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

