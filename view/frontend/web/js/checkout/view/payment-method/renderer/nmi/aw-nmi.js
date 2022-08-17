define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    rendererList.push(
        {
            type: 'aw_nmi',
            component: 'Aheadworks_Sarp2/js/checkout/view/payment-method/renderer/nmi/hosted-fields'
        }
    );

    return Component.extend({});
});
