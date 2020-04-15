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
        "Magento_Checkout/js/model/payment/additional-validators",
        "mage/url",
        "Magento_Checkout/js/model/quote",
        "Magento_Ui/js/modal/modal",
        "mage/translate",
        "ko"
    ],
    function ($, Component, additionalValidators, url, quote, modal, ko) {
        "use strict";
        return Component.extend({
            accountFirstName: "",
            accountLastName: "",
            bankBic: "",
            bankAccountIban: "",
            mandateId: "",
            mandate: false,
            defaults: {
                template: "CreditAgricole_PaymentGateway/payment/method-sepa",
                redirectAfterPlaceOrder: false
            },
            /**
             * Get payment method data
             */
            getData: function () {
                return {
                    "method": this.getCode(),
                    "po_number": null,
                    "additional_data": {
                        "accountFirstName": this.accountFirstName,
                        "accountLastName": this.accountLastName,
                        "bankBic": this.bankBic,
                        "bankAccountIban": this.bankAccountIban,
                        "mandateId": this.mandateId
                    }
                };
            },
            hasBankBic: function() {
                if(parseInt(this.config.enable_bic)) {
                    return true;
                }
                return false;
            },
            validate: function () {
                var frm = $("#" + this.getCode() + "-form");
                return frm.validation() && frm.validation("isValid");
            },
            beforePlaceOrder: function (data, event) {
                var self = this;
                if (this.validate()) {
                    var sepaMandate = $("#sepaMandate");

                    sepaMandate.modal({
                        title: $.mage.__("sepa_mandate"),
                        responsive: true,
                        innerScroll: true,
                        buttons: [{
                            text: "Accept",
                            click: function() {
                                self.mandateId = $("input[name=mandateId]", sepaMandate).val();
                                this.closeModal();
                                self.placeOrder();
                            }
                        },
                            {
                                text: "Close",
                                click: this.closeModal
                            }],
                        opened: function(){
                                var acceptButton = $("footer button:first", sepaMandate.closest(".modal-inner-wrap"));
                            acceptButton.addClass("disabled");
                            var modal = this;
                            $.get(url.build("CreditAgricole_PaymentGateway/frontend/sepamandate", {})).done(
                                function (response) {
                                    response = response.replace(/%firstname%/g, $("#CreditAgricole_PaymentGateway_sepadirectdebit_accountFirstName").val())
                                        .replace(/%lastname%/g, $("#CreditAgricole_PaymentGateway_sepadirectdebit_accountLastName").val())
                                        .replace(/%bankAccountIban%/g, $("#CreditAgricole_PaymentGateway_sepadirectdebit_bankAccountIban").val());

                                    if(self.hasBankBic()) {
                                    response = response.replace(/%bankBic%/g, $("#CreditAgricole_PaymentGateway_sepadirectdebit_bankBic").val());
                                    } else {
                                        response = response.replace(/%bankBic%/g, "");
                                    }
                                    $(modal).html(response);
                                    $("#sepa-accept", modal).on("change", function(event) {
                                        if ($("#sepa-accept", modal).prop("checked")) {
                                            if (acceptButton.hasClass("disabled")) {
                                                acceptButton.removeClass("disabled");
                                            }
                                        } else {
                                            acceptButton.addClass("disabled");
                                        }

                                    });
                                }
                            );
                        }
                    }).modal("openModal");
                }
            }
        });
    }
);
