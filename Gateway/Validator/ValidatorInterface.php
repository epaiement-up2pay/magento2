<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/LICENSE
 */

namespace CreditAgricole\PaymentGateway\Gateway\Validator;

/**
 * Interface for validation of parameters
 *
 * @since 2.2.1
 */
interface ValidatorInterface
{
    /**
     * Validation for business related object
     *
     * @param array $validationParams
     * @return bool
     * @since 2.2.1
     */
    public function validate(array $validationParams);
}
