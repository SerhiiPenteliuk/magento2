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



namespace Mirasvit\Giftr\Model\Service;


use \Magento\Framework\DataObject;
use \Mirasvit\Giftr\Model\Item;

class CartService
{
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;
    /**
     * @var \Magento\Catalog\Helper\Product
     */
    private $productHelper;
    /**
     * @var \Mirasvit\Giftr\Model\ItemFactory
     */
    private $itemFactory;

    /**
     * CartService constructor.
     * @param \Mirasvit\Giftr\Model\ItemFactory         $itemFactory
     * @param \Magento\Checkout\Model\Cart              $cart
     * @param \Magento\Catalog\Helper\Product           $productHelper
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Mirasvit\Giftr\Model\ItemFactory $itemFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Framework\Event\ManagerInterface $eventManager
        //\Magento\Customer\Model\Session $customerSession
    ) {
        $this->itemFactory = $itemFactory;
        $this->cart = $cart;
        $this->productHelper = $productHelper;
        $this->eventManager = $eventManager;
        //$this->customerSession = $customerSession;
    }

    /**
     * Service for adding giftr item to shopping cart.
     *
     * @param DataObject $buyRequest
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addToCart(DataObject $buyRequest)
    {
        /* @var $item Item */
        $item = $this->itemFactory->create()->loadWithOptions($buyRequest->getItemId());
        $qty = ($buyRequest->getQty()) ? $buyRequest->getQty() : $item->getQty();
        $item->setQty($qty);

        $buyRequest = $this->productHelper->addParamsToBuyRequest(
            $buyRequest,
            ['current_config' => $item->getBuyRequest()]
        );

        $item->mergeBuyRequest($buyRequest);
        if ($item->addToCart($this->cart)) {
            $this->cart->save()->getQuote()->collectTotals();
            //$this->customerSession->setCartWasUpdated(true);
        }

        $this->eventManager->dispatch('giftr_item_purchase_after', [
            'quote' => $this->cart->getQuote(),
            'item' => $item
        ]);
    }
}