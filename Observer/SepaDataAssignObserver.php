<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/LICENSE
 */

namespace CreditAgricole\PaymentGateway\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

class SepaDataAssignObserver extends AbstractDataAssignObserver
{
    /**
     * @param Observer $observer
     * @return void|null
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additionalData)) {
            return;
        }

        $paymentInfo = $this->readPaymentModelArgument($observer);

        if (array_key_exists('accountFirstName', $additionalData)) {
            $paymentInfo->setAdditionalInformation(
                'accountFirstName',
                $additionalData['accountFirstName']
            );
        }
        if (array_key_exists('accountLastName', $additionalData)) {
            $paymentInfo->setAdditionalInformation(
                'accountLastName',
                $additionalData['accountLastName']
            );
        }
        if (array_key_exists('bankBic', $additionalData)) {
            $paymentInfo->setAdditionalInformation(
                'bankBic',
                $additionalData['bankBic']
            );
        }
        if (array_key_exists('bankAccountIban', $additionalData)) {
            $paymentInfo->setAdditionalInformation(
                'bankAccountIban',
                $additionalData['bankAccountIban']
            );
        }
        if (array_key_exists('mandateId', $additionalData)) {
            $paymentInfo->setAdditionalInformation(
                'mandateId',
                $additionalData['mandateId']
            );
        }
    }
}
