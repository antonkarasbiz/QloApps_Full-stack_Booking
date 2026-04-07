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
    <label class="seo-label">
        <span class="label-tooltip" data-toggle="tooltip" title="{l s='Preview how this page may appear in search results.'}">
            {l s='SEO Preview'}
        </span>
    </label>
    {foreach from=$languages item=language}
        {assign var=id_lang value=$language.id_lang}
        <div class="panel lang-{$id_lang} translatable-field lang-{$id_lang} wk_text_field_all wk_text_field_{$id_lang}"
            data-lang-id="{$id_lang}" {if isset($currentLang) && $currentLang.id_lang != $id_lang}style="display:none;" {/if}>
            {assign var=seoPreviewTitleDefault value=''}
            {assign var=seoPreviewDescriptionDefault value=''}
            {if isset($inputs.name[$id_lang]) && $inputs.name[$id_lang]|trim}
                {assign var=seoPreviewTitleDefault value=$inputs.name[$id_lang]}
            {elseif isset($product) && isset($product->name[$id_lang]) && $product->name[$id_lang]|trim}
                {assign var=seoPreviewTitleDefault value=$product->name[$id_lang]}
            {elseif isset($inputs.link_rewrite[$id_lang]) && $inputs.link_rewrite[$id_lang]|trim}
                {assign var=seoPreviewTitleDefault value=$inputs.link_rewrite[$id_lang]}
            {/if}

            {if isset($inputs.description_short[$id_lang]) && $inputs.description_short[$id_lang]|trim}
                {assign var=seoPreviewDescriptionDefault value=$inputs.description_short[$id_lang]|strip_tags}
            {elseif isset($product) && isset($product->description_short[$id_lang]) && $product->description_short[$id_lang]|trim}
                {assign var=seoPreviewDescriptionDefault value=$product->description_short[$id_lang]|strip_tags}
            {/if}
            <span id="seo-preview-title-default_{$id_lang}" class="seo-preview-default">{$seoPreviewTitleDefault|escape:'html':'UTF-8'}</span>
            <span id="seo-preview-description-default_{$id_lang}" class="seo-preview-default">{$seoPreviewDescriptionDefault|escape:'html':'UTF-8'}</span>
            <div class="seo-preview-url">
                {if isset($preview_link[$id_lang])}
                    <a
                        id="seo-preview-url-link_{$id_lang}"
                        class="seo-preview-url-text seo-preview-url-link"
                        {if is_array($preview_link[$id_lang])}
                            href="{$preview_link[$id_lang][0]|escape:'html':'UTF-8'}{$inputs.link_rewrite[$id_lang]|default:''|escape:'html':'UTF-8'}{$preview_link[$id_lang][1]|default:''|escape:'html':'UTF-8'}"
                        {else}
                            href="{$preview_link[$id_lang]|escape:'html':'UTF-8'}{$inputs.link_rewrite[$id_lang]|default:''|escape:'html':'UTF-8'}"
                        {/if}
                        target="_blank"
                        rel="noopener"
                        title="{l s='Open preview link'}"
                    >
                {else}
                    <div class="seo-preview-url-text">
                {/if}
                    {strip}
                        {if isset($preview_link[$id_lang]) && is_array($preview_link[$id_lang])}
                            <span class="preview-base">
                                {$preview_link[$id_lang][0]|escape:'html':'UTF-8'}
                            </span>
                            <span id="friendly-url_{$id_lang}">
                                {$inputs.link_rewrite[$id_lang]|default:''|escape:'html':'UTF-8'}
                            </span>
                            <span class="preview-extension">
                                {$preview_link[$id_lang][1]|default:''|escape:'html':'UTF-8'}
                            </span>
                        {elseif isset($preview_link[$id_lang])}
                            <span class="preview-base">
                                {$preview_link[$id_lang]|escape:'html':'UTF-8'}
                            </span>
                            <span id="friendly-url_{$id_lang}">
                                {$inputs.link_rewrite[$id_lang]|default:''|escape:'html':'UTF-8'}
                            </span>
                        {/if}
                    {/strip}
                {if isset($preview_link[$id_lang])}
                    </a>
                {else}
                    </div>
                {/if}
                <span class="seo-preview-more" aria-hidden="true">⋮</span>
            </div>
            {if isset($inputs.meta_title[$id_lang])}
                <div id="meta-title_{$id_lang}" class="seo-meta-title primary">
                    {assign var=seoPreviewTitle value=$inputs.meta_title[$id_lang]|default:''}
                    {if $seoPreviewTitle|trim}
                        {$seoPreviewTitle|escape:'html':'UTF-8'}
                    {else}
                        {$seoPreviewTitleDefault|escape:'html':'UTF-8'}
                    {/if}
                </div>
            {/if}
            {if isset($inputs.meta_description[$id_lang])}
                <div id="meta-description_{$id_lang}" class="seo-meta-description">
                    {assign var=seoPreviewDescription value=$inputs.meta_description[$id_lang]|default:''}
                    {if $seoPreviewDescription|trim}
                        {$seoPreviewDescription|escape:'html':'UTF-8'}
                    {else}
                        {$seoPreviewDescriptionDefault|escape:'html':'UTF-8'}
                    {/if}
                </div>
            {/if}
        </div>
    {/foreach}
    <div class="alert alert-info seo-preview-note">
        <p>
            <b>{l s='Note'}</b>:
            {l s='Leaving Meta title or Meta description blank will use the default values in the preview (Name and Short Description).' }
        </p>
    </div>
    {addJsDef languages=$languages}
    <script>
        $(document).ready(function() {
            if (typeof languages === 'undefined' || !languages || typeof languages.forEach !== 'function') {
                return;
            }

            languages.forEach(function(language) {
                var idLang = language.id_lang;
                $('#meta_title_' + idLang + ', #meta_description_' + idLang + ', #link_rewrite_' + idLang)
                    .on('keyup', function() {
                        updateSeoPreview(idLang);
                    });
                $('#name_' + idLang + ', #description_short_' + idLang + ', #short_description_' + idLang)
                    .on('keyup change', function() {
                        updateSeoPreview(idLang);
                    });
            });
        });

        function stripTags(text) {
            return $('<div/>').html(text || '').text();
        }

        function updateSeoPreviewDefaults(idLang) {
            var titleDefaultEl = $('#seo-preview-title-default_' + idLang);
            var descriptionDefaultEl = $('#seo-preview-description-default_' + idLang);

            var nameValue = ($('#name_' + idLang).val() || '').trim();
            if (nameValue.length) {
                titleDefaultEl.text(nameValue);
            }

            var descriptionValue = '';
            var descriptionShortField = $('#description_short_' + idLang);
            if (descriptionShortField.length) {
                descriptionValue = descriptionShortField.val() || '';
            } else {
                var hotelShortDescriptionField = $('#short_description_' + idLang);
                if (hotelShortDescriptionField.length) {
                    descriptionValue = hotelShortDescriptionField.val() || '';
                }
            }
            descriptionValue = stripTags(descriptionValue).trim();
            if (descriptionValue.length) {
                descriptionDefaultEl.text(descriptionValue);
            }
        }

        function updateSeoPreview(idLang) {
            var title = $('#meta_title_' + idLang).val();
            var description = $('#meta_description_' + idLang).val();
            var link = $('#link_rewrite_' + idLang).val();

            updateSeoPreviewDefaults(idLang);

            if (title !== undefined) {
                var titlePreview = $('#meta-title_' + idLang);
                var defaultTitle = $('#seo-preview-title-default_' + idLang).text() || '';
                var valueTitle = (title || '').trim();
                titlePreview.text(valueTitle.length ? valueTitle : defaultTitle);
            }
            if (description !== undefined) {
                var descriptionPreview = $('#meta-description_' + idLang);
                var defaultDescription = $('#seo-preview-description-default_' + idLang).text() || '';
                var valueDescription = (description || '').trim();
                descriptionPreview.text(valueDescription.length ? valueDescription : defaultDescription);
            }
            if (link !== undefined) {
                $('#friendly-url_' + idLang).text(link.trim());
            }
            updateSeoPreviewLink(idLang);
        }

        function updateSeoPreviewLink(idLang) {
            var urlLink = $('#seo-preview-url-link_' + idLang);
            if (!urlLink.length) {
                return;
            }

            var container = $('.wk_text_field_' + idLang);
            var base = container.find('.preview-base').text() || '';
            var slug = container.find('#friendly-url_' + idLang).text() || '';
            var ext = container.find('.preview-extension').text() || '';
            var href = (base + slug + ext).trim();

            if (href.length) {
                urlLink.attr('href', href);
            }
        }

    </script>
{/if}
<style>
    .seo-preview-url {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        flex-wrap: nowrap;
        font-size: 12px;
        color: #202124;
        margin-bottom: 4px;
    }
    .seo-preview-url-text {
        flex: 0 1 auto;
        min-width: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .seo-preview-more {
        flex: 0 0 auto;
        margin-left: 6px;
        color: #5f6368;
        font-size: 16px;
        line-height: 1;
        user-select: none;
    }
    .seo-meta-title {
        color: #1a0dab;
        font-size: 18px;
        font-weight: normal;
        line-height: 1.3;
        margin-bottom: 4px;
    }
    .seo-meta-description {
        font-size: 14px;
        color: #4d5156;
        line-height: 1.4;
        margin-bottom: 5px;
    }
    .seo-label {
        font-size: 14px;
        font-weight: normal;
    }
    .seo-label span.label-tooltip {
        display: inline-block;
        text-decoration: none;
        font-size: 13px;
        font-weight: normal;
        color: #3586AE;
        background-color: #E3F4FC;
        border: none;
        border-bottom: solid 1px #D8EDF7;
        padding: 0 5px;
        border-radius: 3px;
    }
    .seo-preview-url-link {
        color: inherit !important;
        text-decoration: none;
    }
    .seo-preview-url-link * {
        color: inherit !important;
    }
    .seo-preview-url-link:visited,
    .seo-preview-url-link:hover,
    .seo-preview-url-link:focus,
    .seo-preview-url-link:active {
        color: inherit !important;
    }
    .seo-preview-url-link:hover,
    .seo-preview-url-link:focus {
        text-decoration: none;
    }
    .seo-preview-default {
        display: none;
    }
    .preview-base,
    .preview-extension {
        color: inherit;
    }
    .seo-preview-note {
        margin-top: 10px;
    }
</style>
