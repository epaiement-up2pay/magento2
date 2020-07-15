<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/LICENSE
 */

namespace CreditAgricole\PaymentGateway\Gateway\Validator;

use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Quote\Model\Quote\Address as QuoteAddress;

/**
 * Class used for creating address validator
 *
 * @since 3.0.0
 */
class AddressValidatorFactory
{
    /**
     * Create validators based on Magento Address Objects
     *
     * @param AddressAdapterInterface|QuoteAddress $magentoAddress
     * @return AddressAdapterInterfaceValidator|QuoteAddressValidator
     * @throws \InvalidArgumentException
     * @since 3.0.0
     */
    public function create($magentoAddress)
    {
        switch (true) {
            case $magentoAddress instanceof AddressAdapterInterface:
                return new AddressAdapterInterfaceValidator($magentoAddress);
            case $magentoAddress instanceof QuoteAddress:
                return new QuoteAddressValidator($magentoAddress);
        }
        throw new \InvalidArgumentException('Address data object should be provided.');
    }
}
