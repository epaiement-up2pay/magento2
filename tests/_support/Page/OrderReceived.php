<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/epaiement-up2pay/magento2/blob/master/LICENSE
 */

namespace Page;

class OrderReceived extends Base
{

    // include url of current page
    /**
     * @var string
     * @since 1.4.1
     */
    public $URL = 'success';

    /**
     * @var string
     * @since 2.2.0
     */
    public $pageSpecific = 'success';
}
