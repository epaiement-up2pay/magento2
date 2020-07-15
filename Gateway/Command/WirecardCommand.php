<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/LICENSE
 */

namespace CreditAgricole\PaymentGateway\Gateway\Command;

use Magento\Framework\DataObject;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use CreditAgricole\PaymentGateway\Gateway\Request\TransactionFactory;
use CreditAgricole\PaymentGateway\Gateway\Service\TransactionServiceFactory;
use CreditAgricole\PaymentGateway\Model\Adminhtml\Source\PaymentAction;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\Reservable;

/**
 * Class used for executing command on business object
 */
class WirecardCommand implements CommandInterface
{
    const STATEOBJECT = 'stateObject';

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var TransactionServiceFactory
     */
    private $transactionServiceFactory;

    /**
     * @var HandlerInterface
     */
    private $handler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ConfigInterface
     */
    private $methodConfig;

    /**
     * WirecardCommand constructor.
     * @param TransactionFactory $transactionFactory
     * @param TransactionServiceFactory $transactionServiceFactory
     * @param LoggerInterface $logger
     * @param HandlerInterface $handler
     * @param ConfigInterface $methodConfig
     */
    public function __construct(
        TransactionFactory $transactionFactory,
        TransactionServiceFactory $transactionServiceFactory,
        LoggerInterface $logger,
        HandlerInterface $handler,
        ConfigInterface $methodConfig
    ) {
        $this->transactionFactory = $transactionFactory;
        $this->transactionServiceFactory = $transactionServiceFactory;
        $this->logger = $logger;
        $this->handler = $handler;
        $this->methodConfig = $methodConfig;
    }

    /**
     * @param array $commandSubject
     * @return void
     * @throws \InvalidArgumentException
     */
    public function execute(array $commandSubject)
    {
        $transaction = $this->transactionFactory->create($commandSubject);
        if ($transaction instanceof CreditCardTransaction) {
            return;
        }
        $transactionService = $this->transactionServiceFactory->create($transaction::NAME);

        if (!isset($commandSubject[self::STATEOBJECT])
            || !($commandSubject[self::STATEOBJECT] instanceof DataObject)) {
            throw new \InvalidArgumentException('State object should be provided.');
        }
        /** @var $stateObject DataObject */
        $stateObject = $commandSubject[self::STATEOBJECT];
        $stateObject->setData('state', Order::STATE_PENDING_PAYMENT);

        $operation = Operation::PAY;
        if ($transaction instanceof Reservable
            && $this->methodConfig->getValue('payment_action') === PaymentAction::AUTHORIZE
        ) {
            $operation = Operation::RESERVE;
        }

        try {
            $response = $transactionService->process($transaction, $operation);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            $response = null;
        }

        if ($this->handler) {
            $this->handler->handle($commandSubject, ['paymentSDK-php' => $response]);
        }

        if ($response instanceof FailureResponse) {
            $errors = "";
            foreach ($response->getStatusCollection()->getIterator() as $item) {
                /** @var Status $item */
                $errors .= $item->getDescription() . "<br>\n";
            }
            throw new InvalidArgumentException($errors);
        }
    }
}
