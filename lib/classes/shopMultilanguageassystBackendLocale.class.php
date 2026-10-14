<?php
// Copyright (c) 2026 Vladislav, the88788.com.
// SPDX-License-Identifier: LGPL-3.0-only
class shopMultilanguageassystBackendLocale
{
    public static function t($message)
    {
        waLocale::loadByDomain(array('shop', 'multilanguageassyst'));
        return _wd('shop_multilanguageassyst', (string)$message);
    }

    public static function javascript()
    {
        $keys = json_decode(file_get_contents(dirname(__DIR__, 2).'/locale/backend-js-messages.json'), true);
        $messages = array();
        foreach ($keys as $key) {
            $messages[$key] = self::t($key);
        }
        return json_encode($messages, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    public static function journalText($text)
    {
        static $catalog;
        if ($catalog === null) {
            $catalog = json_decode(file_get_contents(dirname(__DIR__, 2).'/locale/backend-messages.json'), true);
        }
        $text = (string)$text;
        $source = array_search($text, $catalog, true);
        if ($source !== false) { return self::t($source); }
        if (isset($catalog[$text])) { return self::t($text); }
        if (preg_match('/^([a-z]{2,3}_[A-Z]{2}): (?:объектов|items) ([0-9]+), (?:полей|fields) ([0-9]+), (?:замен|replacements) ([0-9]+)$/u', $text, $m)) {
            return $m[1].self::t(': объектов ').$m[2].self::t(', полей ').$m[3].self::t(', замен ').$m[4];
        }
        if (preg_match('/^([a-z_]+ #[0-9]+): (?:перевод сохранён|translation saved)$/u', $text, $m)) {
            return $m[1].self::t(': перевод сохранён');
        }
        if (preg_match('/^(?:Язык |Language )([a-z]{2,3}_[A-Z]{2})(?: удалён из витрины; переводы сохранены| removed from the storefront; translations retained)$/u', $text, $m)) {
            return self::t('Язык ').$m[1].self::t(' удалён из витрины; переводы сохранены');
        }
        // Unknown diagnostics may contain user text. Keep them exactly as recorded.
        return $text;
    }
}
