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

/**
 * @method string getCode()
 * @method int getIsSystem()
 */
class Section extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
    const SECTION_GENERAL       = 'general';
    const SECTION_REGISTRANT    = 'registrant';
    const SECTION_CO_REGISTRANT = 'co_registrant';
    const SECTION_SHIPPING      = 'shipping';

    const CACHE_TAG             = 'giftr_section';

    /**
     * @var string
     */
    protected $_cacheTag        = 'giftr_section';

    /**
     * @var string
     */
    protected $_eventPrefix     = 'giftr_section';

    /**
     * @var array
     */
    private $optionalSections   = [
        self::SECTION_REGISTRANT,
        self::SECTION_CO_REGISTRANT
    ];

    /**
     * @var \Mirasvit\Giftr\Model\ResourceModel\Field\CollectionFactory
     */
    protected $fieldCollectionFactory;

    /**
     * @var \Mirasvit\Giftr\Model\FieldFactory
     */
    protected $fieldFactory;

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
     * @param \Mirasvit\Giftr\Model\ResourceModel\Field\CollectionFactory $fieldCollectionFactory
     * @param \Mirasvit\Giftr\Model\FieldFactory                          $fieldFactory
     * @param \Mirasvit\Giftr\Helper\Storeview                            $giftrStoreview
     * @param \Magento\Framework\Model\Context                            $context
     * @param \Magento\Framework\Registry                                 $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource     $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb               $resourceCollection
     * @param array                                                       $data
     */
    public function __construct(
        \Mirasvit\Giftr\Model\ResourceModel\Field\CollectionFactory $fieldCollectionFactory,
        \Mirasvit\Giftr\Model\FieldFactory $fieldFactory,
        \Mirasvit\Giftr\Helper\Storeview $giftrStoreview,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->fieldCollectionFactory = $fieldCollectionFactory;
        $this->fieldFactory = $fieldFactory;
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
        $this->_init('Mirasvit\Giftr\Model\ResourceModel\Section');
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
     * @return null|string
     */
    public function getName()
    {
        return $this->giftrStoreview->getStoreViewValue($this, 'name');
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

    /**
     * Get field collection associated with this section.
     *
     * @return \Mirasvit\Giftr\Model\ResourceModel\Field\Collection
     */
    public function getFieldCollection()
    {
        return $this->fieldCollectionFactory->create()
            ->addActiveFilter()
            ->addFieldToFilter('field_id', ['in' => $this->getFieldIds()])
            ->sortByOrder();
    }

    /**
     * Get associated collection of fields (in default relation)
     * If $addCustomFields = true, add to collection only related fields and non-system fields
     *
     * @param bool $addCustomFields
     * @return \Mirasvit\Giftr\Model\ResourceModel\Field\Collection
     */
    public function getRelatedFieldCollection($addCustomFields = false)
    {
        $fieldCollection = $this->fieldCollectionFactory->create()
            ->addActiveFilter()
            ->sortByOrder();

        if ($addCustomFields) {
            $fieldCollection->addFieldToFilter(
                ['code', 'is_system'],
                [
                    ['in' => $this->fieldFactory->create()->getFieldCodesForSection($this->getCode())],
                    ['eq' => 0]
                ]
            );
        } else {
            $fieldCollection->addFieldToFilter('code', [
                'in' => $this->fieldFactory->create()->getFieldCodesForSection($this->getCode())
            ]);
        }

        return $fieldCollection;
    }

    /**
     * Return collection of required sections only
     *
     * @return \Mirasvit\Giftr\Model\ResourceModel\Section\Collection
     */
    public function getRequiredCollection()
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('is_system', true)
            ->addFieldToFilter('code', ['nin' => $this->optionalSections]);

        return $collection;
    }

    /**
     * Mark option as disabled in dropdown or not
     * System sections disabled by default(except co_registrant section)
     *
     * @return bool
     */
    public function isOptionDisabled()
    {
        return (bool) $this->getIsSystem() && !in_array($this->getCode(), $this->optionalSections);
    }

    /**
     * Get array with names of inputs that are not allowed for changing in section
     *
     * @return array
     */
    public function getForbiddenFieldNames()
    {
        $forbiddenFieldNames = [];
        if (!in_array($this->getCode(), $this->optionalSections) && $this->getIsSystem()) {
                $forbiddenFieldNames[] = 'is_active';
        }

        return $forbiddenFieldNames;
    }

    /**
     * @return array
     */
    public function getOptionalSections()
    {
        return $this->optionalSections;
    }
    /************************/
}
