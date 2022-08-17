require([
    'jquery',
    'underscore',
    'Magento_Customer/js/customer-data',
    'awSarp2RegularPrice',
    'awSarp2SubscriptionOptionList'
], function ($, _, customerData) {
    'use strict';

    var addToCartFormSelector = '#product_addtocart_form',
        subscriptionOptionsListSelector = '[data-role=aw-sarp2-subscription-type]',
        productIdSelector = '[name=product]',
        cartData = customerData.get('cart');

    cartData.subscribe(function (updateCartData) {
        var form = $(addToCartFormSelector),
            productId = form.find(productIdSelector).val(),
            subscriptionOptions = form.find(subscriptionOptionsListSelector),
            itemData, planInput, dropdownSwitcher;

        if (productId && _.has(updateCartData, 'items')) {
            itemData = _.find(updateCartData['items'], function (itemCandidate) {
                return _.has(itemCandidate, 'product_id')
                    && itemCandidate['product_id'] == productId;
            });
            if (!_.isUndefined(itemData) && _.has(itemData, 'aw_sarp_subscription_type')) {
                planInput = subscriptionOptions.find('[value=' + itemData['aw_sarp_subscription_type'] + ']');
                if (planInput.is('input')) {
                    planInput.attr('checked', 'checked');
                    planInput.trigger('change');
                } else if (planInput.is('option')) {
                    planInput
                        .attr('selected', 'selected')
                        .attr('data-selected', '1');
                    planInput.parent().trigger('change');
                    dropdownSwitcher = subscriptionOptions.find('.aw-sarp2-subscription__dropdown-switcher');
                    if (dropdownSwitcher.awSarp2SubscriptionDropdownShowHide) {
                        subscriptionOptions.find('#aw-sarp2-dropdown-show-hide-1').trigger('click');
                    } else {
                        dropdownSwitcher
                            .on('awSarp2SubscriptionDropdownShowHide.initialized', function () {
                                subscriptionOptions.find('#aw-sarp2-dropdown-show-hide-1').trigger('click');
                            });
                    }
                }
            }
        }
    });
});
