<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/LICENSE
 */

namespace CreditAgricole\PaymentGateway\Controller\Adminhtml\Support;

class Index extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $this->_redirect('adminhtml/system_config/edit/section/CreditAgricole_PaymentGateway');
    }
}
