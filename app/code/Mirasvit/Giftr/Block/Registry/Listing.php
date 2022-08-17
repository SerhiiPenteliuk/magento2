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



namespace Mirasvit\Giftr\Block\Registry;

class Listing extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Mirasvit\Giftr\Model\ResourceModel\Registry\CollectionFactory
     */
    protected $registryCollectionFactory;

    /**
     * @var \Mirasvit\Giftr\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $context;

    /**
     * @param \Mirasvit\Giftr\Model\ResourceModel\Registry\CollectionFactory $registryCollectionFactory
     * @param \Mirasvit\Giftr\Model\Config                                   $config
     * @param \Magento\Customer\Model\CustomerFactory                        $customerFactory
     * @param \Magento\Customer\Model\Session                                $customerSession
     * @param \Magento\Framework\View\Element\Template\Context               $context
     * @param array                                                          $data
     */
    public function __construct(
        \Mirasvit\Giftr\Model\ResourceModel\Registry\CollectionFactory $registryCollectionFactory,
        \Mirasvit\Giftr\Model\Config $config,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->registryCollectionFactory = $registryCollectionFactory;
        $this->config = $config;
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
        $this->context = $context;
        parent::__construct($context, $data);
    }

    /**
     * @var \Mirasvit\Giftr\Model\ResourceModel\Registry\Collection
     */
    private $collection = null;

    /**
     * Get registry collection associated with current customer.
     *
     * @return null|\Mirasvit\Giftr\Model\ResourceModel\Registry\Collection
     */
    public function getCustomerRegistries()
    {
        if (null === $this->collection) {
            $customer = $this->customerFactory->create()->load($this->customerSession->getCustomerId());
            if ($customer && $customer->getId()) {
                $this->collection = $this->registryCollectionFactory->create()
                    ->addFieldToFilter('customer_id', $customer->getId());
            }
        }

        return $this->collection;
    }

    /**
     * @return \Mirasvit\Giftr\Model\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    public function getJsConfig()
    {
        return ['*' =>
            [
                'Magento_Ui/js/core/app' => [
                    'components' => [
                        'giftr_delete' => [
                            'component' => 'Mirasvit_Giftr/js/giftr',
                        ],
                    ],
                ],
            ],
        ];
    }
}
