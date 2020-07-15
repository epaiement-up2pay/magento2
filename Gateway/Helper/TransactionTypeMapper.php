<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/LICENSE
 */

namespace CreditAgricole\PaymentGateway\Gateway\Helper;

use Magento\Sales\Api\Data\TransactionInterface as MagentoTransactionInterface;

/**
 * Helper for mapping transaction type
 *
 * @since 2.2.2
 */
class TransactionTypeMapper
{
    /**
     * @var SdkTransactionTypeCollection
     */
    private $transactionTypeCollection;

    /**
     * TransactionTypeMapper constructor.
     * @param SdkTransactionTypeCollection $transactionTypeCollection
     * @since 2.2.2
     */
    public function __construct(SdkTransactionTypeCollection $transactionTypeCollection)
    {
        $this->transactionTypeCollection = $transactionTypeCollection;
    }

    /**
     * Map TransactionTypeInterface to MagentoTransactionInterface type
     * @param string $transactionType
     * @return string
     * @since 2.2.2
     */
    public function getMappedTransactionType($transactionType)
    {
        $mappedTransactionType = $transactionType;
        if (in_array($transactionType, $this->transactionTypeCollection->getAuthorizationTransactionTypes())) {
            $mappedTransactionType = MagentoTransactionInterface::TYPE_AUTH;
        }
        if (in_array($transactionType, $this->transactionTypeCollection->getPurchaseTransactionTypes())) {
            $mappedTransactionType = MagentoTransactionInterface::TYPE_CAPTURE;
        }
        if (in_array($transactionType, $this->transactionTypeCollection->getRefundTransactionTypes())) {
            $mappedTransactionType = MagentoTransactionInterface::TYPE_REFUND;
        }
        if (in_array($transactionType, $this->transactionTypeCollection->getCancelTransactionTypes())) {
            $mappedTransactionType = MagentoTransactionInterface::TYPE_VOID;
        }
        if ($transactionType === 'check-payer-response') {
            $mappedTransactionType = MagentoTransactionInterface::TYPE_PAYMENT;
        }

        return $mappedTransactionType;
    }
}
