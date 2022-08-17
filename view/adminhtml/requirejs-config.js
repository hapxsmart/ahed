var config = {
    map: {
        '*': {
            awSarp2Titles:  'Aheadworks_Sarp2/js/plan/titles',
            awSarp2Pager:   'Aheadworks_Sarp2/js/subscription/pager',
            awSarp2Tooltip: 'Aheadworks_Sarp2/js/subscription/tooltip',
            awSarp2PlanSelect: 'Aheadworks_Sarp2/js/plan/select',
        }
    },

    config: {
        mixins: {
            'Magento_Ui/js/form/adapter/buttons': {
                'Aheadworks_Sarp2/js/ui/form/adapter/buttons-mixin': true
            }
        }
    }
};
