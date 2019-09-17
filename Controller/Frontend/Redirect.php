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
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\Redirect as RedirectResult;
use Magento\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface;
use CreditAgricole\PaymentGateway\Gateway\Service\TransactionServiceFactory;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\TransactionService;

/**
 * Class Redirect
 * @package CreditAgricole\PaymentGateway\Controller\Frontend
 * @method Http getRequest()
 */
class Redirect extends Action implements CsrfAwareActionInterface
{
    use \Wirecard\ElasticEngine\Controller\Frontend\NoCsrfTrait;

    const CHECKOUT_URL = 'checkout/cart';

    const REDIRECT_URL = 'redirect-url';

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var TransactionServiceFactory
     */
    private $transactionServiceFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var TransactionService
     */
    private $transactionService;

    /**
     * Redirect constructor.
     * @param Context $context
     * @param Session $checkoutSession
     * @param TransactionServiceFactory $transactionServiceFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        TransactionServiceFactory $transactionServiceFactory,
        LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->transactionServiceFactory = $transactionServiceFactory;
        $this->logger = $logger;
        $this->context = $context;
        parent::__construct($context);
    }

    public function execute()
    {
        $methodName = $this->getMethodName();

        if ($methodName === null || !$this->getRequest()->isPost() && !$this->getRequest()->isGet()) {
            /** @var Json $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $this->handleFailedResponse();

            return $this->getRedirectData($result, self::CHECKOUT_URL);
        }

        $this->transactionService = $this->transactionServiceFactory->create($methodName);

        $params = $this->getRequestParam();
        if (isset($params['data'])) {
            return $this->handleNonThreeDResponse($params['data']);
        }

        return $this->handleResponse($params);
    }

    /**
     * Extracts payment method name from request parameters or from post param
     *
     * @return mixed
     * @since 1.5.2
     */
    private function getMethodName()
    {
        $methodName = $this->getRequest()->getParam('method');
        if ($methodName == null && $this->getRequest()->isPost()) {
            $methodName = $this->getRequest()->getPost()->get('method');
        }
        return $methodName;
    }

    /**
     * Request parameters get distinguished between post and get
     *
     * @return array|mixed
     * @since 1.5.2
     */
    private function getRequestParam()
    {
        if ($this->getRequest()->isPost()) {
            return $this->getRequest()->getPost()->toArray();
        }
        return $this->getRequest()->getParams();
    }

    /**
     * Handles credit card 3D responses and other payment methods
     * Returns redirect page
     *
     * @param $responseParams
     * @return RedirectResult
     * @since 1.5.2
     */
    private function handleResponse($responseParams)
    {
        /**
         * @var $resultRedirect RedirectResult
         */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $response = $this->transactionService->handleResponse($responseParams);

        if ($response instanceof SuccessResponse) {
            $this->setRedirectPath($resultRedirect, 'checkout/onepage/success');
            return $resultRedirect;
        }
        $this->handleFailedResponse();
        $this->setRedirectPath($resultRedirect, self::CHECKOUT_URL);

        return $resultRedirect;
    }

    /**
     * Handles credit card non-3D responses and returns redirect url in json
     *
     * @param $responseParams
     * @return Json
     * @since 1.5.2
     */
    private function handleNonThreeDResponse($responseParams)
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $response = $this->transactionService->processJsResponse($responseParams, $resultRedirect);
        /** @var Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        if ($response instanceof SuccessResponse) {
            return $this->getRedirectData($result, 'onepage/success');
        }
        $this->handleFailedResponse();

        return $this->getRedirectData($result, self::CHECKOUT_URL);
    }

    /**
     * Restores order quote and add error message
     *
     * @since 1.5.2
     */
    private function handleFailedResponse()
    {
        $this->checkoutSession->restoreQuote();
        $this->messageManager->addNoticeMessage(__('order_error'));
    }

    /**
     * @param RedirectResult $resultRedirect
     * @param String $path
     * @return RedirectResult
     */
    private function setRedirectPath(RedirectResult $resultRedirect, $path)
    {
        return $resultRedirect->setPath($path, ['_secure' => true]);
    }

    /**
     * Create redirect data for json ResultFactory with given path
     *
     * @param Json $resultJson
     * @param $path
     * @return Json
     * @since 1.5.2
     */
    private function getRedirectData(Json $resultJson, $path)
    {
        $data = [
            self::REDIRECT_URL => null
        ];
        $data[self::REDIRECT_URL] = $this->context->getUrl()->getRedirectUrl($path);
        $resultJson->setData($data);
        return $resultJson;
    }
}
