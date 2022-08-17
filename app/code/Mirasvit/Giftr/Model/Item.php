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

use Magento\Framework\App\ProductMetadataInterface;

/**
 * @method int  getQtyOrdered()
 * @method void setQtyOrdered($qty)
 * @method int  getQtyReceived()
 * @method void setQtyReceived($qty)
 * @method int  getProductId()
 * @method int  getRegistryId()
 * @method int  getQty()
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Item extends \Magento\Framework\Model\AbstractModel implements
    \Magento\Framework\DataObject\IdentityInterface,
    \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface
{
    const EXCEPTION_CODE_ITEM_OPTION_OUT_OF_STOCK = 98789;
    const CACHE_TAG                               = 'giftr_item';

    /**
     * @var string
     */
    protected $_cacheTag = 'giftr_item';

    /**
     * @var string
     */
    protected $_eventPrefix = 'giftr_item';

    /**
     * @var \Mirasvit\Giftr\Model\Registry|null
     */
    protected $registry = null;

    /**
     * @var \Magento\Catalog\Model\Product|null
     */
    protected $_product = null;

    /**
     * @var \Mirasvit\Giftr\Model\Priority|null
     */
    protected $_priority = null;

    /**
     * @var array
     */
    protected $_options = [];

    /**
     * Flag stating that options were successfully saved.
     */
    protected $_flagOptionsSaved = null;

    /**
     * Item options by code cache.
     * @var array
     */
    protected $_optionsByCode = [];

    /**
     * Not Represent options.
     * @var array
     */
    protected $_notRepresentOptions = ['info_buyRequest'];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Url
     */
    protected $catalogUrl;

    /**
     * @var \Mirasvit\Giftr\Model\RegistryFactory
     */
    protected $registryFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Mirasvit\Giftr\Model\PriorityFactory
     */
    protected $priorityFactory;

    /**
     * @var \Mirasvit\Giftr\Model\ConfigFactory
     */
    protected $configFactory;

    /**
     * @var \Mirasvit\Giftr\Model\Item\OptionFactory
     */
    protected $itemOptionFactory;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\ItemFactory
     */
    protected $stockItemFactory;

    /**
     * @var \Mirasvit\Giftr\Model\ResourceModel\Item\Option\CollectionFactory
     */
    protected $itemOptionCollectionFactory;

    /**
     * @var \Mirasvit\Core\Helper\Image
     */
    protected $coreImage;

    /**
     * @var \Mirasvit\Giftr\Helper\Mail
     */
    protected $giftrMail;

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $widgetContext;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ItemFactory
     */
    private $itemFactory;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetaData;

    /**
     * @param ProductMetadataInterface $productMetaData
     * @param ItemFactory $itemFactory
     * @param \Magento\Catalog\Model\ResourceModel\Url $catalogUrl
     * @param \Mirasvit\Giftr\Model\RegistryFactory $registryFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Mirasvit\Giftr\Model\PriorityFactory $priorityFactory
     * @param \Mirasvit\Giftr\Model\ConfigFactory $configFactory
     * @param \Mirasvit\Giftr\Model\Item\OptionFactory $itemOptionFactory
     * @param \Magento\CatalogInventory\Model\Stock\ItemFactory $stockItemFactory
     * @param \Mirasvit\Giftr\Model\ResourceModel\Item\Option\CollectionFactory $itemOptionCollectionFactory
     * @param \Mirasvit\Core\Helper\Image $coreImage
     * @param \Mirasvit\Giftr\Helper\Mail $giftrMail
     * @param \Magento\Backend\Block\Widget\Context $widgetContext
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ProductMetadataInterface $productMetaData,
        \Mirasvit\Giftr\Model\ItemFactory $itemFactory,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        \Mirasvit\Giftr\Model\RegistryFactory $registryFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Mirasvit\Giftr\Model\PriorityFactory $priorityFactory,
        \Mirasvit\Giftr\Model\ConfigFactory $configFactory,
        \Mirasvit\Giftr\Model\Item\OptionFactory $itemOptionFactory,
        \Magento\CatalogInventory\Model\Stock\ItemFactory $stockItemFactory,
        \Mirasvit\Giftr\Model\ResourceModel\Item\Option\CollectionFactory $itemOptionCollectionFactory,
        \Mirasvit\Core\Helper\Image $coreImage,
        \Mirasvit\Giftr\Helper\Mail $giftrMail,
        \Magento\Backend\Block\Widget\Context $widgetContext,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->productMetaData             = $productMetaData;
        $this->itemFactory                 = $itemFactory;
        $this->catalogUrl                  = $catalogUrl;
        $this->registryFactory             = $registryFactory;
        $this->productFactory              = $productFactory;
        $this->priorityFactory             = $priorityFactory;
        $this->configFactory               = $configFactory;
        $this->itemOptionFactory           = $itemOptionFactory;
        $this->stockItemFactory            = $stockItemFactory;
        $this->itemOptionCollectionFactory = $itemOptionCollectionFactory;
        $this->coreImage                   = $coreImage;
        $this->giftrMail                   = $giftrMail;
        $this->widgetContext               = $widgetContext;
        $this->urlManager                  = $widgetContext->getUrlBuilder();
        $this->storeManager                = $widgetContext->getStoreManager();
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Giftr\Model\ResourceModel\Item');
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
     * @return \Mirasvit\Giftr\Model\Registry
     */
    public function getRegistry()
    {
        if (!$this->getRegistryId()) {
            return false;
        }
        if ($this->registry === null) {
            $this->registry = $this->registryFactory->create()->load($this->getRegistryId());
        }

        return $this->registry;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if (!$this->getProductId()) {
            return false;
        }
        if ($this->_product === null) {
            $this->_product = $this->productFactory->create()->load($this->getProductId());
        }

        /*
         * Reset product final price because it related to custom options
         */
        $this->_product->setFinalPrice(null);
        $this->_product->setCustomOptions($this->_optionsByCode);

        return $this->_product;
    }

    /**
     * @return \Mirasvit\Giftr\Model\Priority
     */
    public function getPriority()
    {
        if (!$this->getPriorityId()) {
            return false;
        }
        if ($this->_priority === null) {
            $this->_priority = $this->priorityFactory->create()->load($this->getPriorityId());
        }

        return $this->_priority;
    }

    /**
     * @return null|string
     */
    public function getPriorityName()
    {
        return ($this->getPriority()) ? $this->getPriority()->getName() : null;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Returns formatted buy request - object, holding request received from
     * product view page with keys and options for configured product.
     * @return \Magento\Framework\DataObject
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getBuyRequest()
    {
        $option = $this->getOptionByCode('info_buyRequest');

        $initialData = $option ? $this->unserialize($option->getValue()) : [
            'registry_id' => $this->getRegistryId(),
            'product'     => $this->getProductId(),
        ];

        if ($initialData instanceof \Magento\Framework\DataObject) {
            $initialData = $initialData->getData();
        }

        $buyRequest = new \Magento\Framework\DataObject($initialData);
        $buyRequest->setQty((int)$this->getQty());

        if ($bundleOption = $buyRequest->getBundleOption()) {
            $orderOptions = $this->getProduct()->getTypeInstance(true)->getOrderOptions($this->getProduct());
            if (isset($orderOptions['bundle_options'])) {
                $availableOptions = array_keys($orderOptions['bundle_options']);
                foreach ($bundleOption as $optionId => $optionData) {
                    if (!in_array($optionId, $availableOptions)) {
                        unset($bundleOption[$optionId]);
                    }
                }
            }
            $buyRequest->setBundleOption($bundleOption);
        }

        return $buyRequest;
    }

    /**
     * Merge data to item info_buyRequest option.
     *
     * @param array|\Magento\Framework\DataObject $buyRequest
     *
     * @return \Mirasvit\Giftr\Model\Item
     */
    public function mergeBuyRequest($buyRequest)
    {
        if ($buyRequest instanceof \Magento\Framework\DataObject) {
            $buyRequest = $buyRequest->getData();
        }

        if (empty($buyRequest) || !is_array($buyRequest)) {
            return $this;
        }

        $oldBuyRequest = $this->getBuyRequest()->getData();
        $sBuyRequest   = $this->serializeData($buyRequest + $oldBuyRequest);

        $option = $this->getOptionByCode('info_buyRequest');
        if ($option) {
            $option->setValue($sBuyRequest);
        } else {
            $this->addOption([
                'code'  => 'info_buyRequest',
                'value' => $sBuyRequest,
            ]);
        }

        return $this;
    }

    /**
     * Add or Move item product to shopping cart.
     * Return true if product was successful added or exception with code
     * Return false for disabled or unvisible products
     *
     * @param \Magento\Checkout\Model\Cart $cart
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addToCart(\Magento\Checkout\Model\Cart $cart)
    {
        $product         = $this->getProduct();
        $storeId         = $this->getStoreId();
        $outOfStockItems = [];

        if ($product->getStatus() != \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
            return false;
        }

        if (!$product->isVisibleInSiteVisibility()) {
            if ($product->getStoreId() == $storeId) {
                return false;
            }
            $urlData = $this->catalogUrl->getRewriteByProductStore([$product->getId() => $storeId]);
            if (!isset($urlData[$product->getId()])) {
                return false;
            }
            $product->setUrlDataObject(new \Magento\Framework\DataObject($urlData));
            $visibility = $product->getUrlDataObject()->getVisibility();
            if (!in_array($visibility, $product->getVisibleInSiteVisibilities())) {
                return false;
            }
        }

        if (!$product->isSalable()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('This product(s) is currently out of stock'));
        }

        /*foreach ($this->getOptions() as $option) {
            if ($option->getProductId() != $this->getProductId()) {
                if (!$option->isAvailable()) {
                    if (!in_array($option->getProduct()->getName(), $outOfStockItems)) {
                        $outOfStockItems[] = $option->getProduct()->getName();
                    }
                }
            }
        }*/

        if (count($outOfStockItems)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(implode(', ', $outOfStockItems)), self::EXCEPTION_CODE_ITEM_OPTION_OUT_OF_STOCK
            );
        }

        $buyRequest = $this->getBuyRequest();

        $result = $cart->addProduct($product, $buyRequest);
        $quote  = $result->getQuote();
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getProductType() == 'grouped' && !$item->getBuyRequest()->getRegistryId()) {
                $item->addOption([
                    'code'  => 'info_buyRequest',
                    'value' => $this->serializeData(array_merge($item->getBuyRequest()->getData(), $buyRequest->getData())),
                ]);
                $quote->save();
            }
        }

        if (!$product->isVisibleInSiteVisibility()) {
            $quote->getItemByProduct($product)->setStoreId($storeId);
        }


        return true;
    }

    /**
     * Get item option by code.
     *
     * @param string $code
     *
     * @return \Mirasvit\Giftr\Model\Item\Option|null
     */
    public function getOptionByCode($code)
    {
        if (isset($this->_optionsByCode[$code]) && !$this->_optionsByCode[$code]->isDeleted()) {
            return $this->_optionsByCode[$code];
        }

        return;
    }

    /**
     * Register option code.
     *
     * @param \Mirasvit\Giftr\Model\Item\Option $option
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addOptionCode($option)
    {
        if (!isset($this->_optionsByCode[$option->getCode()])) {
            $this->_optionsByCode[$option->getCode()] = $option;
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('An item option with code %1 already exists.', $option->getCode())
            );
        }

        return $this;
    }

    /**
     * Get all item options as array with codes in array key.
     * @return array
     */
    public function getOptionsByCode()
    {
        return $this->_optionsByCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileDownloadParams()
    {
        return;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function getAddToCartUrl($params = [])
    {
        return $this->urlManager->getUrl('giftr/item/addtocart', array_merge(['item_id' => $this->getId()], $params));
    }

    /**
     * Get product URL associated with current item.
     * @return string
     */
    public function getProductUrl()
    {
        return $this->getProduct()->getProductUrl();
    }

    /**
     * Get product image url.
     *
     * @param int $size
     *
     * @return \Mirasvit\Core\Helper\Image
     */
    public function getProductImageUrl($size = 80)
    {
        return $this->coreImage->init($this->getProduct(), 'small_image', 'catalog/product')->resize($size);
    }

    /**
     * Prepare and send email for new ordered items.
     *
     * @param array $orderedItems - [item_id => ordered_qty]
     *
     * @return void
     */
    public function sendNotificationNewOrderedItems($orderedItems)
    {
        $registries = [];

        foreach ($orderedItems as $itemId => $itemQty) {
            $this->load($itemId);
            $registries[$this->getRegistryId()][$itemId] = $itemQty;
        }

        foreach ($registries as $registryId => $items) {
            $registry          = $this->registryFactory->create()->load($registryId);
            $newOrderBlockHtml = $this->widgetContext->getLayout()->createBlock('\Mirasvit\Giftr\Block\Mail\Neworder')
                ->setRegistry($registry)
                ->setOrderedItems($items)
                ->toHtml();

            $variables = [
                'bodyHtml'       => $newOrderBlockHtml,
                'customer'       => $registry->getCustomer(),
                'registrantName' => $registry->getRegistrantAndCoName(),
                'resumeUrl'      => $registry->getResumeUrl('giftr/item/manage'),
                'registryName'   => $registry->getName(),
            ];

            $this->giftrMail->sendNotificationOwnerEmail(
                $registry->getEmail(),
                $registry->getRegistrantAndCoName(),
                $variables
            );
        }
    }

    /**
     * Prepare item out of stock email.
     * @return void
     */
    public function sendNotificationOutOfStockItem()
    {
        $registry        = $this->getRegistry();
        $itemsCollection = $this->getCollection()
            ->addFieldToFilter('item_id', $this->getId());

        $itemBlockHtml = $this->widgetContext->getLayout()->createBlock('\Mirasvit\Giftr\Block\Mail\Items')
            ->setItemCollection($itemsCollection)
            ->toHtml();

        $variables = [
            'registry'       => $registry,
            'resumeUrl'      => $registry->getResumeUrl('giftr/item/manage'),
            'registrantName' => $registry->getRegistrantAndCoName(),
            'customer'       => $registry->getCustomer(),
            'items'          => $itemBlockHtml,
        ];

        $this->giftrMail->sendNotificationOutOfStockItemEmail(
            $registry->getEmail(),
            $registry->getRegistrantAndCoName(),
            $variables
        );
    }

    /**
     * @param \Magento\Framework\DataObject $buyRequest
     * @param bool|false                    $forciblySetQty
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateItem(\Magento\Framework\DataObject $buyRequest, $forciblySetQty = false)
    {
        if ($buyRequest->getStoreId()) {
            $storeId = $buyRequest->getStoreId();
        } else {
            $storeId = $this->storeManager->getStore()->getId();
            $buyRequest->setStoreId($storeId);
        }

        $product  = $this->productFactory->create()->setStoreId($storeId)
            ->load($buyRequest->getProduct());
        $products = $product->getTypeInstance(true)
            ->processconfiguration($buyRequest, $product);

        if (is_string($products)) {
            throw new \Magento\Framework\Exception\LocalizedException($products);
        } elseif (!is_array($products)) {
            $products = [$products];
        }

        foreach ($products as $item) {
            if ($item->getParentProductId()) {
                continue;
            }
            $this->_updateData($item, $buyRequest, $forciblySetQty);
        }

        return $this;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\DataObject  $buyRequest
     * @param bool|false                     $forciblySetQty
     *
     * @return $this
     */
    protected function _updateData(
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\DataObject $buyRequest,
        $forciblySetQty = false
    ) {
        $item           = null;
        $itemCollection = $this->getCollection()->addRegistryFilter($buyRequest->getRegistryId());
        foreach ($itemCollection as $_item) {
            if ($_item->representProduct($product)) {
                $item = $_item;
                break;
            }
        }

        if ($item === null) {
            if ($this->getId()) {
                foreach ($this->getOptionCollection() as $option) {
                    $option->isDeleted(true);
                    $this->_options[] = $option;
                }
            }
            $this->addData($buyRequest->getData())
                ->setOptions($product->getCustomOptions())
                ->setProductId($buyRequest->getProduct())
                ->save();
        } else {
            $qty = $forciblySetQty ? $buyRequest->getQty() : $item->getQty() + $buyRequest->getQty();
            if ($buyRequest->getNote() && !$item->getNote()) {
                $item->setNote($buyRequest->getNote());
            }

            if ($buyRequest->getPriorityId() && !$item->getPriorityId()) {
                $item->setPriorityId($buyRequest->getPriorityId());
            }

            $item->setQty($qty)
                ->save();
        }

        return $this;
    }

    /**
     * @return \Mirasvit\Giftr\Model\ResourceModel\Item\Option\Collection
     */
    public function getOptionCollection()
    {
        return $this->itemOptionCollectionFactory->create()->addFieldToFilter('item_id', $this->getId());
    }

    /**
     * Check product representation in item.
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return bool
     */
    public function representProduct($product)
    {
        $itemProduct = $this->getProduct();
        if ($itemProduct->getId() != $product->getId()) {
            return false;
        }

        $itemOptions    = $this->getOptionsByCode();
        $productOptions = $product->getCustomOptions();

        if (!$this->compareOptions($itemOptions, $productOptions)) {
            return false;
        }

        if (!$this->compareOptions($productOptions, $itemOptions)) {
            return false;
        }

        return true;
    }

    /**
     * Check if two options array are identical
     * First options array is prerogative
     * Second options array checked against first one.
     *
     * @param array $options
     * @param array $comparedOptions
     *
     * @return bool
     */
    public function compareOptions($options, $comparedOptions)
    {
        foreach ($options as $option) {
            $code = $option->getCode();
            if (in_array($code, $this->_notRepresentOptions)) {
                continue;
            }
            if (!isset($comparedOptions[$code]) ||
                ($comparedOptions[$code]->getValue() === null) ||
                $comparedOptions[$code]->getValue() != $option->getValue()
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Set item options.
     *
     * @param array $options
     *
     * @return \Mirasvit\Giftr\Model\Item
     */
    public function setOptions($options)
    {
        foreach ($options as $option) {
            $this->addOption($option);
        }

        return $this;
    }

    /**
     * Add item option.
     *
     * @param \Mirasvit\Giftr\Model\Item\Option|array $option
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addOption($option)
    {
        if (is_array($option)) {
            $this->setDataChanges(true);
            $option = $this->itemOptionFactory->create()->setData($option)
                ->setItem($this);
        } elseif ($option instanceof \Mirasvit\Giftr\Model\Item\Option) {
            $option->setItem($this);
        } elseif ($option instanceof \Magento\Framework\DataObject) {
            $option = $this->itemOptionFactory->create()
                ->setData($option->getData())
                ->setProduct($option->getProduct())
                ->setItem($this);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid item option format.'));
        }

        $exOption = $this->getOptionByCode($option->getCode());
        if ($exOption) {
            $exOption->addData($option->getData());
        } else {
            $this->_addOptionCode($option);
            $this->_options[] = $option;
        }

        //$this->_options[] = $option;

        return $this;
    }

    /**
     * Is product salable.
     * @return bool|int
     */
    public function isSalable()
    {
        return $this->stockItemFactory->create()->load($this->getProductId(), 'product_id')->getIsInStock();
    }

    /**
     * Save item options.
     * @return \Mirasvit\Giftr\Model\Item
     */
    protected function _saveItemOptions()
    {
        foreach ($this->_options as $index => $option) {
            if ($option->isDeleted()) {
                $option->delete();
                unset($this->_options[$index]);
                unset($this->_optionsByCode[$option->getCode()]);
            } else {
                $option->save();
            }
        }

        $this->_flagOptionsSaved = true; // Report to watchers that options were saved

        return $this;
    }

    /**
     * Save model plus its options
     * Ensures saving options in case when resource model was not changed.
     * @return void
     */
    public function save()
    {
        $hasDataChanges          = $this->hasDataChanges();
        $this->_flagOptionsSaved = false;

        parent::save();
        if ($hasDataChanges && !$this->_flagOptionsSaved) {
            $this->_saveItemOptions();
        }
    }

    /**
     * Save item options after item saved.
     * @return \Mirasvit\Giftr\Model\Item
     */
    public function afterSave()
    {
        $this->_saveItemOptions();

        return parent::afterSave();
    }

    /**
     * Set buy request - object, holding request received from
     * product view page with keys and options for configured product.
     *
     * @param \Magento\Framework\DataObject $buyRequest
     *
     * @return \Mirasvit\Giftr\Model\Item
     */
    public function setBuyRequest($buyRequest)
    {
        $buyRequest->setId($this->getId());

        $_buyRequest = \Zend_Json::encode($buyRequest->getData());
        $this->setData('buy_request', $_buyRequest);

        return $this;
    }

    /**
     * Loads item together with its options (default load() method doesn't load options).
     * If we need to load only some of options, then option code or array of option codes
     * can be provided in $optionsFilter.
     *
     * @param int               $id
     * @param null|string|array $optionsFilter
     *
     * @return \Mirasvit\Giftr\Model\Item
     */
    public function loadWithOptions($id, $optionsFilter = null)
    {
        $this->load($id);
        if (!$this->getId()) {
            return $this;
        }

        $options = $this->itemOptionCollectionFactory->create()
            ->addItemFilter($this);

        if ($optionsFilter) {
            $options->addFieldToFilter('code', $optionsFilter);
        }

        $this->setOptions($options->getOptionsByItem($this));

        return $this;
    }

    /**
     * Change purchased giftr item qty.
     *
     * @param \Magento\Sales\Model\Order $order
     *
     * @return array $newOrderedItems - keys = ordered item ids, values = their qty
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function changeItemQty($order)
    {
        $newOrderedItems  = [];
        $status           = $order->getStatus();
        $origStatus       = $order->getOrigData('status');
        $invoicedStatuses = $this->configFactory->create()->getGeneralOrderInvoicedStatus();
        $receivedStatuses = $this->configFactory->create()->getGeneralOrderReceivedStatus();
        $canceledStatuses = $this->configFactory->create()->getGeneralOrderCanceledStatus();

        if (in_array($status, array_merge($invoicedStatuses, $receivedStatuses, $canceledStatuses))) {
            $orderItems = $order->getItemsCollection();

            /** @var \Magento\Sales\Model\Order\Item $orderItem */
            foreach ($orderItems as $orderItem) {

                if ($itemId = $orderItem->getBuyRequest()->getItemId()) {
                    /** @var \Mirasvit\Giftr\Model\Item $item */
                    $item = $this->itemFactory->create()->loadWithOptions($itemId);

                    if (
                        $item->getId()
                        && (
                            $orderItem->getProductId() == $item->getProductId()
                            || ($orderItem->getParentItem() && $orderItem->getParentItem()->getProductId() == $item->getProductId())
                        )
                    ) {
                        // Increase item qty
                        if (in_array($status, $invoicedStatuses)) {
                            // skip if we already changed QTY for this order
                            if ($item->getOptionByCode('order_id_invoiced')
                                && $item->getOptionByCode('order_id_invoiced')->getValue() == $order->getId()) {
                                continue;
                            }

                            $item->setQtyOrdered($item->getQtyOrdered() + $orderItem->getQtyOrdered());
                            $item->addOption([
                                'product_id' => $item->getProductId(),
                                'code'       => 'order_id_invoiced',
                                'value'      => $order->getId(),
                            ]);

                            // Collect ordered items with their qty
                            $newOrderedItems[$item->getId()] = (int)$orderItem->getQtyOrdered();
                        }

                        if (in_array($status, $receivedStatuses)) {
                            // skip if we already changed QTY for this order
                            if ($item->getOptionByCode('order_id_received')
                                && $item->getOptionByCode('order_id_received')->getValue() == $order->getId()) {
                                continue;
                            }

                            $item->setQtyReceived($item->getQtyReceived() + $orderItem->getQtyOrdered());
                            $item->addOption([
                                'product_id' => $item->getProductId(),
                                'code'       => 'order_id_received',
                                'value'      => $order->getId(),
                            ]);
                        }

                        // Decrease item qty
                        // todo: decrease on QtyRefunded(but it is empty) instead of QtyOrdered
                        if (in_array($status, $canceledStatuses)) {

                            if ($item->getQtyOrdered() > 0
                                && in_array($origStatus, array_merge($invoicedStatuses, $receivedStatuses))
                            ) {
                                $item->setQtyOrdered($item->getQtyOrdered() - $orderItem->getQtyOrdered());
                            }

                            if ($item->getQtyReceived() > 0 && in_array($origStatus, $receivedStatuses)) {
                                $item->setQtyReceived($item->getQtyReceived() - $orderItem->getQtyOrdered());
                            }
                        }

                        if ($item->dataHasChangedFor('qty_ordered') || $item->dataHasChangedFor('qty_received')) {
                            $item->save();
                        }
                    }
                }
            }
        }

        return $newOrderedItems;
    }

    /**
     * Check item order status, change qty of ordered/received items
     * and send email to giftr owner about new ordered items.
     *
     * @param \Magento\Sales\Model\Order $order
     *
     * @return void
     */
    public function updateItemQty(\Magento\Sales\Model\Order $order)
    {
        $orderedItems = $this->changeItemQty($order);
        if (!empty($orderedItems)) {
            // Send email: You have new purchased item
            $this->sendNotificationNewOrderedItems($orderedItems);
        }
    }

    /**
     * Unserialize data using serialization method depending on Magento version.
     *
     * @param string $value
     *
     * @return mixed
     * @throws \Zend_Json_Exception
     */
    private function unserialize($value)
    {
        $result = null;
        if (version_compare($this->productMetaData->getVersion(), '2.2.0', '<')) {
            $result = unserialize($value);
        } else {
            $result = \Zend_Json::decode($value);
        }

        return $result;
    }

    /**
     * Serialize data using serialization method depending on Magento version.
     *
     * @param array $data
     *
     * @return null|string
     */
    private function serializeData($data)
    {
        $result = null;
        if (version_compare($this->productMetaData->getVersion(), '2.2.0', '<')) {
            $result = serialize($data);
        } else {
            $result = \Zend_Json::encode($data);
        }

        return $result;
    }

    /************************/
}
