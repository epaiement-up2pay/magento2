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
        "CreditAgricole_PaymentGateway/js/view/payment/method-renderer/default",
        "CreditAgricole_PaymentGateway/js/validator/min-age-validator",
        "mage/translate",
    ],
    function ($, Component, minAgeValidator, $t) {
        "use strict";
        return Component.extend({
            termsChecked: false,
            customerData: {},
            customerDob: null,
            defaults: {
                template: "CreditAgricole_PaymentGateway/payment/method-ratepay",
                redirectAfterPlaceOrder: false
            },
            onTermsCheckboxClick: function () {
                $("#CreditAgricole_PaymentGateway_ratepayinvoice_submit").attr("disabled", !this.termsChecked);
                $("#CreditAgricole_PaymentGateway_ratepayinvoice_submit").toggleClass("disabled", !this.termsChecked);
                return true;
            },
            initObservable: function () {
                this._super().observe("customerDob");
                return this;
            },
            initialize: function () {
                this._super();
                this.config = window.checkoutConfig.payment[this.getCode()];
                this.customerData = window.customerData;
                this.customerDob(this.customerData.dob);
                return this;
            },
            getData: function () {
                return {
                    "method": this.getCode(),
                    "po_number": null,
                    "additional_data": {
                        "customerDob": this.customerDob()
                    }
                };
            },
            getRatepayScript: function () {
                return this.config.ratepay_script;
            },
            validate: function () {
                var errorPane = $("#" + this.getCode() + "-dob-error");
                if (!minAgeValidator.validate(this.customerDob())) {
                    errorPane.html($t("text_min_age_notice"));
                    errorPane.css("display", "block");
                    return false;
                }
                if (this.config.billing_equals_shipping
                    && $("#billing-address-same-as-shipping-CreditAgricole_PaymentGateway_ratepayinvoice").is(":checked") === false) {
                    errorPane.html($t("text_need_same_address_notice"));
                    errorPane.css("display", "block");
                    return false;
                }
                let form = $("#" + this.getCode() + "-form");
                return $(form).validation() && $(form).validation("isValid");
            }
        });
    }
);
