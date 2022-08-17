define([
    'jquery',
    'underscore',
], function ($, _) {
    'use strict';

    /**
     * Recalculate price relative to displayed prices in priceBox widget
     *
     * @param newPrices
     * @param priceBox
     * @return {{}}
     */
    return function (newPrices, priceBoxElement) {
        var displayPrices = priceBoxElement.priceBox('option').prices,
            priceCodes,
            result = {};

        priceCodes = _.keys(newPrices);
        _.each(priceCodes, function (priceCode) {
            if (_.has(displayPrices, priceCode)) {
                result[priceCode] = _.clone(newPrices[priceCode]);
                result[priceCode].amount = newPrices[priceCode].amount - displayPrices[priceCode].amount;
            }
        });

        return result;
    }
});
