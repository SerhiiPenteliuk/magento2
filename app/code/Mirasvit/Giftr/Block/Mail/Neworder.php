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



namespace Mirasvit\Giftr\Block\Mail;

/**
 * @method \Mirasvit\Giftr\Model\Registry getRegistry()
 * @method array getOrderedItems()
 */
class Neworder extends \Magento\Framework\View\Element\Template
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->setTemplate('mail/neworder.phtml');
        $this->setData('area', 'frontend');
        parent::_construct();
    }

    /**
     * Get collection of ordered items.
     *
     * @return \Mirasvit\Giftr\Model\ResourceModel\Item\Collection
     */
    public function getItemCollection()
    {
        $itemIds = array_keys($this->getOrderedItems());
        $items = $this->getRegistry()->getItemCollection()
            ->addFieldToFilter('item_id', ['in', $itemIds]);

        return $items;
    }

    /**
     * @param \Mirasvit\Giftr\Model\Item $item
     * @return string
     */
    public function getEscapedProductName($item)
    {
        return $this->escapeHtml($item->getProduct()->getName());
    }
}
