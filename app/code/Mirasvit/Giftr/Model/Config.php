<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-gift-registry
 * @version   1.2.34
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Giftr\Model;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class Config
{
    const XML_PATH_GIFTR_VISIBILITY = 'giftr/general/visibility';

    const XML_PATH_GIFTR_GIFT_MESSAGE = 'giftr/general/gift_message';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Model\Context
     */
    protected $context;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    private $orderConfig;

    /**
     * @param \Magento\Sales\Model\Order\Config                  $orderConfig
     * @param \Magento\Framework\UrlInterface                    $urlManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem                      $filesystem
     * @param \Magento\Framework\Model\Context                   $context
     * @param array                                              $data
     */
    public function __construct(
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\UrlInterface $urlManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Model\Context $context,
        array $data = []
    ) {
        $this->orderConfig = $orderConfig;
        $this->urlManager = $urlManager;
        $this->scopeConfig = $scopeConfig;
        $this->filesystem = $filesystem;
        $this->context = $context;
    }

    /**
     * @var string
     */
    const FIELD_TYPE_TEXT = 'text';

    /**
     * @var string
     */
    const FIELD_TYPE_TEXTAREA = 'textarea';

    /**
     * @var string
     */
    const FIELD_TYPE_DATE = 'date';

    /**
     * @var string
     */
    const FIELD_TYPE_CHECKBOX = 'checkbox';

    /**
     * @var string
     */
    const FIELD_TYPE_SELECT = 'select';

    /**
     * @var int
     */
    const IS_PUBLIC_1 = 1;

    /**
     * @var int
     */
    const IS_PUBLIC_0 = 0;

    /**
     * @var string
     */
    const IMAGE_FOLDER_NAME = 'giftr';

    /**
     * @param null $store
     * @return string
     */
    public function getGeneralIsShowMiddlename($store = null)
    {
        return $this->scopeConfig->getValue(
            'giftr/general/is_show_middlename',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }

    /**
     * @param null $store
     * @return array
     */
    public function getGeneralOrderInvoicedStatus($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'giftr/general/order_invoiced_status',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );

        return explode(',', $value);
    }

    /**
     * @param null $store
     * @return array
     */
    public function getGeneralOrderReceivedStatus($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'giftr/general/order_received_status',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );

        return explode(',', $value);
    }

    /**
     * @return array
     */
    public function getGeneralOrderCanceledStatus()
    {
        return $this->orderConfig
            ->getStateStatuses(
                [\Magento\Sales\Model\Order::STATE_CANCELED, \Magento\Sales\Model\Order::STATE_CLOSED],
                false
            );
    }

    /**
     * @param null $store
     * @return string
     */
    public function getNotificationSenderGroup($store = null)
    {
        return $this->scopeConfig->getValue(
            'giftr/notification/sender_email',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }

    /**
     * @param null $store
     * @return string
     */
    public function getNotificationSenderName($store = null)
    {
        return $this->scopeConfig->getValue("trans_email/ident_{$this->getNotificationSenderGroup()}/name");
    }

    /**
     * @param null $store
     * @return string
     */
    public function getNotificationSenderEmail($store = null)
    {
        return $this->scopeConfig->getValue("trans_email/ident_{$this->getNotificationSenderGroup()}/email");
    }

    /**
     * @param null $store
     * @return string
     */
    public function getNotificationOwnerEmailTemplate($store = null)
    {
        return $this->scopeConfig->getValue(
            'giftr/notification/owner_email_template',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }

    /**
     * @param null $store
     * @return string
     */
    public function getNotificationSharingEmailTemplate($store = null)
    {
        return $this->scopeConfig->getValue(
            'giftr/notification/sharing_email_template',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }

    /**
     * @param null $store
     * @return string
     */
    public function getNotificationOutOfStockEmailTemplate($store = null)
    {
        return $this->scopeConfig->getValue(
            'giftr/notification/outofstock_item_email_template',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }

    /**
     * @param null $store
     * @return string
     */
    public function getNotificationUpdateEmailTemplate($store = null)
    {
        return $this->scopeConfig->getValue(
            'giftr/notification/update_email_template',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }

    /**
     * @return string
     */
    public function getBaseMediaPath()
    {
        $path = $this->filesystem
            ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
            ->getAbsolutePath() . self::IMAGE_FOLDER_NAME;

        if (!file_exists($path) || !is_dir($path)) {
            $this->filesystem
                ->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
                ->create($path);
        }

        return $path;
    }

    /**
     * @return string
     */
    public function getBaseMediaUrl()
    {
        return $this->urlManager->getBaseUrl('media') . '/giftr';
    }

    /**
     * @return string
     */
    public function getIsValidationNotRequired()
    {
        return $this->scopeConfig->getValue('giftr/general/is_product_validation_not_required');
    }

    /**
     * @return string
     */
    public function getForceShipping()
    {
        return $this->scopeConfig->getValue('giftr/general/force_shipping');
    }

    /**
     * @return string
     */
    public function getIsAddtoCategory()
    {
        return $this->scopeConfig->getValue('giftr/general/is_addto_category');
    }

    /**
     * @return string
     */
    public function getPlaceholder()
    {
        return $this->scopeConfig->getValue('giftr/general/placeholder');
    }

    /**
     * Show public registries in the search results by default or not
     *
     * @return string
     */
    public function getVisibility()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_GIFTR_VISIBILITY);
    }

    /**
     * Show gift messages or not.
     *
     * @return mixed
     */
    public function getIsShowGiftMessages()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_GIFTR_GIFT_MESSAGE);
    }

    /**
     * Hide expired evenst or not.
     *
     * @return mixed
     */
    public function getIsHideExpiredEvents()
    {
        return $this->scopeConfig->getValue('giftr/general/hide_expired');
    }

    /************************/
}
