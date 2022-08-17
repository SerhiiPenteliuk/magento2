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


/**
 * Class UpdatePurchaseWithItem
 * @package Mirasvit\Giftr\Observer
 */
class AddOrderToPurchase implements ObserverInterface
{
    /**
     * @var \Mirasvit\Giftr\Model\PurchaseFactory
     */
    private $purchaseFactory;

    /**
     * @param \Mirasvit\Giftr\Model\PurchaseFactory $purchaseFactory
     */
    public function __construct(
        \Mirasvit\Giftr\Model\PurchaseFactory $purchaseFactory
    ) {
        $this->purchaseFactory = $purchaseFactory;
    }

    /**
     * Set order id for purchase.
     * Unset variable 'giftr_id' from customer session to avoid possible collisions with registrant shipping address.
     *
     * @param  Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $item = $observer->getItem();
        $buyRequest = $item->getBuyRequest();
        if ($buyRequest->getRegistryId() && $buyRequest->getItemId()) {
            // Add order_id to associated purchase
            $this->purchaseFactory->create()->addOrderToPurchase($item->getOrder(), $buyRequest->getItemId());
        }
    }
}