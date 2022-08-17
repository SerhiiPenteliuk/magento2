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



namespace Mirasvit\Giftr\Model;

use Magento\Framework\DataObject\IdentityInterface;

class Purchase extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
    //private $registry = null;

    const CACHE_TAG = 'giftr_purchase';

    /**
     * @var string
     */
    protected $_cacheTag = 'giftr_purchase';

    /**
     * @var string
     */
    protected $_eventPrefix = 'giftr_purchase';

    /**
     * @var \Mirasvit\Giftr\Model\RegistryFactory
     */
    protected $registryFactory;

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
     * Purchase constructor.
     * @param RegistryFactory $registryFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Mirasvit\Giftr\Model\RegistryFactory $registryFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->registryFactory    = $registryFactory;
        $this->context            = $context;
        $this->registry           = $registry;
        $this->resource           = $resource;
        $this->resourceCollection = $resourceCollection;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Purchase::class);
    }

    /**
     * Get identities.
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @param bool|false $emptyOption
     *
     * @return array
     */
    public function toOptionArray($emptyOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption);
    }

    /**
     * @return $this|bool|\Magento\Framework\Registry
     */
    public function getRegistry()
    {
        if (!$this->getRegistryId()) {
            return false;
        }
        if (null === $this->registry) {
            $this->registry = $this->registryFactory->create()->load($this->getRegistryId());
        }

        return $this->registry;
    }

    /**
     * Create purchase object based on given quote and gift registry item
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param Item                       $item
     *
     * @return $this
     */
    public function createFromQuoteAndItem(\Magento\Quote\Model\Quote $quote, Item $item)
    {
        $this->setData([
            'registry_id' => $item->getRegistryId(),
            'item_id'     => $item->getId(),
            'quote_id'    => $quote->getId(),
            'customer_id' => $quote->getCustomerId(),
        ])
            ->save();

        return $this;
    }

    /**
     * Remove appropriate purchase from table m_giftr_purchase when giftr_item removed from quote.
     *
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     *
     * @return void
     */
    public function removePurchaseByQuoteItem(\Magento\Quote\Model\Quote\Item $quoteItem)
    {
        // Retrieve purchases for current quote
        $purchases = $this->getCollection()->addQuoteFilter($quoteItem->getQuoteId());

        // If removed item is giftr item
        if ($purchases->getSize() && ($itemId = $quoteItem->getBuyRequest()->getItemId())) {
            $purchases->addFieldToFilter('item_id', $itemId)->delete(); // Remove purchases for removed item
        }
    }

    /**
     * Set order id for purchase.
     *
     * @param \Magento\Sales\Model\Order $order
     * @param int|bool                   $itemId
     *
     * @return void
     */
    public function addOrderToPurchase(\Magento\Sales\Model\Order $order, $itemId = false)
    {
        $purchases = $this->getCollection()
            ->addQuoteFilter($order->getQuoteId())
            ->addFieldToFilter('order_id', ['null' => true]);

        if ($itemId !== false) {
            $purchases->addFieldToFilter('item_id', $itemId);
        }

        foreach ($purchases as $purchase) {
            $purchase = $purchase->load($purchase->getId());
            $purchase->setOrderId($order->getId());

            if (!$purchase->getCustomerId() && $order->getCustomerId()) {
                $purchase->setCustomerId($order->getCustomerId());
            }
            $purchase->save();
        }
    }

    /**
     * Check if order has associated purchases of gift registry items
     *
     * @param \Magento\Sales\Model\Order|int $order
     *
     * @return bool
     */
    public function hasPurchaseForOrder($order)
    {
        $orderId = null;
        if ($order instanceof \Magento\Sales\Model\Order) {
            $orderId = $order->getId();
        } elseif (is_int($order)) {
            $orderId = $order;
        }

        $purchases = $this->getCollection()->addOrderFilter($orderId);

        return $purchases->getSize() > 0;
    }

    /************************/
}
