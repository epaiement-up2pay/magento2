<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/LICENSE
 */

namespace CreditAgricole\PaymentGateway\Gateway\Service;

use Magento\Payment\Gateway\ConfigFactoryInterface;
use Psr\Log\LoggerInterface;
use Wirecard\PaymentSdk\TransactionService;

/**
 * Class used for creating transaction service
 */
class TransactionServiceFactory
{
    /**
     * @var ConfigFactoryInterface
     */
    private $paymentSdkConfigFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * TransactionServiceFactory constructor.
     * @param LoggerInterface $logger
     * @param ConfigFactoryInterface $paymentSdkConfigFactory
     */
    public function __construct(LoggerInterface $logger, ConfigFactoryInterface $paymentSdkConfigFactory)
    {
        $this->logger = $logger;
        $this->paymentSdkConfigFactory = $paymentSdkConfigFactory;
    }

    /**
     * @param string $paymentName
     * @return TransactionService
     */
    public function create($paymentName = null)
    {
        $txConfig = $this->paymentSdkConfigFactory->create($paymentName);
        return new TransactionService($txConfig, $this->logger);
    }
}
