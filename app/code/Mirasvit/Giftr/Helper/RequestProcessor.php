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



namespace Mirasvit\Giftr\Helper;


use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class RequestProcessor
{
    /**
     * @var \Mirasvit\Giftr\Model\ItemFactory
     */
    private $itemFactory;

    /**
     * RequestProcessor constructor.
     * @param \Mirasvit\Giftr\Model\ItemFactory $itemFactory
     */
    public function __construct(
        \Mirasvit\Giftr\Model\ItemFactory $itemFactory
    ) {
        $this->itemFactory = $itemFactory;
    }

    /**
     * Validate buy request passed to giftr_item_addtocart
     *
     * @param DataObject $buyRequest
     * @throws LocalizedException
     */
    public function validateItemAddtocartBuyRequest(DataObject $buyRequest)
    {
        $item = $this->itemFactory->create()->load($buyRequest->getItemId());
        if ($buyRequest->isEmpty() || !$item) {
            throw new LocalizedException(__('Insufficient Data Provided'));
        }
    }
}