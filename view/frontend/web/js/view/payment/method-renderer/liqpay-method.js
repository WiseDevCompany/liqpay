/**
 * Copyright © 2016 Wise Ltd. All rights reserved.
 * Released under the Open Software License (OSL 3.0)
 * Please visit http://opensource.org/licenses/osl-3.0.php for the full text of the OSL 3.0 license
 */
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default'
    ],
    function (
        $,
        Component
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Wise_Liqpay/payment/liqpay',
                redirectAfterPlaceOrder: false
            },
            afterPlaceOrder: function () {
                
                var L = window.location,
                    URL = L.origin;
                
                if (!URL) {
                    URL = L.protocol + '//' + L.hostname + (L.port ? ':' + L.port : '');
                }
                
                $.ajax(URL + '/liqpay/api/redirecturl', {
                    method: 'POST',
                    cache: false,
                    success: function (data) {
                        window.location.replace(data ? data : window.checkoutConfig.defaultSuccessPageUrl)
                    }
                });
                
            }
        });
    }
);
