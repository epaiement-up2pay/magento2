/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/magento2-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/magento2-ee/blob/master/LICENSE
 */

define(
    [
        "jquery",
        "CreditAgricole_PaymentGateway/js/view/payment/method-renderer/default",
        "mage/translate",
        "mage/url"
    ],
    function ($, Component, $t, url) {
        "use strict";
        return Component.extend({
            bankBic: "",
            defaults: {
                template: "CreditAgricole_PaymentGateway/payment/method-default",
                redirectAfterPlaceOrder: false
            },
            getData: function () {
                return {
                    "method": this.getCode(),
                    "po_number": null,
                    "additional_data": {
                        "bankBic": this.bankBic,
                    }
                };
            },
            validate: function () {
                var frm = $("#" + this.getCode() + "-form");
                return frm.validation() && frm.validation("isValid");
            },
            afterPlaceOrder: function () {
                $.get(url.build("CreditAgricole_PaymentGateway/frontend/callback"), function (result) {
                    window.location.replace(result.data["redirect-url"]);
                });
            }
        });
    }
);
