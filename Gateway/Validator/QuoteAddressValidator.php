<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/LICENSE
 */

namespace CreditAgricole\PaymentGateway\Gateway\Validator;

use Magento\Quote\Model\Quote\Address;

/**
 * Validator for business related object
 *
 * @since 2.2.1
 */
class QuoteAddressValidator extends AbstractValidator
{
    /**
     * @var Address
     */
    private $address;

    /**
     * QuoteAddressValidator constructor.
     * @param Address $address
     * @since 2.2.1
     */
    public function __construct(Address $address)
    {
        $this->address = $address;
    }

    /**
     * Validation for business related object
     *
     * @param array $validationParams
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.1
     */
    public function validate(array $validationParams = [])
    {
        $isValid = true;
        if (empty($this->address->getCountryId())
            || empty($this->address->getCity())
            || empty($this->address->getStreetLine(1))
        ) {
            $isValid = false;
        }

        return $isValid;
    }
}
