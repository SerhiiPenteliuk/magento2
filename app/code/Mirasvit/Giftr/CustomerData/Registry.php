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


namespace Mirasvit\Giftr\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Mirasvit\Giftr\Model\ResourceModel\Registry\CollectionFactory;

class Registry implements SectionSourceInterface
{
    /**
     * @var array
     */
    private $collection = [];

    /**
     * @var CurrentCustomer
     */
    private $currentCustomer;
    /**
     * @var CollectionFactory
     */
    private $registryCollectionFactory;

    /**
     * Registry constructor.
     *
     * @param CurrentCustomer   $currentCustomer
     * @param CollectionFactory $registryCollectionFactory
     */
    public function __construct(
        CurrentCustomer $currentCustomer,
        CollectionFactory $registryCollectionFactory
    ) {
        $this->registryCollectionFactory = $registryCollectionFactory;
        $this->currentCustomer = $currentCustomer;
    }


    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $registries = $this->getCustomerRegistries();

        return [
            'registries' => $registries ? $registries->toOptionArray() : [],
            'selected'   => $registries && $registries->getSize() === 1 ? $registries->getAllIds() : [],
            'is_logged_in' => $this->currentCustomer->getCustomerId() ? true : false,
        ];
    }

    /**
     * @return array|\Mirasvit\Giftr\Model\ResourceModel\Registry\Collection
     */
    protected function getCustomerRegistries()
    {
        if ($this->currentCustomer->getCustomerId()) {
            if (empty($this->collection)) {
                $this->collection = $this->registryCollectionFactory->create()
                    ->addFieldToFilter('customer_id', (int)$this->currentCustomer->getCustomerId())
                    ->addIsActiveFilter();
            }
        }

        return $this->collection;
    }
}
