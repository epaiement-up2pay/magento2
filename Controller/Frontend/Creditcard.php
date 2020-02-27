<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/magento2-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/magento2-ee/blob/master/LICENSE
 */

namespace CreditAgricole\PaymentGateway\Controller\Frontend;

use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Helper\Data;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Psr\Log\LoggerInterface;
use Wirecard\Converter\WppVTwoConverter;
use CreditAgricole\PaymentGateway\Gateway\Helper\CalculationTrait;
use CreditAgricole\PaymentGateway\Gateway\Helper\OrderDto;
use CreditAgricole\PaymentGateway\Gateway\Helper\ThreeDsHelper;
use CreditAgricole\PaymentGateway\Gateway\Service\TransactionServiceFactory;
use CreditAgricole\PaymentGateway\Model\Adminhtml\Source\PaymentAction;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\CustomField;
use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Entity\Item;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
use Wirecard\PaymentSdk\Transaction\Transaction;

class Creditcard extends Action
{
    use CalculationTrait;

    /*' @var string FORM parameter name to send the transaction type in AJAX */
    const FRONTEND_DATAKEY_TXTYPE = 'txtype';

    /** @var string key CREDITCARD as sent by frontend */
    const FRONTEND_CODE_CREDITCARD = 'CreditAgricole_PaymentGateway_creditcard';

    /** @var JsonFactory */
    protected $resultJsonFactory;

    /** @var CartRepositoryInterface */
    protected $cartRepository;

    /** @var Session */
    protected $checkoutSession;

    /** @var TransactionServiceFactory */
    protected $transactionServiceFactory;

    /** @var ResolverInterface */
    protected $resolver;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var UrlInterface */
    protected $urlBuilder;

    /** @var Data */
    protected $paymentHelper;

    /** @var ConfigInterface */
    protected $methodConfig;

    /** @var LoggerInterface */
    protected $logger;

    /** @var ThreeDsHelper */
    protected $threeDsHelper;

    /** @var CartRepositoryInterface */
    protected $quoteRepository;

    /**
     * @var PaymentTokenManagementInterface
     */
    private $paymentTokenManagement;
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * Creditcard constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param TransactionServiceFactory $transactionServiceFactory
     * @param CartRepositoryInterface $quoteRepository
     * @param Session $checkoutSession
     * @param ResolverInterface $resolver
     * @param StoreManagerInterface $storeManager
     * @param Data $paymentHelper
     * @param ConfigInterface $methodConfig
     * @param LoggerInterface $logger
     * @param PaymentTokenManagementInterface $paymentTokenManagement
     * @param CustomerSession $customerSession
     * @param ThreeDsHelper $threeDsHelper
     *
     * @since 2.1.0 added ThreeDsHelper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        TransactionServiceFactory $transactionServiceFactory,
        CartRepositoryInterface $quoteRepository,
        Session $checkoutSession,
        ResolverInterface $resolver,
        StoreManagerInterface $storeManager,
        Data $paymentHelper,
        ConfigInterface $methodConfig,
        LoggerInterface $logger,
        PaymentTokenManagementInterface $paymentTokenManagement,
        CustomerSession $customerSession,
        ThreeDsHelper $threeDsHelper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->transactionServiceFactory = $transactionServiceFactory;
        $this->quoteRepository = $quoteRepository;
        $this->checkoutSession = $checkoutSession;
        $this->resolver = $resolver;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $context->getUrl();
        $this->paymentHelper = $paymentHelper;
        $this->methodConfig = $methodConfig;
        $this->logger = $logger;
        $this->threeDsHelper = $threeDsHelper;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Execute the command to build the CreditCard UI init data
     *
     * Based on the session data about user data and cart information (items, sum)
     * the request data are build to init the CreditCard UI later in JavaScript.
     *
     * The result is JSON with following structure:
     *
     * - success case: status=OK, uiData=(raw JSON data)
     * - error case:   status=ERR, errMsg=error message, optionally details
     *
     * @return Json
     * @throws LocalizedException
     * @throws \Exception
     */
    public function execute()
    {
        $quote = $this->checkoutSession->getQuote();
        if (is_null($quote)) {
            return $this->buildErrorResponse('no quote found');
        }

        $transactionType = $this->getRequest()->getParam(self::FRONTEND_DATAKEY_TXTYPE);

        if (!$this->isCreditCardTransactionType($transactionType)) {
            return $this->buildErrorResponse('Unknown transaction type');
        }

        $orderDto = new OrderDto();
        $orderDto->quote = $quote->reserveOrderId();
        $this->quoteRepository->save($quote);

        $transactionService = $this->transactionServiceFactory->create(CreditCardTransaction::NAME);
        $orderDto->orderId = $quote->getReservedOrderId();

        $orderDto->config = $transactionService->getConfig()->get(CreditCardTransaction::NAME);
        $orderDto->transaction = new CreditCardTransaction();
        $this->addCreditCardFields($orderDto);
        $this->addCreditCardThreeDsFields($orderDto);
        $this->addCreditCardToken($orderDto);

        try {
            $data = $transactionService->getCreditCardUiWithData(
                $orderDto->transaction,
                $this->getTransactionTypeForPaymentAction(),
                $this->getSupportedWppLangCode()
            );
            if (empty($data)) {
                throw new \Exception("Cannot create UI");
            }
            return $this->buildSuccessResponse($data);
        } catch (\Exception $e) {
            return $this->buildErrorResponse('cannot create UI', ['exception' => get_class($e)]);
        }
    }

