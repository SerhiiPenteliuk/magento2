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


class UpdatePurchasedItemQty implements ObserverInterface
{
    /**
     * @var \Mirasvit\Giftr\Helper\Data
     */
    private $helper;

    /**
     * @var \Mirasvit\Giftr\Model\PurchaseFactory
     */
    private $purchaseFactory;

    /**
     * @var \Mirasvit\Giftr\Model\ItemFactory
     */
    private $itemFactory;

    /**
     * UpdatePurchasedItemQty constructor.
     * @param \Mirasvit\Giftr\Helper\Data $helper
     * @param \Mirasvit\Giftr\Model\PurchaseFactory $purchaseFactory
     * @param \Mirasvit\Giftr\Model\ItemFactory $itemFactory
     */
    public function __construct(
        \Mirasvit\Giftr\Helper\Data $helper,
        \Mirasvit\Giftr\Model\PurchaseFactory $purchaseFactory,
        \Mirasvit\Giftr\Model\ItemFactory $itemFactory
    ) {
        $this->helper          = $helper;
        $this->purchaseFactory = $purchaseFactory;
        $this->itemFactory     = $itemFactory;
    }

    /**
     * Check item order status, change qty of ordered/received items.
     *
     * @param  Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        if ($this->purchaseFactory->create()->hasPurchaseForOrder($order) && $order->dataHasChangedFor('status')) {
            // Update Invoiced/Received QTY of purchased gift registry items
            $this->itemFactory->create()->updateItemQty($order);
        }
    }
}