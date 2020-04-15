/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/LICENSE
 */
define([
        "Magento_Vault/js/view/payment/vault-enabler",
    ],
    function (VaultEnabler) {
        return VaultEnabler.extend({
            defaults: {
                isActivePaymentTokenEnabler: false
            }
        });
    }
);
