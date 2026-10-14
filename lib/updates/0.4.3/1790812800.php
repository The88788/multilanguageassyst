<?php
// Copyright (c) 2026 Владислав, the88788.com.
// SPDX-License-Identifier: LGPL-3.0-only
// Register new sidebar and storefront hooks after an existing installation is updated.
waEvent::clearCache();
waAppConfig::clearAutoloadCache('shop');
