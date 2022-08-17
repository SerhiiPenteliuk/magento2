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
use Magento\Framework\Stdlib\DateTime;
use Mirasvit\Giftr\Api\Repository\RegistryRepositoryInterface;

/**
 * @method int getCustomerId()
 * @method int getShippingAddressId()
 * @method int getWebsiteId()
 * @method int getTypeId()
 * @method int getUid()
 * @method int getIsPublic()
 * @method string getEventAt()
 * @method $this setEventAt($date)
 * @method $this setCustomerId($id)
 * @method $this setWebsiteId($id)
 * @method $this setValues($string)
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Registry extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
    /**
     * Length of registry unique ID
     */
    const UID_LENGTH = 5;

    /**
     * Registry visibility
     */
    const VISIBILITY_PUBLIC = 1;
    const VISIBILITY_PRIVATE = 0;

    const CACHE_TAG = 'giftr_registry';


    /**
     * @var null|\Mirasvit\Giftr\Model\Type
     */
    protected $_type = null;

    /**
     * @var null|\Magento\Store\Model\Website
     */
    protected $_website = null;

    /**
     * @var null|\Magento\Customer\Model\Customer
     */
    protected $_customer = null;

    /**
     * @var null|\Magento\Customer\Model\Address
     */
    protected $_shipping = null;

    /**
     * @var string
     */
    protected $_cacheTag = 'giftr_registry';

    /**
     * @var string
     */
    protected $_eventPrefix = 'giftr_registry';

    /**
     * @var \Mirasvit\Giftr\Model\TypeFactory
     */
    protected $typeFactory;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $websiteFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $addressFactory;

    /**
     * @var \Mirasvit\Giftr\Model\ResourceModel\Field\CollectionFactory
     */
    protected $fieldCollectionFactory;

    /**
     * @var \Mirasvit\Giftr\Model\ResourceModel\Item\CollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * @var \Mirasvit\Giftr\Model\ResourceModel\Purchase\CollectionFactory
     */
    protected $purchaseCollectionFactory;

    /**
     * @var \Mirasvit\Giftr\Model\Config
     */
    protected $config;

    /**
     * @var \Mirasvit\Giftr\Helper\Storeview
     */
    protected $giftrStoreview;

    /**
     * @var \Mirasvit\Core\Helper\Image
     */
    protected $coreImage;

    /**
     * @var \Mirasvit\Giftr\Helper\Data
     */
    protected $giftrData;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Image\Factory
     */
    protected $imageFactory;

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
     * @var RegistryRepositoryInterface
     */
    private $registryRepository;
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     * @param RegistryRepositoryInterface                                    $registryRepository
     * @param \Magento\Catalog\Helper\Image                                  $imageHelper
     * @param \Mirasvit\Giftr\Model\TypeFactory                              $typeFactory
     * @param \Magento\Store\Model\WebsiteFactory                            $websiteFactory
     * @param \Magento\Customer\Model\CustomerFactory                        $customerFactory
     * @param \Magento\Customer\Model\AddressFactory                         $addressFactory
     * @param \Mirasvit\Giftr\Model\ResourceModel\Field\CollectionFactory    $fieldCollectionFactory
     * @param \Mirasvit\Giftr\Model\ResourceModel\Item\CollectionFactory     $itemCollectionFactory
     * @param \Mirasvit\Giftr\Model\ResourceModel\Purchase\CollectionFactory $purchaseCollectionFactory
     * @param \Mirasvit\Giftr\Model\Config                                   $config
     * @param \Mirasvit\Giftr\Helper\Storeview                               $giftrStoreview
     * @param \Mirasvit\Core\Helper\Image                                    $coreImage
     * @param \Mirasvit\Giftr\Helper\Data                                    $giftrData
     * @param \Magento\Framework\UrlInterface                                $urlManager
     * @param \Magento\Store\Model\StoreManagerInterface                     $storeManager
     * @param \Magento\Framework\Image\Factory                               $imageFactory
     * @param \Magento\Framework\Model\Context                               $context
     * @param \Magento\Framework\Registry                                    $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource        $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb                  $resourceCollection
     * @param array                                                          $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        RegistryRepositoryInterface $registryRepository,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Mirasvit\Giftr\Model\TypeFactory $typeFactory,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Mirasvit\Giftr\Model\ResourceModel\Field\CollectionFactory $fieldCollectionFactory,
        \Mirasvit\Giftr\Model\ResourceModel\Item\CollectionFactory $itemCollectionFactory,
        \Mirasvit\Giftr\Model\ResourceModel\Purchase\CollectionFactory $purchaseCollectionFactory,
        \Mirasvit\Giftr\Model\Config $config,
        \Mirasvit\Giftr\Helper\Storeview $giftrStoreview,
        \Mirasvit\Core\Helper\Image $coreImage,
        \Mirasvit\Giftr\Helper\Data $giftrData,
        \Magento\Framework\UrlInterface $urlManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Image\Factory $imageFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->registryRepository = $registryRepository;
        $this->imageHelper = $imageHelper;
        $this->typeFactory = $typeFactory;
        $this->websiteFactory = $websiteFactory;
        $this->customerFactory = $customerFactory;
        $this->addressFactory = $addressFactory;
        $this->fieldCollectionFactory = $fieldCollectionFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->purchaseCollectionFactory = $purchaseCollectionFactory;
        $this->config = $config;
        $this->giftrStoreview = $giftrStoreview;
        $this->coreImage = $coreImage;
        $this->giftrData = $giftrData;
        $this->urlManager = $urlManager;
        $this->storeManager = $storeManager;
        $this->imageFactory = $imageFactory;
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
        $this->_init('Mirasvit\Giftr\Model\ResourceModel\Registry');
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
     * @param bool|false $emptyOption
     * @return array
     */
    public function toOptionArray($emptyOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption);
    }

    /**
     * @return bool|\Mirasvit\Giftr\Model\Type
     */
    public function getType()
    {
        if (!$this->getTypeId()) {
            return false;
        }
        if ($this->_type === null) {
            $this->_type = $this->typeFactory->create()->load($this->getTypeId());
        }

        return $this->_type;
    }

    /**
     * @return bool|\Magento\Store\Model\Website
     */
    public function getWebsite()
    {
        if (!$this->getWebsiteId()) {
            return false;
        }
        if ($this->_website === null) {
            $this->_website = $this->websiteFactory->create()->load($this->getWebsiteId());
        }

        return $this->_website;
    }

    /**
     * Retrieve registry store ID.
     *
     * @return int $storeId
     */
    public function getStoreId()
    {
        if (!$this->getData('store_id')) {
            $this->setStoreId($this->storeManager->getStore()->getId());
        }

        return $this->getData('store_id');
    }

    /**
     * @return bool|\Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        if (!$this->getCustomerId()) {
            return false;
        }
        if ($this->_customer === null) {
            $this->_customer = $this->customerFactory->create()->load($this->getCustomerId());
        }

        return $this->_customer;
    }

    /**
     * @return bool|\Magento\Customer\Model\Address
     */
    public function getShippingAddress()
    {
        if (!$this->getShippingAddressId()) {
            return false;
        }
        if ($this->_shipping === null) {
            $this->_shipping = $this->addressFactory->create()->load($this->getShippingAddressId());
        }

        return $this->_shipping;
    }

    /**
     * Return array of values defined in additional fields.
     *
     * @return array $result - ['field_code' => 'field_value']
     */
    public function getValues()
    {
        $result = [];
        $values = $this->giftrStoreview->unserialize($this->getData('values'));
        if ($values && is_array($values)) {
            // Change values retrieving logic
            $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($values));
            if ($iterator->valid() && $iterator->key()) {
                $result = iterator_to_array($iterator);
            }
        }

        return $result;
    }

    /**
     * Convert array of additional fields associated with current registry into field collection.
     *
     * @return \Mirasvit\Giftr\Model\ResourceModel\Field\Collection
     */
    public function getFieldCollection()
    {
        $values = $this->getValues();

        foreach ($values as $index => $value) {
            if (!$value) {
                unset($values[$index]);
            }
        }

        $fields = $this->fieldCollectionFactory->create()
            ->addFieldToFilter('code', ['in' => array_keys($values)])
            ->addFieldToFilter('type', ['neq' => 'checkbox'])
            ->setSortOrder();

        return $fields;
    }

    /**
     * Retrieve value for particular field from array of values by field code.
     *
     * @param string $key field code
     *
     * @return null|string
     */
    public function getValueByCode($key)
    {
        $value = null;
        $values = $this->getValues();
        if ($values && isset($values[$key])) {
            $value = $values[$key];
        }

        return $value;
    }

    /**
     * Save/update registry data.
     *
     * @param array $data
     *
     * @return $this
     * @throws \Exception
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function updateRegistryData(array $data)
    {
        $this->registryRepository->save($this, $data);

        return $this;
    }

    /**
     * @return \Mirasvit\Giftr\Model\ResourceModel\Item\Collection
     */
    public function getItemCollection()
    {
        $collection = $this->itemCollectionFactory->create()
            ->addFieldToFilter('main_table.registry_id', $this->getId());

        return $collection;
    }

    /**
     * Is registry has salable item(s).
     *
     * @return bool
     */
    public function isSalable()
    {
        foreach ($this->getItemCollection() as $item) {
            if ($item->getProduct()->getIsSalable()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get Gift Registry image file name "media/giftr/{image_file_name}.ext".
     *
     * @return string
     */
    public function getImage()
    {
        return $this->getData('image') ? $this->getData('image') : $this->config->getPlaceholder();
    }

    /**
     * Get image url, if params specified && image orig size higher then params - resize image.
     *
     * @param null|int $width
     * @param null|int $height
     *
     * @return \Mirasvit\Core\Helper\Image $image
     */
    public function getImageUrl($width = null, $height = null)
    {
        $imageExists = file_exists($this->config->getBaseMediaPath().'/'.$this->getImage()) && $this->getImage();
        if (!$imageExists) {
            $image = $this->imageHelper;
        } else {
            $image = $this->coreImage->init($this, 'image', Config::IMAGE_FOLDER_NAME, $this->getImage());
        }

        if ($width !== null) {
            $image->resize($width, $height);
        }

        return $imageExists ? $image : $this->getType()->getImageUrl('event_image', $width, $height);
    }

    /**
     * Whether the registry contains info about the Co-Registrant.
     *
     * @return bool
     */
    public function hasCoRegistrant()
    {
        $result = false;
        $coData = ['co_firstname', 'co_middlename', 'co_lastname', 'co_email'];
        foreach ($coData as $key) {
            if ($this->hasData($key) && $this->getData($key) != '') {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Return section collection associated with this registry.
     *
     * @param bool $includeSystem
     * @return null|\Mirasvit\Giftr\Model\ResourceModel\Section\Collection
     */
    public function getSectionCollection($includeSystem = true)
    {
        $collection = null;
        if ($this->getType()) {
            $collection = $this->getType()->getSectionCollection($includeSystem);
        }

        return $collection;
    }

    /**
     * Get url to registry guest view page.
     * If $customStoreId is not null, load URL of specified store
     *
     * @param int|null $customStoreId
     *
     * @return string
     */
    public function getViewUrl($customStoreId = null)
    {
        $storeId = $this->getStoreId();
        if ($customStoreId !== null) {
            $storeId = $customStoreId;
        }

        return $this->storeManager->getStore($storeId)->getUrl('giftr/registry/view', ['uid' => $this->getUid()]);
    }

    /**
     * Retrieve registry by UID.
     *
     * @param string $uid
     *
     * @return $this|bool
     */
    public function loadByUid($uid)
    {
        $this->load($uid, 'uid');
        if (!$this->getId()) {
            return false;
        }

        return $this;
    }

    /**
     * Is registry customer is current user.
     *
     * @param int $customerId
     *
     * @return bool
     */
    public function isOwner($customerId)
    {
        return $customerId == $this->getCustomerId();
    }

    /**
     * Get purchases associated with this registry.
     *
     * @return \Mirasvit\Giftr\Model\ResourceModel\Purchase\Collection
     */
    public function getPurchaseCollection()
    {
        $collection = $this->purchaseCollectionFactory->create()
            ->addFieldToFilter('registry_id', $this->getId());

        return $collection;
    }

    /**
     * Is this registry has orders.
     *
     * @return bool
     */
    public function hasOrders()
    {
        return $this->getPurchaseCollection()
            ->addFieldToFilter('order_id', ['notnull' => true])
            ->getSize() > 0;
    }

    /**
     * Get array of order ids associated with this registry.
     *
     * @return array
     */
    public function getOrderIds()
    {
        return $this->getPurchaseCollection()
            ->addFieldToFilter('order_id', ['notnull' => true])
            ->getColumnValues('order_id');
    }

    /**
     * Get concatenated registrant and co-registrant names
     * with middlename if config allows.
     *
     * @return string
     */
    public function getRegistrantAndCoName()
    {
        $config = $this->config;
        $middlename = ($config->getGeneralIsShowMiddlename() && $this->getMiddlename())
            ? ' '.$this->getMiddlename().' '
            : ' ';
        $coMiddlename = ($config->getGeneralIsShowMiddlename() && $this->getCoMiddlename())
            ? ' '.$this->getCoMiddlename().' '
            : ' ';

        $displayName = sprintf('%s%s%s', $this->getFirstname(), $middlename, $this->getLastname());
        if ($this->hasCoRegistrant()) {
            $displayName .= sprintf(
                ' and %s%s%s',
                $this->getCoFirstname(),
                $coMiddlename,
                $this->getCoLastname()
            );
        }

        return $displayName;
    }

    /**
     * Get md5 encoded string for identifying registry.
     *
     * @return string
     */
    public function getUidMd5()
    {
        return md5($this->getUid().$this->getCustomerId().$this->getId());
    }

    /**
     * Get resumed url of customer session for particular path.
     *
     * @param string $path - path to controller action
     *
     * @return string - url
     */
    public function getResumeUrl($path = 'giftr/registry/view')
    {
        $store = $this->storeManager->getStore($this->getWebsite()->getDefaultStore()->getId());

        return $store->getUrl('giftr/giftr/resume', [
            'code' => $this->getUidMd5(),
            'path' => $this->giftrData->base64UrlEncode($path),
            'id' => $this->getId()
        ]);
    }

    /**
     * Check whether the registry already contains this product.
     *
     * @param int $productId
     *
     * @return bool
     */
    public function isProductFromRegistry($productId)
    {
        $items = $this->getItemCollection()
            ->addFieldToFilter('product_id', $productId);

        return (bool) $items->count();
    }

    /**
     * Load registry by order ID.
     *
     * @param int $orderId
     *
     * @return $this
     */
    public function loadByOrder($orderId)
    {
        $purchase = $this->purchaseCollectionFactory->create()
            ->addFieldToFilter('order_id', $orderId)
            ->getFirstItem();

        if ($purchase->getId()) {
            $this->load($purchase->getRegistryId());
        }

        return $this;
    }

    /**
     * Get registrant firstname, if name is not specified return customer's firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->getData('firstname')
            ? $this->getData('firstname')
            : ($this->getData('lastname') ? '' : $this->getCustomer()->getFirstname());
    }

    /**
     * Get registrant lastname, if lastname is not specified return customer's lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->getData('lastname')
            ? $this->getData('lastname')
            : ($this->getData('firstname') ? '' : $this->getCustomer()->getLastname());
    }

    /**
     * Get registrant email, if email is not specified return customer's email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getData('email') ? $this->getData('email') : $this->getCustomer()->getEmail();
    }

    /**
     * @param string $attribute
     * @return array|string|string[]
     */
    public function getSafe($attribute)
    {
        return $this->escape($this->getData($attribute));
    }

    /**
     * @param array $data
     * @return array|string|string[]
     */
    public function escape($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (!is_array($value)) {
                    $data[$key] = strip_tags(addslashes($value));
                }
            }
        } elseif (is_string($data)) {
            $data = strip_tags(addslashes($data));
        } else {
            $data = strip_tags($data);
        }

        $data = str_replace('\\', '', $data);

        return $data;
    }
}
