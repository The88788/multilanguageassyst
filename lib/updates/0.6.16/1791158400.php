<?php
// Copyright (c) 2026 Владислав, the88788.com.
// SPDX-License-Identifier: LGPL-3.0-only
// Refresh event handlers after adding storefront and comparison integration.
waAppConfig::clearAutoloadCache('shop');
waEvent::clearCache();
