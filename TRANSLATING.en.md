# Translate content using JSON files

## Export and import

1. Open **Translations** in Shop-Script or **MultiLanguageAssyst** in Plugins.
2. Open **Languages** and register the target language if necessary.
3. In **Export**, select content and target language. Search by name or model and click **Show**.
4. Select individual items or the entire current batch, then click **Download selected texts**. IDs are included automatically. Use **Next 100** or the batch number to move to another batch. Selections apply only to the current batch. Products are sorted from newest to oldest; adding products can change batch contents.
5. Keep the original file and give a copy to a translator. Export contains the selected public content, without customers, orders, or API keys.
6. Fill only `translation` values. Preserve every ID, key, source text, and hash.
7. In **Import**, choose the translated file. Click **Validate file**, review the target language and counts, then click **Import**. The language comes from `target_language`, and must be registered in the plugin. Empty translations do not remove existing translations.
8. Check the storefront. Enable replacement only when you intend to update different existing translations. Import each file separately. The log shows the file name and result.

For manual editing, use **Content** and click an item's name. The product editor includes its feature names and text values. These values are shared: all products with the same feature and value use the same translation. Feature export remains separate.

## Using ChatGPT or a translator

ChatGPT can prepare a draft. For better quality, ask a translator or a native speaker to review it, especially technical specifications, delivery and return conditions, and checkout messages. The plugin validates file structure and source matching, not linguistic quality.

Attach one exported JSON file and use a prompt below. Replace `[target language]` with the language matching `target_language`, for example "Kazakh in Cyrillic" for `kk_KZ`.

Save the complete output as UTF-8 JSON. Copy only JSON, without Markdown fences or explanations. If the response is incomplete, export fewer items and translate again. The 100-item limit is not a guarantee that the output will fit into an AI response.

## Products, categories, pages, features, services, and theme blocks

```text
Translate the texts in the attached JSON file into [target language] for an online shop.
Return the complete valid JSON with the original structure, without explanations or omissions.
Change only translation values. Fill empty translations and retain translations that are already filled.
Do not change format, source_language, target_language, source, source_hash, needs_review,
entity_type, entity_id, feature_code, feature_type, theme_app, theme_id, setting_key, or field keys.
Preserve model names, brands, SKUs, numbers, measurement units, URLs, and contact details.
Preserve existing HTML structure and attributes. Translate visible text only.
Do not add scripts, event handlers, new iframe elements, or JavaScript URLs.
Preserve supported placeholders such as {$date} exactly. Do not add Smarty code.
Do not translate content inside elements marked notranslate.
If a source value contains both languages, use the target-language wording without duplicating the source-language text.
Keep JSON escaping valid. Return the full file.
```

For services, `name` is the service name and `variant_ID` is an option name. Preserve their IDs. Feature IDs and values belong to the shop that exported the file; do not import another shop's export without matching IDs and source texts.

## Interface phrases

```text
Translate the attached interface phrase JSON into [target language].
Return the complete valid JSON, without explanations or Markdown fences.
Change only empty translation values. Preserve all IDs, keys, source texts, hashes, and existing translations.
Use short, clear labels suitable for buttons, menus, checkout, and account pages.
Preserve %s, %d, numbered placeholders, and all other substitution parameters in their original order.
Do not add HTML, line breaks that change meaning, or template code.
Preserve brands, numbers, currency codes, URLs, and contact details.
Return the full JSON file.
```

## Correcting a translated file

```text
Review the attached translation JSON for [target language].
Correct only translation values. Preserve the JSON structure, all IDs, source values, source_hash,
target_language, and other metadata. Preserve HTML, links, numbers, units, and substitution placeholders.
Do not add scripts or template code. Return the complete valid JSON without explanations.
```

Keep the original export for comparison. Validate the corrected file and explicitly allow replacement if needed. A source mismatch requires a new export from the shop; do not edit the hash to bypass validation.

## Import errors

- Wrong language: check `target_language` and register that language in **Languages**.
- Source text changed: export the affected item again and apply the translation to the new file.
- HTML in a plain text field: remove HTML from names and other plain text fields. Descriptions allow supported markup.
- Unsupported markup: retain only supported content. The plugin permits selected source-matching embedded elements, not arbitrary new iframe or script code.
- Existing translations: validate first, then enable replacement only for intentional changes.
- Incomplete or oversized JSON: export a smaller selection. Maximum file size is 5 MB.
