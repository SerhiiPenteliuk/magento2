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



namespace Mirasvit\Giftr\Model\ResourceModel\Item;

/**
 * @SuppressWarnings(PHPMD)
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'item_id';//@codingStandardsIgnoreLine

    /**
     * @var \Mirasvit\Giftr\Model\RegistryFactory
     */
    protected $registryFactory;

    /**
     * @var \Mirasvit\Giftr\Model\ResourceModel\Item\Option\CollectionFactory
     */
    protected $itemOptionCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactoryInterface
     */
    protected $entityFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface
     */
    protected $fetchStrategy;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\Data\Collection
     */
    protected $connection;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected $resource;

    /**
     * Collection constructor.
     * @param \Mirasvit\Giftr\Model\RegistryFactory $registryFactory
     * @param Option\CollectionFactory $itemOptionCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Mirasvit\Giftr\Model\RegistryFactory $registryFactory,
        \Mirasvit\Giftr\Model\ResourceModel\Item\Option\CollectionFactory $itemOptionCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->registryFactory = $registryFactory;
        $this->itemOptionCollectionFactory = $itemOptionCollectionFactory;
        $this->storeManager = $storeManager;
        $this->entityFactory = $entityFactory;
        $this->logger = $logger;
        $this->fetchStrategy = $fetchStrategy;
        $this->eventManager = $eventManager;
        $this->connection = $connection;
        $this->resource = $resource;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Giftr\Model\Item', 'Mirasvit\Giftr\Model\ResourceModel\Item');
    }

    /**
     * @param bool|false $emptyOption
     * @return array
     */
    public function toOptionArray($emptyOption = false)
    {
        $arr = [];
        if ($emptyOption) {
            $arr[0] = ['value' => 0, 'label' => __('-- Please Select --')];
        }
        foreach ($this as $item) {
            $arr[] = ['value' => $item->getId(), 'label' => $item->getName()];
        }

        return $arr;
    }

    /**
     * @param bool|false $emptyOption
     * @return array
     */
    public function getOptionArray($emptyOption = false)
    {
        $arr = [];
        if ($emptyOption) {
            $arr[0] = __('-- Please Select --');
        }
        foreach ($this as $item) {
            $arr[$item->getId()] = $item->getName();
        }

        return $arr;
    }

    /**
     * @return void
     */
    protected function initFields()
    {
        $select = $this->getSelect();
        $select->joinLeft(
            ['registry' => $this->getTable('mst_giftr_registry')],
            'main_table.registry_id = registry.registry_id',
            ['registry_name' => 'registry.name']
        );
        $select->joinLeft(
            ['product' => $this->getTable('catalog_product_entity')],
            'main_table.product_id = product.entity_id',
            ['sku' => 'product.sku']
        );
        $select->joinLeft(
            ['priority' => $this->getTable('mst_giftr_priority')],
            'main_table.priority_id = priority.priority_id',
            ['priority_name' => 'priority.name', 'sort_order' => 'priority.sort_order']
        );
        // $select->columns(['is_replied' => new \Zend_Db_Expr("answer <> ''")]);
    }

    /**
     * @return void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->initFields();
    }

    /**
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        /*
         * Assign products
         */
        $this->_assignOptions();
        $this->_assignPriorities();

        return $this;
    }

    /**
     * Add options to items.
     *
     * @return \Mirasvit\Giftr\Model\ResourceModel\Item\Collection
     */
    protected function _assignOptions()
    {
        $itemIds = array_keys($this->_items);
        /* @var $optionCollection \Mirasvit\Giftr\Model\ResourceModel\Item\Option\Collection */
        $optionCollection = $this->itemOptionCollectionFactory->create();
        $optionCollection->addItemFilter($itemIds);

        /* @var $item \Mirasvit\Giftr\Model\Item */
        foreach ($this as $item) {
            $item->setOptions($optionCollection->getOptionsByItem($item));
        }
        //$productIds = $optionCollection->getProductIds();
        //$this->_productIds = array_merge($this->_productIds, $productIds);

        return $this;
    }

    /**
     * @return $this
     */
    protected function _assignPriorities()
    {
        // Unserialize priority name, retrieve priority name for current store and if not exists then for default store.
        $storeId = $this->storeManager->getStore()->getId();
        foreach ($this->getItems() as $item) {
            if (@unserialize($item->getPriorityName())) {
                $priorityName = unserialize($item->getPriorityName());
                $priorityName = (array_key_exists($storeId, $priorityName))
                    ? $priorityName[$storeId]
                    : $priorityName[0];
                $item->setPriorityName($priorityName);
                $item->setSortOrder(($item->getSortOrder() == '' || $item->getSortOrder() == null)
                    ? 'abc'
                    : $item->getSortOrder());
            }
        }

        return $this;
    }

    /**
     * Order items by the field sort_order in ASC.
     *
     * @return $this
     */
    public function setSortOrder()
    {
        $this->getSelect()->order(new \Zend_Db_Expr('IF (sort_order = "" or sort_order is null, 1, 0), sort_order'));

        return $this;
    }

    /**
     * Select only active items from active registries.
     *
     * @return $this
     */
    public function addActiveFilter()
    {
        $this->getSelect()->where(
            '(main_table.qty_received < main_table.qty_ordered OR'.
            '(main_table.qty_received = 0 AND main_table.qty_ordered = 0)) AND registry.is_active = 1'
        );

        return $this;
    }

    /**
     * Filter option collection by registry.
     *
     * @param \Mirasvit\Giftr\Model\Registry|int|array $registry
     *
     * @return Collection
     */
    public function addRegistryFilter($registry)
    {
        $registryId = $registry;
        if (is_object($registry)) {
            $registryId = $registry->getId();
        } elseif (!is_array($registry)) {
            $registry = $this->registryFactory->create()->load($registryId);
        }

        if (is_array($registry)) {
            $this->addFieldToFilter('main_table.registry_id', ['in' => $registryId]);
        } else {
            $this->addFieldToFilter('main_table.registry_id', $registryId);
            $this->addFieldToFilter('main_table.store_id', $registry->getStoreId());
        }

        return $this;
    }

     /************************/
}
