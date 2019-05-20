define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (Component,
              rendererList) {
        'use strict';
        rendererList.push(
            {
                type: 'ccavenue',
                component: 'AWstreams_Ccavenue/js/view/payment/method-renderer/ccavenue-method'
            }
        );
        return Component.extend({});
    }
);