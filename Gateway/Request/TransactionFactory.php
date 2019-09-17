<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/magento2-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/magento2-ee/blob/master/LICENSE
 */

namespace CreditAgricole\PaymentGateway\Gateway\Request;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction\Repository;
use Magento\Store\Model\StoreManagerInterface;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\CustomField;
use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Exception\MandatoryFieldMissingException;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

/**
 * Class TransactionFactory
 * @package CreditAgricole\PaymentGateway\Gateway\Request
 */
class TransactionFactory
{
    const PAYMENT = 'payment';
    const AMOUNT = 'amount';
    const REFUND_OPERATION = Operation::CREDIT;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ResolverInterface
     */
    protected $resolver;

    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * @var string
     */
    protected $orderId;

    /**
     * @var string
     */
    protected $transactionId;

    /**
     * @var ConfigInterface
     */
    protected $methodConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var AccountHolderFactory
     */
    protected $accountHolderFactory;

    /**
     * @var BasketFactory
     */
    protected $basketFactory;

    /**
     * @var Repository
     */
    protected $transactionRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * TransactionFactory constructor.
     * @param UrlInterface $urlBuilder
     * @param ResolverInterface $resolver
     * @param Transaction $transaction
     * @param ConfigInterface $methodConfig
     * @param StoreManagerInterface $storeManager
     * @param AccountHolderFactory $accountHolderFactory
     * @param BasketFactory $basketFactory
     * @param Repository $transactionRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ResolverInterface $resolver,
        Transaction $transaction,
        ConfigInterface $methodConfig,
        StoreManagerInterface $storeManager,
        AccountHolderFactory $accountHolderFactory,
        BasketFactory $basketFactory,
        Repository $transactionRepository = null,
        SearchCriteriaBuilder $searchCriteriaBuilder = null,
        FilterBuilder $filterBuilder = null
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->resolver = $resolver;
        $this->transaction = $transaction;
        $this->methodConfig = $methodConfig;
        $this->storeManager = $storeManager;
        $this->transactionRepository = $transactionRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->accountHolderFactory = $accountHolderFactory;
        $this->basketFactory = $basketFactory;
    }

    /**
     * @param array $commandSubject
     * @return Transaction
     * @throws \InvalidArgumentException
     * @throws MandatoryFieldMissingException
     *
     * @since 2.0.1 set order-number
     */
    public function create($commandSubject)
    {
        if (!isset($commandSubject[self::PAYMENT])
            || !$commandSubject[self::PAYMENT] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided.');
        }

        /** @var PaymentDataObjectInterface $payment */
        $payment = $commandSubject[self::PAYMENT];

        /** @var OrderAdapterInterface $order */
        $order = $payment->getOrder();

        $amount = new Amount((float)$order->getGrandTotalAmount(), $order->getCurrencyCode());
        $this->transaction->setAmount($amount);

        $this->orderId = $order->getOrderIncrementId();
        $this->addOrderIdToTransaction($this->orderId);

        $this->transaction->setEntryMode('ecommerce');
        $this->transaction->setLocale(substr($this->resolver->getLocale(), 0, 2));

        $cfgkey = $this->transaction->getConfigKey();

        // Special handling for the non-standard mapping
        if ($this->transaction instanceof RatepayInvoiceTransaction) {
            $cfgkey = RatepayInvoiceTransaction::NAME;
        }

        $wdBaseUrl = $this->urlBuilder->getRouteUrl('CreditAgricole_PaymentGateway');
        $methodAppend = '?method=' . urlencode($cfgkey);

        $this->transaction->setRedirect(new Redirect(
            $wdBaseUrl . 'frontend/redirect' . $methodAppend,
            $wdBaseUrl . 'frontend/cancel' . $methodAppend,
            $wdBaseUrl . 'frontend/redirect' . $methodAppend
        ));
        $this->transaction->setNotificationUrl($wdBaseUrl . 'frontend/notify?orderId=' . $this->orderId);

        if ($this->methodConfig->getValue('send_additional')) {
            $this->setAdditionalInformation($order);
        }

        return $this->transaction;
    }

    /**
     * @param $commandSubject
     * @return Transaction
     */
    public function capture($commandSubject)
    {
        if (!isset($commandSubject[self::PAYMENT])
            || !$commandSubject[self::PAYMENT] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided.');
        }

        /** @var PaymentDataObjectInterface $paymentDo */
        $paymentDo = $commandSubject[self::PAYMENT];

        /** @var OrderAdapterInterface $order */
        $order = $paymentDo->getOrder();

        /** @var Payment $payment */
        $payment = $paymentDo->getPayment();

        $this->orderId = $order->getId();
        $captureAmount = $commandSubject[self::AMOUNT];
        $amount = new Amount((float)$captureAmount, $order->getCurrencyCode());
        $this->transaction->setParentTransactionId($payment->getParentTransactionId());
        $this->transaction->setAmount($amount);

        $this->transaction->setEntryMode('ecommerce');
        $this->transaction->setLocale(substr($this->resolver->getLocale(), 0, 2));

        $wdBaseUrl = $this->urlBuilder->getRouteUrl('CreditAgricole_PaymentGateway');
        $this->transaction->setNotificationUrl($wdBaseUrl . 'frontend/notify');

        return $this->transaction;
    }

    /**
     * @param $commandSubject
     * @return Transaction
     */
    public function refund($commandSubject)
    {
        if (!isset($commandSubject[self::PAYMENT])
            || !$commandSubject[self::PAYMENT] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided.');
        }

        /** @var PaymentDataObjectInterface $paymentDo */
        $paymentDo = $commandSubject[self::PAYMENT];

        /** @var OrderAdapterInterface $order */
        $order = $paymentDo->getOrder();

        /** @var Payment $payment */
        $payment = $paymentDo->getPayment();

        $this->orderId = $order->getId();
        $this->transactionId = $payment->getParentTransactionId();
        $this->transaction->setEntryMode('ecommerce');
        $this->transaction->setLocale(substr($this->resolver->getLocale(), 0, 2));
        $this->transaction->setAmount(new Amount((float)$commandSubject[self::AMOUNT], $order->getCurrencyCode()));

        return $this->transaction;
    }

    public function void($commandSubject)
    {
        if (!isset($commandSubject[self::PAYMENT])
            || !$commandSubject[self::PAYMENT] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided.');
        }

        /** @var PaymentDataObjectInterface $paymentDo */
        $paymentDo = $commandSubject[self::PAYMENT];

        /** @var OrderAdapterInterface $order */
        $order = $paymentDo->getOrder();

        /** @var Payment $payment */
        $payment = $paymentDo->getPayment();

        $this->orderId = $order->getId();
        $this->transactionId = $payment->getParentTransactionId();
        $this->transaction->setEntryMode('ecommerce');
        $this->transaction->setLocale(substr($this->resolver->getLocale(), 0, 2));
        $this->transaction->setAmount(new Amount((float)$order->getGrandTotalAmount(), $order->getCurrencyCode()));
        $this->transaction->setParentTransactionId($this->transactionId);

        return $this->transaction;
    }

    /**
     * @return string
     */
    public function getRefundOperation()
    {
        return self::REFUND_OPERATION;
    }

    public function setAdditionalInformation($order)
    {
        $this->transaction->setDescriptor(sprintf(
            '%s %s',
            substr($this->storeManager->getStore()->getName(), 0, 9),
            $this->orderId
        ));
        $billingAddress = $order->getBillingAddress();
        $this->transaction->setAccountHolder($this->accountHolderFactory->create($billingAddress));
        if (null != $order->getShippingAddress()) {
            $this->transaction->setShipping($this->accountHolderFactory->create($order->getShippingAddress()));
        }
        $this->transaction->setBasket($this->basketFactory->create($order, $this->transaction));
        $this->transaction->setIpAddress($order->getRemoteIp());
        $this->transaction->setConsumerId($order->getCustomerId());

        return $this->transaction;
    }

    /**
     * Gets all existing transactions for the specified orders
     *
     * @param $order
     * @param $payment
     * @return array
     */
    protected function getTransactionsForOrder($order, $payment)
    {
        if ($this->transactionRepository === null) {
            return [];
        }

        $filters[] = $this->filterBuilder->setField('payment_id')
            ->setValue($payment->getId())
            ->create();

        $filters[] = $this->filterBuilder->setField('order_id')
            ->setValue($order->getId())
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder->addFilters($filters)
            ->create();

        return $this->transactionRepository->getList($searchCriteria)->toArray();
    }

    /**
     * Add mandatory order-number to transaction and custom field orderId for backwards compatibility
     *
     * @param $orderId
     * @since 2.0.1
     */
    protected function addOrderIdToTransaction($orderId)
    {
        $customFields = new CustomFieldCollection();
        $customFields->add(new CustomField('orderId', $orderId));
        $this->transaction->setCustomFields($customFields);
        $this->transaction->setOrderNumber($orderId);
    }
}