    /**
     * @return string
     * @throws LocalizedException
     * @since 3.0.0
     */
    private function getTransactionTypeForPaymentAction()
    {
        $method = $this->paymentHelper->getMethodInstance(self::FRONTEND_CODE_CREDITCARD);
        $paymentAction = $method->getConfigData('payment_action');
        if ($paymentAction === PaymentAction::AUTHORIZE) {
            return Transaction::TYPE_AUTHORIZATION;
        }
        return Transaction::TYPE_PURCHASE;
    }

    /**
     * Generate the SUCCESS JSON result
     *
     * The resulting JSON contains two keys:
     * - 'status': 'OK' to signalize the JavaScript caller handle answer as init data
     * - 'uiData': the UI init data used by JavaScript code to render the UI
     *
     * @param \stdClass $uiData the JSON payload received from backend
     * @return Json JsonResponse with 'status' and 'uiData'
     */
    private function buildSuccessResponse($uiData)
    {
        $jsonResponse = $this->resultJsonFactory->create();
        $jsonResponse->setData([
            'status' => 'OK',
            'uiData' => $uiData,
        ]);
        return $jsonResponse;
    }

    /**
     * Generate the ERROR JSON result
     *
     * The resulting JSON contains two or three keys:
     * - 'status': 'ERR' to signalize the JavaScript caller handle answer as error
     * - 'errMsg': an english human readable message what's happen
     * - 'details': mapping with additional information (optionally)
     *
     * @param string $errMsg error message for caller
     * @param array $details map with addional information about the problem
     * @return Json JsonResponse with 'status' and 'errMsg'. Can also contains key 'details'
     */
    private function buildErrorResponse($errMsg, $details = [])
    {
        $jsonResponse = $this->resultJsonFactory->create();
        $errData = [
            'status' => 'ERR',
            'errMsg' => $errMsg,
        ];
        if (!empty($details)) {
            $errData['details'] = $details;
        }

        $jsonResponse->setData($errData);
        return $jsonResponse;
    }

    /**
     * Prepare CreditCardTransaction with information stored in $orderDto
     *
     * NOTE: the resulting transaction also stored in the DTO so there is
     *       no return here.
     *
     * @param OrderDto $orderDto data transfer object holds all order data
     *
     * @since 2.0.1 set order-number
     * @since 2.1.0 add 3D Secure parameters via ThreeDsHelper
     */
    private function addCreditCardFields(OrderDTO $orderDto)
    {
        $orderDto->transaction->setConfig($orderDto->config);

        $currency = $orderDto->quote->getBaseCurrencyCode();
        $orderDto->amount = new Amount((float)$orderDto->quote->getBaseGrandTotal(), $currency);
        $orderDto->transaction->setAmount($orderDto->amount);
        $this->addOrderIdToTransaction($orderDto);

        $orderDto->transaction->setEntryMode('ecommerce');
        $orderDto->transaction->setLocale(substr($this->resolver->getLocale(), 0, 2));

        $cfgkey = $orderDto->transaction->getConfigKey();
        $methodAppend = '?method=' . urlencode($cfgkey);
        $orderDto->transaction->setRedirect($this->createRedirect($methodAppend));

        if ($this->methodConfig->getValue('send_additional')) {
            $this->setAdditionalInformation($orderDto);
        }
    }

    /**
     * @param $orderDto
     * @throws \Exception
     * @since 3.0.0
     */
    private function addCreditCardThreeDsFields($orderDto)
    {
        $challengeIndicator = $this->methodConfig->getValue('challenge_ind');
        $orderDto->transaction = $this->threeDsHelper->getThreeDsTransaction(
            $challengeIndicator,
            $orderDto->transaction,
            $orderDto
        );
    }

