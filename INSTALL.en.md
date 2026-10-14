# Install without the marketplace

[Русский](INSTALL.md)

These steps are for a self-hosted Webasyst installation with file access. In Webasyst Cloud, use Installer and the installation methods supported by your hosting service.

## Requirements

- An existing Webasyst installation with Shop-Script 11.1.0 or later.
- Installer application 3.2.0 or later.
- PHP 7.4.24 or later with mbstring.
- Access to site files and Shop-Script administrator permissions.

The plugin does not include Webasyst or Shop-Script. Install them first.

## 1. Back up your site

Back up the database and site files. If the plugin is already installed, also keep a copy of its folder and `wa-config/apps/shop/plugins.php`. Never publish these backups on GitHub.

## 2. Download the release archive

Open [Releases](https://github.com/The88788/multilanguageassyst/releases) and download the attached installation asset named `multilanguageassyst-VERSION.tar.gz`.

Use this asset, not GitHub's automatically generated Source code archives. Extract it with 7-Zip, tar, or your hosting file manager. The resulting `multilanguageassyst` folder must contain `lib`, `templates`, `js`, `locale`, and `img`.

## 3. Upload the files

Copy the `multilanguageassyst` folder into:

```text
wa-apps/shop/plugins/multilanguageassyst/
```

Check the final path:

```text
wa-apps/shop/plugins/multilanguageassyst/lib/config/plugin.php
```

There must not be another nested `multilanguageassyst` folder. Include hidden `.htaccess` files. The web server user must be able to read the plugin files. Do not use permissions 777.

## 4. Enable the plugin

Open `wa-config/apps/shop/plugins.php` in a text editor. Add this entry inside its existing returned array:

```php
'multilanguageassyst' => true,
```

Keep all other entries. If this key already exists, set it to `true` instead of adding a duplicate.

If the file does not exist, create it with this content:

```php
<?php
return array(
    'multilanguageassyst' => true,
);
```

Save as UTF-8 without BOM. When editing an existing file, preserve its structure and place the entry inside the returned array.

## 5. Clear the cache and open the plugin

1. Open Installer settings in the backend and clear Webasyst's cache. Manual uploads can leave an old event-handler or class cache.
2. Reload the browser with Ctrl+F5.
3. Open **Translations** in the Shop-Script menu, or select the plugin under **Plugins**.

For the standard backend URL, you can open:

```text
https://YOUR-SITE/webasyst/shop/?plugin=multilanguageassyst&module=backend
```

Use your actual Shop-Script backend URL if the admin path or installation subdirectory differs. Webasyst creates the translation table automatically when the plugin is first loaded. No manual SQL import is needed.

## 6. Configure languages and your theme

Add a target language in **Languages**. Test a manual translation of one product, or export, validate, and import a small file. Follow [THEME-INTEGRATION.en.md](THEME-INTEGRATION.en.md) to display translations and place the language switcher in your theme. The plugin does not replace theme templates automatically.

See [TRANSLATING.en.md](TRANSLATING.en.md) for translation instructions and prompts. The plugin's backend language follows your Webasyst user profile, independently of storefront languages.

## Manual updates

1. Back up files and the database.
2. Download the new installation asset from Releases.
3. Upload its contents over the existing files in `wa-apps/shop/plugins/multilanguageassyst/`. Do not uninstall the plugin using Installer before updating.
4. Keep the enabled entry in `plugins.php`. Clear Webasyst's cache and open the plugin. Webasyst runs the required plugin updates through its normal update mechanism.
5. Check the version, existing translations, and storefront.

You do not need to import all translations again. If you customized the plugin code, compare your changes before replacing files. Theme template customizations are updated separately.

## Disable or uninstall

To disable the plugin, set `'multilanguageassyst' => false` in `plugins.php` and clear the cache. Translations are retained. Theme integration must handle a disabled plugin as described in the theme guide.

Uninstalling through Installer removes the translation table and settings. Back up your data first. Deleting only the plugin folder does not fully remove its database data.

## Troubleshooting

- Check the path to `lib/config/plugin.php`, uploaded files, and the `plugins.php` entry.
- Check PHP requirements and backend user permissions.
- Clear Webasyst's cache and reload the browser.
- Check `wa-log/` and the server's PHP error log. Remove passwords, customer data, and other private information before sharing logs.
