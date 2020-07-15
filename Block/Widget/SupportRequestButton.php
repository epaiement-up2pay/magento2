<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/LICENSE
 */

namespace CreditAgricole\PaymentGateway\Block\Widget;

use Magento\Backend\Block\Widget\Button;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class SupportRequestButton extends Field
{
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->setElement($element);

        $url = $this->getUrl('CreditAgricole_PaymentGateway/support/contact');
        $html = $this->getLayout()->createBlock(Button::class)
            ->setType('button')
            ->setClass('scalable')
            ->setLabel('Contact support')
            ->setOnClick("setLocation('$url')")
            ->toHtml();

        return $html;
    }
}
