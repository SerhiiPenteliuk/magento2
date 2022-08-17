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



namespace Mirasvit\Giftr\Block\Item;

class Add extends \Magento\Framework\View\Element\Template
{
    const MODE_CONFIGURE    = 'configure';
    const MODE_ADD          = 'add';

    /**
     * @var \Mirasvit\Giftr\Model\Config
     */
    private $config;

    /**
     * @var \Magento\Customer\Model\Url
     */
    private $customerUrl;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    private $context;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Mirasvit\Giftr\Model\Registry
     */
    private $giftr;

    /**
     * @param \Mirasvit\Giftr\Model\Config $config
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Mirasvit\Giftr\Model\Registry $giftr
     * @param array $data
     */
    public function __construct(
        \Mirasvit\Giftr\Model\Config $config,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Mirasvit\Giftr\Model\Registry $giftr,
        array $data = []
    ) {
        $this->config = $config;
        $this->customerUrl = $customerUrl;
        $this->registry = $registry;
        $this->context = $context;
        $this->messageManager = $messageManager;
        $this->giftr = $giftr;
        parent::__construct($context, $data);
    }

    /**
     * Get current product.
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * @return \Mirasvit\Giftr\Model\Item
     */
    public function getItem()
    {
        return $this->registry->registry('registry_item');
    }

    /**
     * @return string
     */
    public function getUpdateUrl()
    {
        return $this->getUrl('*/*/update', ['id' => $this->getItem()->getId()]);
    }

    /**
     * @param string $referrer
     * @param string $giftrUid
     * @return string | bool
     */
    public function getRegistryUrl($referrer, $giftrUid)
    {
        $result = false;

        if (!empty($giftrUid)) {
            $registry = $this->giftr->loadByUid(trim($giftrUid));
            if ($registry) {
                $result = $registry->getViewUrl();
            }
        } elseif (!empty($referrer)) {
            if (strstr($referrer, 'giftr/registry/view')) {
                $result = $referrer;
            }
        }

        return $result;
    }

    /**
     * Is validate product selections before adding product to registry.
     *
     * @return int
     */
    public function isValidationNotRequired()
    {
        return (int) $this->config->getIsValidationNotRequired();
    }

    /**
     * Is show registry list for the button "Add to Gift Registry"
     * We do not show registry list when editing existing item.
     *
     * @return int
     */
    public function isShowRegistryList()
    {
        $result = 1;
        if ($this->getRequest()->getModuleName() == 'giftr') {
            $result = (int) ($this->getRequest()->getActionName() != 'configure');
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getJsConfiguration()
    {
        return [
            'Magento_Ui/js/core/app' => [
                'components' => [
                    'giftr-addto__form' => [
                        'component' => 'Mirasvit_Giftr/js/item',
                        'config' => [
                            'url' => $this->getUrl('giftr/item/add'),
                            'newRegistryUrl' => $this->getUrl('giftr/registry/new'),
                            'loginUrl' => $this->customerUrl->getLoginUrl()
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     *
     * @param string $message
     * @return void
     */
    public function setMessage($message)
    {
        $this->messageManager->addNotice($message);
    }
}
