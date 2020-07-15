<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/LICENSE
 */

namespace CreditAgricole\PaymentGateway\Gateway\Helper;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Helper for getting order from order repository
 *
 * @since 2.1.0
 */
class Order
{

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     *
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->orderRepository       = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param $orderId
     *
     * @return \Magento\Sales\Model\Order
     * @throws NoSuchEntityException
     */
    public function getOrderByIncrementId($orderId)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            OrderInterface::INCREMENT_ID,
            $orderId
        )->create();

        $result = $this->orderRepository->getList($searchCriteria);

        if (empty($result->getItems())) {
            throw new NoSuchEntityException(__('no_such_order_error'));
        }

        $orders = $result->getItems();

        return reset($orders);
    }
}
