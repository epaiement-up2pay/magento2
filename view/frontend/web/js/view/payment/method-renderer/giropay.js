/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/LICENSE
 */

define(
    [
        "jquery",
        "CreditAgricole_PaymentGateway/js/view/payment/method-renderer/bicdefault",
        "mage/translate",
        "mage/url"
    ],
    function ($, Component, $t, url) {
        "use strict";
        return Component.extend({
            bankBic: "",
            defaults: {
                template: "CreditAgricole_PaymentGateway/payment/method-giropay",
                redirectAfterPlaceOrder: false
            }
        });
    }
);
