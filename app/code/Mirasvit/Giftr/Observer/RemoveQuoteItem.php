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


class RemoveQuoteItem implements ObserverInterface
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
     * @param \Mirasvit\Giftr\Helper\Data           $helper
     * @param \Mirasvit\Giftr\Model\PurchaseFactory $purchaseFactory
     */
    public function __construct(
        \Mirasvit\Giftr\Helper\Data $helper,
        \Mirasvit\Giftr\Model\PurchaseFactory $purchaseFactory
    ) {
        $this->helper = $helper;
        $this->purchaseFactory = $purchaseFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        if ($this->helper->isGiftrPurchase()) {
            $this->purchaseFactory->create()->removePurchaseByQuoteItem($observer->getQuoteItem());
        }
    }
}