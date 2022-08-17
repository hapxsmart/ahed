var config = {
    map: {
        '*': {
            awSarp2AsLowAsPrice:                  'Aheadworks_Sarp2/js/product/as-low-as-price',
            awSarp2RegularPrice:                  'Aheadworks_Sarp2/js/product/regular-price',
            awSarp2SubscriptionOptionList:        'Aheadworks_Sarp2/js/product/subscription-option-list',
            awSarp2SubscriptionDetails:           'Aheadworks_Sarp2/js/product/subscription-details',
            awSarp2SubscriptionDropdownShowHide:  'Aheadworks_Sarp2/js/product/subscription-dropdown-show-hide',
            awSarp2SubscriptionOptionStorage:     'Aheadworks_Sarp2/js/product/storage',
            awSarp2ElementVisibility:             'Aheadworks_Sarp2/js/element-visibility',
            awSarp2Calendar:                      'Aheadworks_Sarp2/js/widget/profile/calendar',
            awSarp2ProfileProductItemEdit:        'Aheadworks_Sarp2/js/customer/subscriptions/edit/product-item',
            awSarp2UpdateSubscriptionButton:      'Aheadworks_Sarp2/js/customer/subscriptions/edit/update-subscription-button',

            // priceBox does not work in Firefox, Safari and other non-chrome browsers
            priceBox:                           'Aheadworks_Sarp2/js/product/price-box-mixin',
        }
    },
    shim: {
        'awSarp2SubscriptionOptionList': {
            deps: [
                'awSarp2RegularPrice',
                'awSarp2AsLowAsPrice'
            ]
        },
        'Aheadworks_Sarp2/js/product/custom-options-mixin': {
            deps: [
                'awSarp2SubscriptionOptionList'
            ]
        },
        'Aheadworks_Sarp2/js/product/bundle-options-mixin': {
            deps: [
                'awSarp2SubscriptionOptionList'
            ]
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/model/payment-service': {
                'Aheadworks_Sarp2/js/checkout/model/payment-service-mixin': true
            },
            'Magento_Checkout/js/model/quote': {
                'Aheadworks_Sarp2/js/checkout/model/quote-mixin': true
            },
            'Magento_Checkout/js/action/set-payment-information': {
                'Aheadworks_Sarp2/js/checkout/action/set-payment-information-mixin': true
            },
            'Magento_Checkout/js/action/set-payment-information-extended': {
                'Aheadworks_Sarp2/js/checkout/action/set-payment-information-extended-mixin': true
            },
            'Magento_Checkout/js/action/get-totals': {
                'Aheadworks_Sarp2/js/checkout/action/get-totals-mixin': true
            },
            'Aheadworks_BamboraApac/js/view/payment/method-renderer/hosted-fields': {
                'Aheadworks_Sarp2/js/checkout/view/payment-method/renderer/bambora-apac/hosted-fields-mixin': true
            },
            'Aheadworks_Nmi/js/view/payment/method-renderer/hosted-fields': {
                'Aheadworks_Sarp2/js/checkout/view/payment-method/renderer/nmi/hosted-fields-checkout-mixin': true
            },
            'Magento_AuthorizenetAcceptjs/js/view/payment/method-renderer/authorizenet-accept': {
                'Aheadworks_Sarp2/js/checkout/view/payment-method/renderer/authorizenet-accept-mixin': true
            },
            'Magento_OfflinePayments/js/view/payment/method-renderer/cashondelivery-method': {
                'Aheadworks_Sarp2/js/checkout/view/payment-method/renderer/cashondelivery-mixin': true
            },
            'Magento_ConfigurableProduct/js/configurable': {
                'Aheadworks_Sarp2/js/product/configurable-mixin': true
            },
            'Magento_Catalog/js/price-options': {
                'Aheadworks_Sarp2/js/product/custom-options-mixin': true
            },
            'Magento_Swatches/js/swatch-renderer': {
                'Aheadworks_Sarp2/js/product/swatch-renderer-mixin': true
            },
            'Magento_Bundle/js/price-bundle': {
                'Aheadworks_Sarp2/js/product/bundle-options-mixin': true
            },
            'Magento_Customer/js/model/customer-addresses': {
                'Aheadworks_Sarp2/js/customer/model/customer-addresses-mixin': true
            }
        }
    }
};
