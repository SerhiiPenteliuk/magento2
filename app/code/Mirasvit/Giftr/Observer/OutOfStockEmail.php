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



namespace Mirasvit\Giftr\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;


class OutOfStockEmail implements ObserverInterface
{
    /**
     * @var \Mirasvit\Giftr\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    private $stockRegistry;

    /**
     * @param \Mirasvit\Giftr\Helper\Data                   $helper
     * @param \Magento\CatalogInventory\Model\StockRegistry $stockRegistry
     */
    public function __construct(
        \Mirasvit\Giftr\Helper\Data $helper,
        \Magento\CatalogInventory\Model\StockRegistry $stockRegistry
    ) {
        $this->helper = $helper;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Send "out of stock" email to gift registrant if an item becomes out of stock
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getProduct();
        $stockItem = $this->stockRegistry->getStockItem($product->getId());
        $stockData = (array)$product->getStockData();

        if ($stockItem->getIsInStock() &&
            isset($stockData['is_in_stock']) &&
            (
                !$stockData['is_in_stock'] ||
                (
                    isset($stockData['qty']) && isset($stockData['original_inventory_qty']) &&
                    !$stockData['qty'] && $stockData['original_inventory_qty'] != $stockData['qty']
                )
            )
        ) {
            foreach ($this->helper->getAssociatedGiftrItems($product->getId()) as $giftrItem) {
                $giftrItem->sendNotificationOutOfStockItem();
            }
        }
    }
}