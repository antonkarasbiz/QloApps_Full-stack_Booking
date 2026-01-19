{*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License version 3.0
* that is bundled with this package in the file LICENSE.md
* It is also available through the world-wide-web at this URL:
* https://opensource.org/license/osl-3-0-php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to support@qloapps.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to a newer
* versions in the future. If you wish to customize this module for your needs
* please refer to https://store.webkul.com/customisation-guidelines for more information.
*
* @author Webkul IN
* @copyright Since 2010 Webkul
* @license https://opensource.org/license/osl-3-0-php Open Software License version 3.0
*}
{if $languages}
    <input type="hidden" id="languages-data" value="{$languages|@json_encode|escape:'html':'UTF-8'}">
    <label class="seo-label">{l s='Seo Preview'}</label>
    {foreach from=$languages item=language}
        {assign var=id_lang value=$language.id_lang}
        <div class="panel lang-{$id_lang} translatable-field lang-{$id_lang} wk_text_field_all wk_text_field_{$id_lang}"
            data-lang-id="{$id_lang}" {if isset($currentLang) && $currentLang.id_lang != $id_lang}style="display:none;" {/if}>
            {if isset($inputs.meta_title[$id_lang])}
                <div id="meta-title_{$id_lang}" class="seo-meta-title primary">
                    {$inputs.meta_title[$id_lang]|default:''|escape:'html':'UTF-8'}
                </div>
            {/if}
            {if isset($inputs.meta_description[$id_lang])}
                <div id="meta-description_{$id_lang}" class="seo-meta-description">
                    {$inputs.meta_description[$id_lang]|default:''|escape:'html':'UTF-8'}
                </div>
            {/if}
            {if isset($inputs.meta_keywords[$id_lang])}
                <div id="meta-keywords_{$id_lang}" class="seo-meta-keywords">
                    {$inputs.meta_keywords[$id_lang]|default:''|escape:'html':'UTF-8'}
                </div>
            {/if}
            {strip}
                <strong>
                    {if isset($preview_link[$id_lang]) && is_array($preview_link[$id_lang])}
                        <span class="preview-base">
                            {$preview_link[$id_lang][0]|escape:'html':'UTF-8'}
                        </span>
                        <span id="friendly-url_{$id_lang}">
                            {$inputs.link_rewrite[$id_lang]}
                        </span>
                        <span class="preview-extension">
                            {$preview_link[$id_lang][1]|default:''|escape:'html':'UTF-8'}
                        </span>
                    {elseif isset($preview_link[$id_lang])}
                        <span class="preview-base">
                            {$preview_link[$id_lang]|escape:'html':'UTF-8'}
                        </span>
                        <span id="friendly-url_{$id_lang}">
                            {$inputs.link_rewrite[$id_lang]}
                        </span>
                    {/if}
                </strong>
            {/strip}
        </div>
    {/foreach}
    {addJsDef languages=$languages}
{/if}
<script>
    $(document).ready(function() {
        languages.forEach(function(language) {
            var idLang = language.id_lang;
            $('#meta_title_' + idLang + ', #meta_description_' + idLang + ', #link_rewrite_' + idLang)
                .on('keyup', function() {
                    updateSeoPreview(idLang);
                });
            $(document).on('click keyup', '.wk_text_field_' + idLang + ' .tagify-container',
        function() {
                updateMetaKeywordsPreview(idLang);
            });
        });
    });

    function updateSeoPreview(idLang) {
        var title = $('#meta_title_' + idLang).val();
        var description = $('#meta_description_' + idLang).val();
        var link = $('#link_rewrite_' + idLang).val();

        if (title !== undefined) {
            $('#meta-title_' + idLang).text(title);
        }
        if (description !== undefined) {
            $('#meta-description_' + idLang).text(description);
        }
        if (link !== undefined) {
            $('#friendly-url_' + idLang).text(link.trim());
        }
    }

    function updateMetaKeywordsPreview(idLang) {
        var keywords = [];
        $('.wk_text_field_' + idLang + ' .tagify-container span').each(function() {
            var text = $(this).clone().children('a').remove().end().text().trim();
            if (text.length) {
                keywords.push(text);
            }
        });
        var keywordText = keywords.join(', ');
        var preview = $('#meta-keywords_' + idLang);

        if (keywordText.length) {
            preview.text(keywordText).show();
        } else {
            preview.hide();
        }
    }
</script>
<style>
    .seo-meta-title {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .seo-meta-description {
        font-size: 14px;
        margin-bottom: 5px;
    }
    .seo-label {
        font-size: 14px;
    }
    .preview-base,
    .preview-extension {
        color: #666;
    }
    .seo-meta-keywords {
        font-size: 13px;
        color: #777;
        margin-top: 4px;
    }
</style>