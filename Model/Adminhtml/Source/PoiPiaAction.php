<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/LICENSE
 */

namespace CreditAgricole\PaymentGateway\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PoiPiaAction implements OptionSourceInterface
{
    const INVOICE = 'invoice';
    const ADVANCE = 'advance';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::INVOICE,
                'label' => __('text_payment_type_poi')
            ],
            [
                'value' => self::ADVANCE,
                'label' => __('text_payment_type_pia')
            ]
        ];
    }
}
