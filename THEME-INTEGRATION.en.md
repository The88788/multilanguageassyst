# Theme integration

Shop-Script events prepare translations before HTML is rendered. Some themes load content inside their templates, after those events. Use the context helper at the point where data is displayed.

## Prepare template variables

Add this block at the start of the relevant template, for example `page.html`, and before breadcrumbs in `main.html`:

```smarty
{$ml_context = $wa->shop->multilanguageassystContext()}
{if is_array($ml_context)}
    {foreach $ml_context as $ml_key=>$ml_value}
        {assign var=$ml_key value=$ml_value}
    {/foreach}
{/if}
```

It updates the executing template's local variables. Updating global variables through the older `multilanguageassystBootstrap()` helper may not affect local variables that already exist. The block prints nothing, runs with Smarty compilation caching, and receives an empty result when the plugin is disabled.

Place it where the application content is rendered. A Site header alone may run too late.

## Lists fetched by the theme

Translate a list immediately after the theme fetches it. Keep your theme's existing query arguments:

```smarty
{$categories = $wa->shop->categories(0, null, true)}
{if class_exists('shopMultilanguageassystPlugin') && shopMultilanguageassystPlugin::enabled()}
    {$categories = shopMultilanguageassystPlugin::categories($categories)}
{/if}

{$shop_pages = $wa->shop->pages()}
{if class_exists('shopMultilanguageassystPlugin') && shopMultilanguageassystPlugin::enabled()}
    {$shop_pages = shopMultilanguageassystPlugin::pages($shop_pages)}
{/if}
```

Class and enabled checks make disabling the plugin safe. `pages()` translates Shop-Script pages. Do not pass Site or Blog pages with unrelated IDs.

## Language switcher

Place a switcher where it fits your theme. Available languages come from `shopMultilanguageassystLanguages::all()`, the current language from `shopMultilanguageassystPlugin::language()`, and each link from `shopMultilanguageassystPlugin::languageUrl($code)`. Escape labels and links.

Use the class `multilanguageassyst-theme-language` to avoid a duplicate automatic switcher. Hide the switcher when only one language is available. Storefront URLs use `multilanguageassyst_lang`; the cookie is `shop_multilanguageassyst`.

## Interface text and customer data

Mark public interface areas with `data-multilanguageassyst-ui`. Mark customer names, addresses, comments, and other personal data with `data-multilanguageassyst-personal`, so text matching does not translate them. Relative date labels can use `data-multilanguageassyst-date`.

JavaScript supports the standard dynamic order form `#wa-order-form-wrapper`, its modal windows, and HTML updates. Third-party widgets require separate integration and verification.

For the standard theme, add the context helper to `order.html`, `checkout.success.html`, `my.order.html`, `my.orders.html`, `my.nav.html`, and `my.profile.html` in the Site theme where applicable.

Delivery and payment names, checkout messages, and standard country/region labels are available in **Content > Interface phrases**. Codes and stored address values are unchanged. Cities supplied by external delivery plugins may need their own integration.

Account order pages translate product names for display by product ID only when the stored name matches the current original product name. Historical or manually changed names are retained. Saved orders, totals, quantities, order numbers, contacts, and addresses are unchanged.

Theme block translations containing `{$date}` must preserve that variable. Other Smarty code in translations is not allowed. Preserve official attribution links and their display conditions.

## Check your theme

Check the home page, search, product pages, parent and child categories, shop pages and nested pages, navigation, breadcrumbs, features, SKU names, cart, checkout, and account pages. Switch back to the source language and repeat without clearing the cache. Check disabled plugin behavior and the case with no target languages.

Clear Webasyst's cache in **Installer > Settings** if event handlers do not run after manual installation. An old event-handler cache can survive a file upload.

Theme changes are separate from the plugin archive. Transfer integration blocks to your own theme rather than replacing complete templates from another theme version. Arbitrary themes require verification; core framework and Shop-Script files do not need modification.
