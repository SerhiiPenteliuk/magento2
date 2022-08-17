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
 * @method \Mirasvit\Giftr\Model\ResourceModel\Item\Collection getItemCollection()
 */
class Items extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $context;

    /**
     * @param \Magento\Catalog\Block\Product\Context           $context
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        array $data = []
    ) {
        $this->context = $context;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mail/items.phtml');
        $this->setData('area', 'frontend');
    }

    /**
     * Return item collection with sort order by 'sort_order'.
     *
     * @return \Mirasvit\Giftr\Model\ResourceModel\Item\Collection
     */
    public function getRegistryItems()
    {
        return $this->getItemCollection()->setSortOrder();
    }

    /**
     * @param \Mirasvit\Giftr\Model\Item $item
     * @return string
     */
    public function getEscapedNote(\Mirasvit\Giftr\Model\Item $item)
    {
        $note = '&nbsp;';
        if ($item->hasNote()) {
            $note = substr($this->escapeHtml($item->getNote()), 0, 300);
        }

        return $note;
    }

    /**
     * @param \Mirasvit\Giftr\Model\Item $item
     * @return bool
     */
    public function hasNote(\Mirasvit\Giftr\Model\Item $item)
    {
        return $item->getNote() != '';
    }

    /**
     * @param \Mirasvit\Giftr\Model\Item $item
     * @return string
     */
    public function getItemAddToCartUrl(\Mirasvit\Giftr\Model\Item $item)
    {
        return $item->getAddToCartUrl(['is_email' => true]);
    }
}
