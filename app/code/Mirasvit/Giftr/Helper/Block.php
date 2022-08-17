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



namespace Mirasvit\Giftr\Helper;

class Block extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Mirasvit\Giftr\Model\RegistryFactory
     */
    protected $registryFactory;

    /**
     * @var \Mirasvit\Giftr\Model\ResourceModel\Priority\CollectionFactory
     */
    protected $priorityCollectionFactory;

    /**
     * @var \Mirasvit\Giftr\Helper\Data
     */
    protected $giftrData;

    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\View\Element\Html\Select
     */
    protected $select;

    /**
     * @param \Magento\Framework\View\Element\Html\Select                    $select
     * @param \Mirasvit\Giftr\Model\RegistryFactory                          $registryFactory
     * @param \Mirasvit\Giftr\Model\ResourceModel\Priority\CollectionFactory $priorityCollectionFactory
     * @param \Mirasvit\Giftr\Helper\Data                                    $giftrData
     * @param \Magento\Framework\App\Helper\Context                          $context
     * @param \Magento\Framework\View\Asset\Repository                       $assetRepo
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface           $localeDate
     * @param \Magento\Customer\Model\CustomerFactory                        $customerFactory
     * @param \Magento\Customer\Model\Session                                $customerSession
     */
    public function __construct(
        \Magento\Framework\View\Element\Html\Select $select,
        \Mirasvit\Giftr\Model\RegistryFactory $registryFactory,
        \Mirasvit\Giftr\Model\ResourceModel\Priority\CollectionFactory $priorityCollectionFactory,
        \Mirasvit\Giftr\Helper\Data $giftrData,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->select = $select;
        $this->registryFactory = $registryFactory;
        $this->priorityCollectionFactory = $priorityCollectionFactory;
        $this->giftrData = $giftrData;
        $this->context = $context;
        $this->assetRepo = $assetRepo;
        $this->localeDate = $localeDate;
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * @param int|null $id                - shipping address ID or gift registry ID
     * @param bool     $isRegistryAddress - is load gift registry's address or not
     *
     * @return \Magento\Framework\View\Element\Html\Select
     */
    public function getAddressHtmlSelect($id = null, $isRegistryAddress = false)
    {
        $options = [];
        $addresses = [];

        if ($isRegistryAddress && $id) {
            $registry = $this->registryFactory->create()->load($id);
            $id = $registry->getShippingAddressId();
            if ($registry->getId()) {
                $addresses[] = $registry->getShippingAddress();
            }
        } else {
            $customer = $this->customerFactory->create()->load($this->customerSession->getCustomerId());
            $addresses = $customer->getAddresses();
        }

        foreach ($addresses as $address) {
            $options[] = [
                'value' => $address->getId(),
                'label' => $address->format('oneline'),
            ];
        }

        $select = $this->select
            ->setName('shipping_address_id')
            ->setId('shipping-address-select')
            ->setClass('address-select')
            //->setExtraParams('onchange="shipping.newAddress(!this.value)"')
            ->setValue($id)
            ->setOptions($options);
        $select->addOption('', __('New Address'));

        return $select;
    }

    /**
     * @param null|int $priorityId
     * @param int $itemId
     * @param string $name
     * @return \Magento\Framework\View\Element\Html\Select
     */
    public function getPriorityHtmlSelect($priorityId = null, $itemId, $name = 'priority_id')
    {
        $collection = $this->priorityCollectionFactory->create();
        $collection->setOrder('sort_order', 'asc');
        $select = $this->select
            ->setName($name)
            ->setLabel(__('Priority'))
            ->setId('priority_' . $itemId)
            ->setClass('priority-select')
            ->setValue($priorityId)
            ->setOptions($collection->toOptionArray(true));

        return $select;
    }
}
