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
use Mirasvit\Giftr\Model\Config as Config;

class Type extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'giftr_type';

    /**
     * @var string
     */
    protected $_cacheTag = 'giftr_type';

    /**
     * @var string
     */
    protected $_eventPrefix = 'giftr_type';

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
     * @var \Mirasvit\Giftr\Model\ResourceModel\Section\CollectionFactory
     */
    protected $sectionCollectionFactory;

    /**
     * @var \Mirasvit\Giftr\Helper\Storeview
     */
    protected $giftrStoreview;

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
     * @var \Mirasvit\Giftr\Model\Config
     */
    protected $config;

    /**
     * @var \Mirasvit\Giftr\Model\Config
     */
    protected $coreImage;

    /**
     * @var \Mirasvit\Giftr\Model\Config
     */
    protected $imageHelper;

    /**
     * @var null
     */
    protected $images = ['getEventIcon'=>'event_icon','getEventImage'=>'event_image'];

    /**
     * @param \Mirasvit\Giftr\Model\Config $config
     * @param \Mirasvit\Core\Helper\Image $coreImage
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Mirasvit\Giftr\Model\ResourceModel\Section\CollectionFactory $sectionCollectionFactory
     * @param \Mirasvit\Giftr\Helper\Storeview $giftrStoreview
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        Config $config,
        \Mirasvit\Core\Helper\Image $coreImage,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Mirasvit\Giftr\Model\ResourceModel\Section\CollectionFactory $sectionCollectionFactory,
        \Mirasvit\Giftr\Helper\Storeview $giftrStoreview,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->config = $config;
        $this->coreImage = $coreImage;
        $this->imageHelper = $imageHelper;
        $this->sectionCollectionFactory = $sectionCollectionFactory;
        $this->giftrStoreview = $giftrStoreview;
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
        $this->_init('Mirasvit\Giftr\Model\ResourceModel\Type');
    }

    /**
     * @param bool|false $emptyOption
     * @param bool $active
     * @param bool $selectAll
     * @return array
     */
    public function toOptionArray($emptyOption = false, $active = false, $selectAll = false)
    {
        if ($active) {
            return $this->getCollection()->addIsActiveFilter()->toOptionArray($emptyOption, $selectAll);
        } else {
            return $this->getCollection()->toOptionArray($emptyOption, $selectAll);
        }
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getInactiveTypes()
    {
        return $this->getCollection()->addFieldToFilter('is_active', 0);
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->escape($this->giftrStoreview->getStoreViewValue($this, 'name'));
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setName($value)
    {
        $this->giftrStoreview->setStoreViewValue($this, 'name', $value);

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function addData(array $data)
    {
        if (isset($data['name']) && strpos($data['name'], 'a:') !== 0) {
            $this->setName($data['name']);
            unset($data['name']);
        }

        return parent::addData($data);
    }

    /*public function getSectionIds()
    {
        return array_merge(
            (array)$this->getData('section_ids'),
            $this->sectionCollectionFactory->create()
                ->addFieldToFilter('is_system', 1)
                ->getAllIds()
        );
    }*/

    /**
     * Get section collection for this type.
     *
     * @param bool $includeSystem
     * @return \Mirasvit\Giftr\Model\ResourceModel\Section\Collection
     */
    public function getSectionCollection($includeSystem = true)
    {
        $sectionCollection = $this->sectionCollectionFactory->create()
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('section_id', ['in' => $this->getSectionIds()])
            ->setOrder('sort_order', 'asc');

        if (!$includeSystem) {
            $sectionCollection->addFieldToFilter('is_system', 0);
        }

        return $sectionCollection;
    }

    /**
     * Is section allowed(selected) for this type
     *
     * @param string $sectionCode
     * @return bool
     */
    public function isSectionAllowed($sectionCode)
    {
        $allowed = false;
        foreach ($this->getSectionCollection() as $section) {
            if ($section->getCode() == $sectionCode) {
                $allowed = true;
                break;
            }
        }

        return $allowed;
    }

    /**
     * @param string $field
     * @param null $width
     * @param null $height
     * @return \Magento\Catalog\Helper\Image|\Mirasvit\Core\Helper\Image|\Mirasvit\Giftr\Model\Config|string
     */
    public function getImageUrl($field, $width = null, $height = null)
    {
        $getImage = $this->getData($field);
        $imageExists = file_exists($this->config->getBaseMediaPath().'/'.$getImage) && $getImage;

        if (!$imageExists) {
            $image = $this->imageHelper;
        } else {
            $image = $this->coreImage->init($this, 'image', Config::IMAGE_FOLDER_NAME, $getImage);
        }

        if ($width !== null) {
            $image->resize($width, $height);
        }

        return $imageExists ? $image : $image->getDefaultPlaceholderUrl('small_image');
    }

    /**
     * @param array $data
     * @return array|string
     */
    public function escape($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = strip_tags(addslashes($value));
            }
        } elseif (is_string($data)) {
            $data = strip_tags(addslashes($data));
        } else {
            $data = strip_tags($data);
        }

        return $data;
    }
}
