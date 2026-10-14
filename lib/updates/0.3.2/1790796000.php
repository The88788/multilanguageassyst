<?php
// Copyright (c) 2026 Владислав, the88788.com.
// SPDX-License-Identifier: LGPL-3.0-only
$model = new waModel();
$model->query('ALTER TABLE shop_multilanguageassyst_translation CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
