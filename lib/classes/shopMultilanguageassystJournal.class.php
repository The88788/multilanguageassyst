<?php
// Copyright (c) 2026 Владислав, the88788.com.
// SPDX-License-Identifier: LGPL-3.0-only
class shopMultilanguageassystJournal
{
    public static function entries() { $rows=json_decode((new waAppSettingsModel())->get(array('shop','multilanguageassyst'),'journal','[]'),true);return is_array($rows)?$rows:array(); }
    public static function fileName($name) {
        $name=basename(str_replace('\\','/',(string)$name));
        return mb_substr(preg_replace('/[\x00-\x1f\x7f]/u','',$name),0,200);
    }
    public static function record($action,$status,$message,$fileName='') {
        shopMultilanguageassystContent::assertNotTemplate();
        try {
            $rows=self::entries();array_unshift($rows,array('time'=>date('Y-m-d H:i:s'),'action'=>$action,'status'=>$status,'message'=>mb_substr($message,0,500),'file'=>self::fileName($fileName)));
            (new waAppSettingsModel())->set(array('shop','multilanguageassyst'),'journal',json_encode(array_slice($rows,0,200)));
        } catch(Exception $e) { waLog::log('Unable to record translation operation','shop/multilanguageassyst.log'); }
    }
}
