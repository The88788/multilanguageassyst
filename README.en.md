# MultiLanguageAssyst 0.6.18

[Русская документация](README.md) · [Webasyst COM](https://www.webasyst.com/store/plugin/shop/multilanguageassyst/) · [Webasyst RU](https://www.webasyst.ru/store/plugin/shop/multilanguageassyst/) · [GitHub Releases](https://github.com/The88788/multilanguageassyst/releases)

Requires Webasyst, Shop-Script 11.1.0 or later, and PHP 7.4.24 or later. The repository contains the plugin, not the Webasyst framework or Shop-Script. Install through Webasyst Installer or use the installation archive attached to a release. The plugin folder is `wa-apps/shop/plugins/multilanguageassyst/`.

Add storefront languages to your Shop-Script shop. Enter translations manually or import them from JSON files. The plugin does not translate automatically or connect to translation APIs.

The backend follows the language selected in your Webasyst user settings. Russian and English are included. Backend language and storefront languages are separate settings.

## Supported content

- Product names, descriptions, summaries, and SEO fields available in the editor.
- Categories and Shop-Script pages.
- Feature names and text values shared across the catalog.
- Additional services and service options.
- Interface phrases and text settings in supported themes.

Numbers, prices, stock quantities, measurement units, customer data, and order records are not translated or modified. Blog and Photos support is not claimed for this release. Theme integration may require changes to your theme. Full SEO localization and automatic compatibility with every theme are not included.

## Get started

1. Install the plugin using Installer. Open **Translations** in the Shop-Script menu or select **MultiLanguageAssyst** in Plugins.
2. Add a target language in **Languages**. Use a locale code such as `en_US` or `kk_KZ` and a display name of your choice.
3. In **Content**, select the content type and target language. Search by name, click **Show**, then click an item to edit its translations. Saving returns you to the list.
4. To translate using files, open **Export**, select items, and download the JSON file. Each batch contains up to 100 items. Product features are exported separately, but can also be edited directly in the product editor.
5. Fill only `translation` values. Keep IDs, source texts, source hashes, and other JSON fields unchanged.
6. Open **Import**, choose the translated file, click **Validate file**, then **Import**. The target language comes from `target_language` in the file. Replacing different existing translations requires explicit permission.
7. Check the storefront in both languages. See [THEME-INTEGRATION.en.md](THEME-INTEGRATION.en.md) for theme setup and [TRANSLATING.en.md](TRANSLATING.en.md) for translation prompts.

## Interface phrases, services, and theme blocks

Use **Interface phrases** for menu labels, buttons, checkout messages, delivery and payment names, and currency labels available in the catalog. Use **Additional services and options** for service names and their options. Service translations take priority over translations of the same interface phrase.

Long text settings in supported themes appear under **Theme text blocks**. Preserve HTML, links, and supported placeholders. Keep official Webasyst and Shop-Script attribution links and their display conditions unchanged.

## Languages and log

Kazakh is included as a removable target language. You may remove it and add other languages. Removing a target language retains its translations: add the same code again to restore them. The source language cannot be removed. Changing the source language setting does not translate existing shop content.

The log stores the latest 200 import and other operation events. Older entries are removed automatically. Display 20, 50, 100, or all 200 entries per page. Full translations and file contents are not stored in the log.

## Cache and permissions

If a menu entry or event handler is missing after manual installation or an update, open **Installer > Settings** and use the cache clearing option. Then reload the browser with Ctrl+F5. Webasyst may retain event handler information from before the plugin was enabled.

Plugin management requires administrator access or permission to manage Shop-Script settings. Disabling the plugin retains translations. Uninstalling it using Installer removes its translation table and settings. Back up translations before uninstalling.

The plugin has partial PREMIUM support. It does not change product quantities, prices, or totals. Measurement unit name translation is not implemented, and all extended features have not been verified.

License: LGPL version 3 only. See COPYING.txt, COPYING.LESSER.txt, and NOTICE.md.

Author: Vladislav, [the88788.com](https://the88788.com).
