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
use Mirasvit\Giftr\Model\ItemFactory;


class GiftrItemOrderPlace implements ObserverInterface
{
    /**
     * @var \Mirasvit\Giftr\Model\ItemFactory
     */
    private $itemFactory;

    /**
     * GiftrItemOrderPlace constructor.
     * @param ItemFactory $itemFactory
     */
    public function __construct(
        ItemFactory $itemFactory
    ) {
        $this->itemFactory     = $itemFactory;
    }

    /**
     * @param  Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($observer->hasData('order')) {
            $order = $observer->getData('order');
        } else {
            $order = $observer->getDataByKey('payment')->getOrder();
        }

        $this->itemFactory->create()->changeItemQty($order);
    }
}