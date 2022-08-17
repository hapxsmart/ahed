define([
    'jquery',
    'ko',
    'mage/utils/wrapper',
    'Magento_Customer/js/model/customer/address'
], function ($, ko, wrapper, Address) {
    'use strict';

    var isLoggedIn = ko.observable(window.isCustomerLoggedIn);

    return function (target) {

        target.getAddressItems = wrapper.wrapSuper(target.getAddressItems, function () {
            var items = [],
                customerData = window.customerData,
                isHashUsed = checkoutConfig !== undefined
                    ? checkoutConfig.isHashUsed
                    : false;

            if (isLoggedIn() || isHashUsed) {
                if (Object.keys(customerData).length) {
                    $.each(customerData.addresses, function (key, item) {
                        items.push(new Address(item));
                    });
                }
            }

            return items;
        });

        return target;
    };
});