    /**
     * @param OrderDto $orderDto
     * @since 3.1.0
     */
    private function addCreditCardToken(OrderDto $orderDto)
    {
        if ($this->isTokenizedTransactionType($this->getRequest()->getParams())) {
            $tokenFromBrowser = $this->getRequest()->getParam('token');
            $customerId = $this->customerSession->getCustomerId();
            $token = $this->paymentTokenManagement->getByPublicHash($tokenFromBrowser, $customerId);
            $orderDto->transaction->setTokenId($token->getGatewayToken());
        }
    }

    /**
     * @param string $paymentMethod
     * @return Redirect
     * @since 2.2.2
     */
    private function createRedirect($paymentMethod)
    {
        $routeUrl = $this->urlBuilder->getRouteUrl('CreditAgricole_PaymentGateway');
        return new Redirect(
            $routeUrl . 'frontend/redirect' . $paymentMethod,
            $routeUrl . 'frontend/cancel' . $paymentMethod,
            $routeUrl . 'frontend/redirect' . $paymentMethod
        );
    }

    /**
     * Add additional data to transaction
     *
     * NOTE: the resulting transaction also stored in the DTO so there is
     *       no return here.
     *
     * @param OrderDto $orderDto data transfer object holds all order data
     */
    private function setAdditionalInformation(OrderDto $orderDto)
    {
        $orderDto->basket = new Basket();

        $orderDto->transaction->setDescriptor(sprintf(
            '%s %s',
            substr($this->storeManager->getStore()->getName(), 0, 9),
            $orderDto->orderId
        ));

        $orderDto->basket = new Basket();
        $this->addOrderItemsToBasket($orderDto);
        $orderDto->transaction->setBasket($orderDto->basket);
        $orderDto->transaction->setIpAddress($orderDto->quote->getRemoteIp());
        $orderDto->transaction->setConsumerId($orderDto->quote->getCustomerId());
    }

    /**
     * Build basket based on stored items
     *
     * NOTE: the resulting transaction also stored in the DTO so there is
     *       no return here.
     *
     * @param OrderDto $orderDto data transfer object holds all order data
     */
    private function addOrderItemsToBasket(OrderDto $orderDto)
    {
        $items = $orderDto->quote->getAllVisibleItems();
        $currency = $orderDto->quote->getBaseCurrencyCode();
        foreach ($items as $orderItem) {
            $totalAmount = $orderItem->getBasePriceInclTax();
            $taxAmount = $orderItem->getBaseTaxAmount();
            $item = new Item(
                $orderItem->getName(),
                new Amount((float)$totalAmount, $currency),
                $orderItem->getQty()
            );
            $item->setTaxAmount(new Amount((float)$taxAmount, $currency));
            $item->setTaxRate($this->calculateTax($taxAmount, $totalAmount));
            $orderDto->basket->add($item);
        }
    }

    /**
     * @param string $txType frontend key to specify the transaction type
     * @return bool
     * @since 3.0.0
     */
    private function isCreditCardTransactionType($txType)
    {
        if ($txType != self::FRONTEND_CODE_CREDITCARD) {
            return false;
        }
        return true;
    }

    /**
     * @param array $getParams
     * @return bool
     * @since 3.1.0
     */
    private function isTokenizedTransactionType(array $getParams)
    {
        if (!$this->customerSession->isLoggedIn()) {
            return false;
        }
        if (isset($getParams['token']) && trim($getParams['token'])) {
            return true;
        }
        return false;
    }

    /**
     * Convert locale to WPP V2 supported language code
     *
     * @return string
     * @since 2.0.0
     */
    private function getSupportedWppLangCode()
    {
        //Set default for exception case
        $language = 'en';
        $locale = $this->resolver->getLocale();

        //Shorten to ISO-639-1 because of magento2 special cases e.g. zh_Hans_CN
        $locale = mb_substr($locale, 0, 2);
        $converter = new WppVTwoConverter();

        try {
            $converter->init();
            $language = $converter->convert($locale);
        } catch (\InvalidArgumentException $exception) {
            $this->logger->error($exception->getMessage());
            return $language;
        }
        return $language;
    }

    /**
     * Add mandatory order-number to transaction in DTO and custom field orderId for backwards compatibility
     *
     * NOTE: the resulting transaction also stored in the DTO so there is
     *       no return here.
     *
     * @param OrderDto $orderDto data transfer object holds all order data
     * @since 2.0.1
     */
    private function addOrderIdToTransaction(OrderDTO $orderDto)
    {
        $orderDto->customFields = new CustomFieldCollection();
        $orderDto->customFields->add(new CustomField('orderId', $orderDto->orderId));
        $orderDto->transaction->setCustomFields($orderDto->customFields);
        $orderDto->transaction->setOrderNumber($orderDto->orderId);
    }
}
