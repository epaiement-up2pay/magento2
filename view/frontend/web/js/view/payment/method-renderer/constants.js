/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/LICENSE
 */

define(
    [],
    function () {
        return {
            seamlessResponse: null,

            screenWidth: {
                medium: 768,
                small: 460
            },
            iFrameHeight: {
                large: "415px",
                medium: "341px",
                small: "267px"
            },
            settings: {
                formIdSuffix: "_seamless_form",
                formIdTokenSuffix: "_seamless_token_form",
                maxErrorRepeatCount:3,
                reloadTimeout: 3000
            },
            wpp: {
                errorPrefix: "error_",
                clientValidationErrorCodes: ["FE0001"]
            },
            localStorage: {
                initValue: "0",
                counterKey: "errorCounter"
            },
            button: {
                submitOrder: "CreditAgricole_PaymentGateway_creditcard_submit",
                submitOrderVaulted: "CreditAgricole_PaymentGateway_cc_vault_submit"
            },
            error: {
                creditCardFormLoading: "credit_card_form_loading_error",
                creditCardFormSubmitting: "credit_card_form_submitting_error"
            },
            routes: {
                callbackController: "CreditAgricole_PaymentGateway/frontend/callback",
                creditCardController: "CreditAgricole_PaymentGateway/frontend/creditcard",
                redirectController: "CreditAgricole_PaymentGateway/frontend/redirect",
                vaultController: "CreditAgricole_PaymentGateway/frontend/vault?hash="
            },
            spinner: {
                start: "processStart",
                stop: "processStop"
            },
            key: {
                formUrl: "form-url",
                formMethod: "form-method",
                formFields: "form-fields",
                acsUrl: "acs_url",
                redirectUrl: "redirect-url"
            },
            dataType: {
                json: "json",
                undefined: "undefined"
            },
            method: {
                get: "GET",
                post: "POST"
            },
            successStatus: {
                ok: "OK"
            },
            data: {
                creditCard: "creditcard",
                wppTxType: "CreditAgricole_PaymentGateway_creditcard"
            },
            tag: {
                body: "body"
            },
            input: {
                type: {
                    hidden: "hidden"
                }
            },
            id: {
                creditCardRadioButton: "CreditAgricole_PaymentGateway_creditcard",
                sameShippingAndBillingAddress: "billing-address-same-as-shipping-CreditAgricole_PaymentGateway_creditcard"
            }
        };
    }
);
