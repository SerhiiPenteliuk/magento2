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



namespace Mirasvit\Giftr\Model\ResourceModel\Registry;

/**
 * @SuppressWarnings(PHPMD)
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'registry_id';//@codingStandardsIgnoreLine

    /**
     * @var \Mirasvit\Giftr\Model\ResourceModel\Purchase\Collection
     */
    protected $purchaseCollection;

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
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

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
     * @param \Mirasvit\Giftr\Model\ResourceModel\Purchase\Collection $purchaseCollection
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Mirasvit\Giftr\Model\ResourceModel\Purchase\Collection $purchaseCollection,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->purchaseCollection = $purchaseCollection;
        $this->entityFactory = $entityFactory;
        $this->logger = $logger;
        $this->fetchStrategy = $fetchStrategy;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->connection = $connection;
        $this->resource = $resource;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Giftr\Model\Registry', 'Mirasvit\Giftr\Model\ResourceModel\Registry');
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
            ['type' => $this->getTable('mst_giftr_type')],
            'main_table.type_id = type.type_id',
            ['type_name' => 'type.name']
        );
        $select->joinLeft(
            ['website' => $this->getTable('store_website')],
            'main_table.website_id = website.website_id',
            ['website_name' => 'website.name']
        );
        
        // $select->columns(['is_replied' => new \Zend_Db_Expr("answer <> ''")]);
    }

    /**
     * @param mixed $collection
     * @return void
     */
    protected function addQtyFilter($collection)
    {
        $collection->getSelect->joinInner(
            ['items' => $this->getTable('mst_giftr_item')],
            'main_table.registry_id = items.registry_id and qty > qty_received',
            []
        );

        $select->where('items.qty > items.qty_received');
        $select->group('items.registry_id');
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
    public function addIsActiveFilter()
    {
        $this->addFieldToFilter('main_table.is_active', '1');

        return $this;
    }

    /**
     * Function for joining customer table and retrieving registrant full name
     * if $isReturnExpr = true - then just build and return expression
     *
     * @param bool|false $isReturnExpr
     * @return Collection|\Zend_Db_Expr
     */
    public function joinRegistrantName($isReturnExpr = false)
    {
        $sql = ($isReturnExpr) ? '' : ' AS registrant_name';
        $sql = 'CONCAT_WS(" ", IF(`main_table`.`firstname` = "", `c`.`firstname`, `main_table`.`firstname`),
                IF(`main_table`.`lastname` = "", `c`.`lastname`, `main_table`.`lastname`))' . $sql;
        $expr = new \Zend_Db_Expr($sql);

        if (!$isReturnExpr) {
            $this->getSelect()
                ->columns($expr)
                ->joinLeft(
                    ['c' => $this->getTable('customer_entity')],
                    'c.entity_id = main_table.customer_id',
                    []
                );
        }

        return $isReturnExpr ? $expr : $this;
    }

    /**
     * Join number of orders
     *
     * @return $this
     */
    public function joinOrdersQty()
    {
        $countSelect = $this->purchaseCollection->getSelect();
        $countSelect->reset('columns')
            ->columns(['qty' => new \Zend_Db_Expr('COUNT(DISTINCT order_id)'), 'registry_id' => 'registry_id'])
            ->where('order_id IS NOT NULL')
            ->group('registry_id');

        $this->getSelect()
            ->joinLeft(
                ['p' => $countSelect],
                'p.registry_id = main_table.registry_id',
                ['sum_order_qty' => new \Zend_Db_Expr('IF(p.qty IS NULL, 0, p.qty)')]
            );

        return $this;
    }

    /**
     * Filter collection by website
     *
     * @return $this
     */
    public function addWebsiteFilter()
    {
        $this->addFieldToFilter('main_table.website_id', $this->storeManager->getWebsite()->getId());

        return $this;
    }

     /************************/
}
