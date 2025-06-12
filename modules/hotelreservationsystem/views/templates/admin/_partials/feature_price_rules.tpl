
<div class="panel advanced_price_rule {if isset($rowsToHighlight) && in_array($key, $rowsToHighlight)}error-border{/if}" data-advanced_price_rule_index="{$key}">
    <input type="hidden" name="advance_price_rule[{$key}][id]" value="{if isset($advancePriceRule['id'])}{$advancePriceRule['id']}{/if}">
    <div class="row advance_price_rule_header {if $key}collapsed{/if}" data-toggle="collapse" data-target="#advanced_price_rule_{$key}">
        <div class="col-xs-10">
            {l s='Price rule restriction' mod='hotelreservationsystem'}
        </div>
        <div class="col-xs-1 pull-right">
            <div class="col-xs-8">
                <a class="btn btn-default remove_advanced_price_rule">
                    <i class="icon-trash"></i>
                </a>
            </div>
            <div class="col-xs-4">
                <i class="icon-caret-down"></i>
                <i class="icon-caret-up"></i>
            </div>
        </div>
    </div>
    <div id="advanced_price_rule_{$key}" class="collapse advanced_price_rule_body {if !$key}in{/if}">
        <div class="form-group">
            <label for="advance_price_rule[{$key}][date_selection_type]" class="control-label col-sm-4 col-xs-3">
                {l s='Date Selection type' mod='hotelreservationsystem'}
            </label>
            <div class="col-xs-6">
                <select class="form-control date_selection_type" name="advance_price_rule[{$key}][date_selection_type]" id="date_selection_type_{$key}">
                    <option value="{HotelRoomTypeFeaturePricing::DATE_SELECTION_TYPE_RANGE}" {if isset($advancePriceRule['date_selection_type']) && $advancePriceRule['date_selection_type'] == HotelRoomTypeFeaturePricing::DATE_SELECTION_TYPE_RANGE}selected="seleted"{/if}>
                        {l s='Date Range' mod='hotelreservationsystem'}
                    </option>
                    <option value="{HotelRoomTypeFeaturePricing::DATE_SELECTION_TYPE_SPECIFIC}" {if isset($advancePriceRule['date_selection_type']) && $advancePriceRule['date_selection_type'] == HotelRoomTypeFeaturePricing::DATE_SELECTION_TYPE_SPECIFIC}selected="seleted"{/if}>
                        {l s='Specific Date' mod='hotelreservationsystem'}
                    </option>
                </select>
            </div>
        </div>

        <div class="form-group specific_date_type_{$key}" {if !isset($advancePriceRule['date_selection_type']) || $advancePriceRule['date_selection_type'] == HotelRoomTypeFeaturePricing::DATE_SELECTION_TYPE_RANGE}style="display:none;"{/if}>
            <label class="col-sm-4 col-xs-3 control-label required" for="advance_price_rule[{$key}][specific_date]" >
                {l s='Specific Date' mod='hotelreservationsystem'}
            </label>
            <div class="col-xs-6">
                <input type="text" id="specific_date_{$key}" name="advance_price_rule[{$key}][specific_date]" class="specific_date form-control datepicker-input" value="{if isset($advancePriceRule['specific_date'])}{$advancePriceRule['specific_date']}{else}{$date_from}{/if}" readonly/>
            </div>
        </div>

        <div class="form-group date_range_type_{$key}" {if isset($advancePriceRule['date_selection_type']) && $advancePriceRule['date_selection_type'] != HotelRoomTypeFeaturePricing::DATE_SELECTION_TYPE_RANGE}style="display:none;"{/if}>
            <label class="col-sm-4 col-xs-3 control-label required" for="advance_price_rule[{$key}][date_from]" >
                {l s='Date From' mod='hotelreservationsystem'}
            </label>
            <div class="col-xs-6">
                <input type="text" id="feature_plan_date_from_{$key}" name="advance_price_rule[{$key}][date_from]" class="feature_plan_date_from form-control datepicker-input" value="{if isset($advancePriceRule['date_from'])}{$advancePriceRule['date_from']}{else}{$date_from}{/if}" readonly/>
            </div>
        </div>
        <div class="form-group date_range_type_{$key}" {if isset($advancePriceRule['date_selection_type']) && $advancePriceRule['date_selection_type'] != HotelRoomTypeFeaturePricing::DATE_SELECTION_TYPE_RANGE}style="display:none;"{/if}>
            <label class="col-sm-4 col-xs-3 control-label required" for="advance_price_rule[{$key}][date_to]" >
                {l s='Date To' mod='hotelreservationsystem'}
            </label>
            <div class="col-xs-6">
                <input type="text" id="feature_plan_date_to_{$key}" name="advance_price_rule[{$key}][date_to]" class="feature_plan_date_to form-control datepicker-input" value="{if isset($advancePriceRule['date_to'])}{$advancePriceRule['date_to']}{else}{$date_to}{/if}" readonly/>
            </div>
        </div>

        <div class="form-group special_days_content_{$key}" {if isset($advancePriceRule['date_selection_type']) && $advancePriceRule['date_selection_type'] != HotelRoomTypeFeaturePricing::DATE_SELECTION_TYPE_RANGE}style="display:none;"{/if}>
            <label class="control-label col-sm-4 col-xs-3" for="advance_price_rule[{$key}][is_special_days_exists]">
                <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="{l s='Enable this option to restrict this rule to specific week days (for example, weekends) of the selected date range. If disabled, rule will be applicable to all week days.' mod='hotelreservationsystem'}">
                    {l s='Restrict to Week Days' mod='hotelreservationsystem'}
                </span>
            </label>
            <div class="col-xs-6">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" value="1" id="advance_price_rule[{$key}][is_special_days_exists_on]" name="advance_price_rule[{$key}][is_special_days_exists]" class="is_special_days_exists" {if isset($advancePriceRule['is_special_days_exists']) && $advancePriceRule['is_special_days_exists']}checked="checked"{/if}>
                    <label for="advance_price_rule[{$key}][is_special_days_exists_on]">{l s='Yes' mod='hotelreservationsystem'}</label>
                    <input type="radio" value="0" id="advance_price_rule[{$key}][is_special_days_exists_off]" name="advance_price_rule[{$key}][is_special_days_exists]" class="is_special_days_exists" {if !isset($advancePriceRule['is_special_days_exists']) || !$advancePriceRule['is_special_days_exists']}checked="checked"{/if}>
                    <label for="advance_price_rule[{$key}][is_special_days_exists_off]">{l s='No' mod='hotelreservationsystem'}</label>
                    <a class="slide-button btn"></a>
                </span>
            </div>
        </div>

        <div class="form-group week_days week_days_{$key}" {if isset($advancePriceRule['special_days']) && is_array($advancePriceRule['special_days']) && isset($advancePriceRule['is_special_days_exists']) && $advancePriceRule['is_special_days_exists']}style="display:block;"{/if}>
            <label for="advance_price_rule[{$key}][special_days]" class="control-label col-sm-4 col-xs-3">
                {l s='Select Week Days' mod='hotelreservationsystem'}
            </label>
            <div class="col-xs-8 checkboxes-wrap">
            {foreach $week_days as $dayVal => $day}
                <div class="day-wrap">
                    <input type="checkbox" name="advance_price_rule[{$key}][special_days][]" value="{$dayVal}" {if (isset($advancePriceRule['special_days']) && is_array($advancePriceRule['special_days']) && in_array($dayVal, $advancePriceRule['special_days']))}checked="checked" {/if}/>
                    <p>{$day}</p>
                </div>
            {/foreach}
            </div>
        </div>
    </div>
</div>
