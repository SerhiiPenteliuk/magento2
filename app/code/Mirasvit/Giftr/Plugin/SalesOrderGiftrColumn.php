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


namespace Mirasvit\Giftr\Plugin;

use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as SalesOrderGridCollection;

class SalesOrderGiftrColumn
{
    /**
     * @var MessageManager
     */
    private $messageManager;
    /**
     * @var SalesOrderGridCollection
     */
    private $collection;

    /**
     * SalesOrderGiftrColumn constructor.
     * @param MessageManager $messageManager
     * @param SalesOrderGridCollection $collection
     */
    public function __construct(MessageManager $messageManager,
                                SalesOrderGridCollection $collection
    ) {

        $this->messageManager = $messageManager;
        $this->collection = $collection;
    }

    /**
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject
     * @param \Closure $proceed
     * @param string $requestName
     * @return SalesOrderGridCollection|mixed
     * @throws \Zend_Db_Select_Exception
     */
    public function aroundGetReport(
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject,
        \Closure $proceed,
        $requestName
    ) {
        $result = $proceed($requestName);
        if ($requestName == 'sales_order_grid_data_source') {
            if ($result instanceof $this->collection &&
                !array_key_exists('giftr_purchase', $this->collection->getSelect()->getPart('from'))) {
                $select = new \Zend_Db_Expr('CASE WHEN `giftr_purchase`.`order_id` > 0 THEN "YES" ELSE "NO" END');
                $this->collection->getSelect()->joinLeft(
                    ["giftr_purchase" => $this->collection->getTable("mst_giftr_purchase")],
                    'main_table.entity_id = giftr_purchase.order_id',
                    array('gift_registry_order' => $select)
                );
                $this->collection->getSelect()->group('main_table.entity_id');

                return $this->collection;
            }
        }
        return $result;
    }
}