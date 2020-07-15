<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/LICENSE
 */

namespace CreditAgricole\PaymentGateway\Gateway\Validator;

use CreditAgricole\PaymentGateway\Gateway\Validator;

/**
 * Class used for creating validator
 *
 * @since 2.2.1
 */
class ValidatorFactory
{
    /**
     * Create validator with specific type within validator namespace
     *
     * @param string $type
     * @param mixed $object
     * @return AbstractValidator
     * @throws \InvalidArgumentException
     * @since 2.2.1
     */
    public function create($type, $object)
    {
        $class = __NAMESPACE__ . Validator::NAMESPACE_SEPARATOR . $type . Validator::VALIDATOR;
        if (class_exists($class)) {
            return new $class($object);
        }
        throw new \InvalidArgumentException('Invalid validator given');
    }
}
