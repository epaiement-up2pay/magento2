/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/LICENSE
 */

define(
    [
        "uiComponent",
        "Magento_Checkout/js/model/payment/renderer-list"
    ],
    function (
        Component,
        rendererList
    ) {
        "use strict";
        rendererList.push(
            {
                type: "CreditAgricole_PaymentGateway_paypal",
                component: "CreditAgricole_PaymentGateway/js/view/payment/method-renderer/default"
            },
            {
                type: "CreditAgricole_PaymentGateway_creditcard",
                component: "CreditAgricole_PaymentGateway/js/view/payment/method-renderer/creditcard"
            },
            {
                type: "CreditAgricole_PaymentGateway_sepadirectdebit",
                component: "CreditAgricole_PaymentGateway/js/view/payment/method-renderer/sepa"
            },
            {
                type: "CreditAgricole_PaymentGateway_sofortbanking",
                component: "CreditAgricole_PaymentGateway/js/view/payment/method-renderer/default"
            },
            {
                type: "CreditAgricole_PaymentGateway_ideal",
                component: "CreditAgricole_PaymentGateway/js/view/payment/method-renderer/ideal"
            },
            {
                type: "CreditAgricole_PaymentGateway_giropay",
                component: "CreditAgricole_PaymentGateway/js/view/payment/method-renderer/giropay"
            },
            {
                type: "CreditAgricole_PaymentGateway_ratepayinvoice",
                component: "CreditAgricole_PaymentGateway/js/view/payment/method-renderer/ratepay"
            },
            {
                type: "CreditAgricole_PaymentGateway_ratepayinstall",
                component: "CreditAgricole_PaymentGateway/js/view/payment/method-renderer/ratepay"
            },
            {
                type: "CreditAgricole_PaymentGateway_alipayxborder",
                component: "CreditAgricole_PaymentGateway/js/view/payment/method-renderer/default"
            },
            {
                type: "CreditAgricole_PaymentGateway_poipia",
                component: "CreditAgricole_PaymentGateway/js/view/payment/method-renderer/default"
            },
            {
                type: "CreditAgricole_PaymentGateway_paybybankapp",
                component: "CreditAgricole_PaymentGateway/js/view/payment/method-renderer/default"
            }
        );

        return Component.extend({});
    }
);
