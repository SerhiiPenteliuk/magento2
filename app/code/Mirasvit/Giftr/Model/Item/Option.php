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



namespace Mirasvit\Giftr\Model\Item;

use Magento\Framework\DataObject\IdentityInterface;

class Option extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'giftr_item_option';

    /**
     * @var null
     */
    protected $_item = null;

    /**
     * @var null
     */
    protected $_product = null;

    /**
     * @var string
     */
    protected $_cacheTag = 'giftr_item_option';

    /**
     * @var string
     */
    protected $_eventPrefix = 'giftr_item_option';

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\ItemFactory
     */
    protected $stockItemFactory;

    /**
     * @var \Magento\Framework\Model\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Model\ResourceModel\AbstractResource
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb
     */
    protected $resourceCollection;

    /**
     * @param \Magento\Catalog\Model\ProductFactory                   $productFactory
     * @param \Magento\CatalogInventory\Model\Stock\ItemFactory       $stockItemFactory
     * @param \Magento\Framework\Model\Context                        $context
     * @param \Magento\Framework\Registry                             $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb           $resourceCollection
     * @param array                                                   $data
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\CatalogInventory\Model\Stock\ItemFactory $stockItemFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        $this->stockItemFactory = $stockItemFactory;
        $this->context = $context;
        $this->registry = $registry;
        $this->resource = $resource;
        $this->resourceCollection = $resourceCollection;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Giftr\Model\ResourceModel\Item\Option');
    }

    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Get option item.
     *
     * @return \Magento\Wishlist\Model\Item
     */
    public function getItem()
    {
        return $this->_item;
    }

    /**
     * Retrieve value associated with this option.
     *
     * @return string|null
     */
    public function getValue()
    {
        return $this->_getData('value');
    }

    /**
     * Set quote item.
     *
     * @param \Mirasvit\Giftr\Model\Item $item
     *
     * @return Option
     */
    public function setItem($item)
    {
        $this->setItemId($item->getId());
        $this->_item = $item;

        return $this;
    }

    /**
     * Set quote item.
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return \Mirasvit\Giftr\Model\Item\Option
     */
    public function setProduct($product)
    {
        $this->setProductId($product->getId());
        $this->_product = $product;

        return $this;
    }

    /**
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getProduct()
    {
        if (null === $this->_product) {
            $product = $this->productFactory->create()->load($this->getProductId());
            $this->setProduct($product);
        }

        return $this->_product;
    }

    /**
     * Initialize item identifier before save data.
     *
     * @return \Magento\Wishlist\Model\Item\Option
     */
    public function beforeSave()
    {
        if ($this->getItem()) {
            $this->setItemId($this->getItem()->getId());
        }

        return parent::beforeSave();
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {
        $inventory = $this->stockItemFactory->create()->loadByProduct($this->getProduct());

        return $inventory->getIsInStock();
    }
}
