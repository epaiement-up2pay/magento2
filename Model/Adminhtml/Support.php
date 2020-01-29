<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/magento2-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/magento2-ee/blob/master/LICENSE
 */

namespace CreditAgricole\PaymentGateway\Model\Adminhtml;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\DataObject;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Module\ModuleList\Loader;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Payment\Model\Config;

class Support
{
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Framework\Module\ModuleList\Loader
     */
    protected $moduleLoader;

    /**
     * @var \Magento\Payment\Model\Config
     */
    protected $paymentConfig;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var array
     */
    protected $_moduleBlacklist = [
        'Magento_Store',
        'Magento_AdvancedPricingImportExport',
        'Magento_Directory',
        'Magento_Theme',
        'Magento_Backend',
        'Magento_Backup',
        'Magento_Eav',
        'Magento_Customer',
        'Magento_BundleImportExport',
        'Magento_AdminNotification',
        'Magento_CacheInvalidate',
        'Magento_Indexer',
        'Magento_Cms',
        'Magento_CatalogImportExport',
        'Magento_Catalog',
        'Magento_Rule',
        'Magento_Msrp',
        'Magento_Search',
        'Magento_Bundle',
        'Magento_Quote',
        'Magento_CatalogUrlRewrite',
        'Magento_Widget',
        'Magento_SalesSequence',
        'Magento_CheckoutAgreements',
        'Magento_Payment',
        'Magento_Downloadable',
        'Magento_CmsUrlRewrite',
        'Magento_Config',
        'Magento_ConfigurableImportExport',
        'Magento_CatalogInventory',
        'Magento_SampleData',
        'Magento_Contact',
        'Magento_Cookie',
        'Magento_Cron',
        'Magento_CurrencySymbol',
        'Magento_CatalogSearch',
        'Magento_CustomerImportExport',
        'Magento_CustomerSampleData',
        'Magento_Deploy',
        'Magento_Developer',
        'Magento_Dhl',
        'Magento_Authorization',
        'Magento_User',
        'Magento_ImportExport',
        'Magento_Sales',
        'Magento_CatalogRule',
        'Magento_Email',
        'Magento_EncryptionKey',
        'Magento_Fedex',
        'Magento_GiftMessage',
        'Magento_Checkout',
        'Magento_GoogleAnalytics',
        'Magento_GoogleOptimizer',
        'Magento_GroupedImportExport',
        'Magento_GroupedProduct',
        'Magento_Tax',
        'Magento_DownloadableImportExport',
        'Magento_Integration',
        'Magento_LayeredNavigation',
        'Magento_Marketplace',
        'Magento_MediaStorage',
        'Magento_ConfigurableProduct',
        'Magento_MsrpSampleData',
        'Magento_Multishipping',
        'Magento_NewRelicReporting',
        'Magento_Newsletter',
        'Magento_OfflinePayments',
        'Magento_SalesRule',
        'Magento_OfflineShipping',
        'Magento_PageCache',
        'Magento_Captcha',
        'Magento_Persistent',
        'Magento_ProductAlert',
        'Magento_Weee',
        'Magento_ProductVideo',
        'Magento_CatalogSampleData',
        'Magento_Reports',
        'Magento_RequireJs',
        'Magento_Review',
        'Magento_BundleSampleData',
        'Magento_Rss',
        'Magento_DownloadableSampleData',
        'Magento_OfflineShippingSampleData',
        'Magento_ConfigurableSampleData',
        'Magento_SalesSampleData',
        'Magento_ProductLinksSampleData',
        'Magento_ThemeSampleData',
        'Magento_ReviewSampleData',
        'Magento_SendFriend',
        'Magento_Ui',
        'Magento_Sitemap',
        'Magento_CatalogRuleConfigurable',
        'Magento_Swagger',
        'Magento_Swatches',
        'Magento_SwatchesSampleData',
        'Magento_GroupedProductSampleData',
        'Magento_TaxImportExport',
        'Magento_TaxSampleData',
        'Magento_GoogleAdwords',
        'Magento_CmsSampleData',
        'Magento_Translation',
        'Magento_Shipping',
        'Magento_Ups',
        'Magento_UrlRewrite',
        'Magento_CatalogRuleSampleData',
        'Magento_Usps',
        'Magento_Variable',
        'Magento_Version',
        'Magento_Webapi',
        'Magento_SalesRuleSampleData',
        'Magento_CatalogWidget',
        'Magento_WidgetSampleData',
        'Magento_Wishlist',
        'Magento_WishlistSampleData'
    ];
    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param Loader $moduleLoader
     * @param Config $paymentConfig
     * @param ModuleListInterface $moduleList
     * @param ProductMetadata $productMetadata
     * @internal param ScopeConfigInterface $scopePool
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        Loader $moduleLoader,
        Config $paymentConfig,
        ModuleListInterface $moduleList,
        ProductMetadata $productMetadata
    ) {
        $this->moduleList = $moduleList;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->moduleLoader = $moduleLoader;
        $this->paymentConfig = $paymentConfig;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @param DataObject $postObject
     *
     * @return bool
     * @throws \Exception
     */
    public function sendrequest($postObject)
    {
        if (strlen(trim($postObject->getData('replyto')))) {
            if (!filter_var($postObject->getData('replyto'), FILTER_VALIDATE_EMAIL)) {
                throw new \Exception(__('enter_valid_email_error'));
            }
            $this->transportBuilder->setReplyTo(trim($postObject->getData('replyto')));
        }

        $sender = [
            'name' => $this->scopeConfig->getValue('trans_email/ident_general/name'),
            'email' => $this->scopeConfig->getValue('trans_email/ident_general/email'),
        ];

        if (!strlen($sender['email'])) {
            throw new \Exception(__('enter_valid_email_error'));
        }

        $modules = [];
        foreach ($this->moduleLoader->load() as $module) {
            if (!in_array($module['name'], $this->_moduleBlacklist)) {
                $modules[] = $module['name'];
            }
        }
        natsort($modules);

        $payments = $this->paymentConfig->getActiveMethods();

        $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $foreign = [];
        $mine = [];
        foreach ($payments as $paymentCode => $paymentModel) {
            $method = [
                'value' => $paymentCode,
                'config' => []
            ];

            if (preg_match('/^CreditAgricole_PaymentGateway/i', $paymentCode)) {
                $method['config'] = $this->scopeConfig->getValue('payment/' . $paymentCode, $scope);
                $mine[$paymentCode] = $method;
            } else {
                $foreign[$paymentCode] = $method;
            }
        }

        $versioninfo = new \Magento\Framework\DataObject();
        $versioninfo->setData([
            'product' => 'Magento2',
            'productVersion' => $this->productMetadata->getVersion(),
            'pluginName' => 'CreditAgricole_PaymentGateway',
            'pluginVersion' => $this->moduleList->getOne('CreditAgricole_PaymentGateway')['setup_version']
        ]);
        $transport = $this->transportBuilder
            ->setTemplateIdentifier('contact_support_email')
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
            )
            ->setTemplateVars([
                'data' => $postObject,
                'modules' => $modules,
                'foreign' => $foreign,
                'mine' => $mine,
                'configstr' => $this->getConfigString(),
                'versioninfo' => $versioninfo
            ])
            // Exchange of setFrom to setFromByScope results in incompatibility to Magento 2.2
            ->setFrom($sender)
            ->addTo('support-epaiement@up2pay.fr')
            ->getTransport();

        $transport->sendMessage();

        return true;
    }

    /**
     * @return string
     */
    private function getConfigString()
    {
        $config = $this->scopeConfig->getValue(
            'CreditAgricole_PaymentGateway/credentials',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $config_str = "";

        foreach ($config as $key => $value) {
            if (in_array($key, ['pass'])) {
                continue;
            }
            $config_str .= "[$key] = $value\n";
        }

        return $config_str;
    }
}
