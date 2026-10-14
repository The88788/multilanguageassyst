<?php
// Copyright (c) 2026 Владислав, the88788.com.
// SPDX-License-Identifier: LGPL-3.0-only
class shopMultilanguageassystPluginBackendLayout extends shopBackendLayout
{
    public function __construct()
    {
        parent::__construct();
        // Use Shop-Script's layout, including its legacy UI fallback.
        // Automatic resolution treats this subclass as a plugin template.
        $this->template = 'Backend';
    }

    protected function getPage()
    {
        return 'plugins';
    }
}
