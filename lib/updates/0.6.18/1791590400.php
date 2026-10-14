<?php
// Copyright (c) 2026 Vladislav, the88788.com.
// SPDX-License-Identifier: LGPL-3.0-only
// Load the backend localization helper after an update.
waAppConfig::clearAutoloadCache('shop');
waEvent::clearCache();
