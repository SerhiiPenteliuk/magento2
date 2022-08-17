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
use Magento\Framework\ObjectManagerInterface;
use Mirasvit\Giftr\Api\Service\Field\ValueFilterInterface;

/**
 * @method int getIsRequired()
 * @method int getIsSystem()
 * @method string getType()
 */
class Field extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'giftr_field';

    /**
     * @var string
     */
    protected $_cacheTag = 'giftr_field';

    /**
     * @var string
     */
    protected $_eventPrefix = 'giftr_field';

    /**
     * @var array
     */
    private $forbiddenFieldNames = [
        'code',
        'type',
        'values',
        'is_required',
        'is_system'
    ];

    /**
     * @var array
     */
    private $fieldToSectionRelation = [
        Section::SECTION_GENERAL => [
            'name',
            'description',
            'location',
            'event_at',
            'is_active',
            'is_public',
            'image'
        ],
        Section::SECTION_REGISTRANT => [
            'firstname',
            'middlename',
            'lastname',
            'email'
        ],
        Section::SECTION_CO_REGISTRANT => [
            'co_firstname',
            'co_middlename',
            'co_lastname',
            'co_email'
        ]
    ];

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
     * @var array
     */
    protected $filters = [
        'date' => \Mirasvit\Giftr\Service\Field\DateFilter::class
    ];
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param \Mirasvit\Giftr\Helper\Storeview $giftrStoreview
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        \Mirasvit\Giftr\Helper\Storeview $giftrStoreview,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->objectManager = $objectManager;
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
        $this->_init('Mirasvit\Giftr\Model\ResourceModel\Field');
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
     * @return null|string
     */
    public function getDescription()
    {
        return $this->giftrStoreview->getStoreViewValue($this, 'description');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setDescription($value)
    {
        $this->giftrStoreview->setStoreViewValue($this, 'description', $value);

        return $this;
    }

    /**
     * @return null|string
     */
    public function getValues()
    {
        return $this->giftrStoreview->getStoreViewValue($this, 'values');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setValues($value)
    {
        $this->giftrStoreview->setStoreViewValue($this, 'values', $value);

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

        if (isset($data['description']) && strpos($data['description'], 'a:') !== 0) {
            $this->setDescription($data['description']);
            unset($data['description']);
        }

        if (isset($data['values']) && strpos($data['values'], 'a:') !== 0) {
            $this->setValues($data['values']);
            unset($data['values']);
        }

        return parent::addData($data);
    }

    /**
     * @return array
     */
    public function getValuesAsOptions()
    {
        $options = [];
        $values = explode("\n", $this->getValues());

        foreach ($values as $value) {
            $option = explode('|', $value);
            if (is_array($option)) {
                $options[] = [
                    'value' => isset($option[0]) ? trim($option[0]) : '',
                    'label' => isset($option[1]) ? __(trim($option[1])) : '',
                ];
            }
        }

        return $options;
    }

    /**
     * @return string
     */
    public function getFormattedName()
    {
        return 'section['.$this->getSectionId().'][field]['.$this->getId().']';
    }

    /**
     * Get dropdown option name according to option value saved in registry.
     *
     * @param string $value
     *
     * @return null|string
     */
    public function getOptionByValue($value)
    {
        $name = null;
        $options = explode("\n", $this->getValues());
        foreach ($options as $option) {
            $option = explode('|', $option);
            if (trim($option[0]) == $value) {
                $name = $option[1];
            }
        }

        return $name;
    }

    /**
     * Get array with names of inputs that are not allowed for changing in field
     *
     * @return array
     */
    public function getForbiddenFieldNames()
    {
        $forbiddenFieldNames = [];
        if ($this->getIsSystem()) {
            $forbiddenFieldNames = $this->forbiddenFieldNames;
            if ($this->getIsRequired()) {
                $forbiddenFieldNames[] = 'is_active';
            }
        }

        return $forbiddenFieldNames;
    }

    /**
     * Return field codes associated with the section
     * Only for system sections and fields
     *
     * @param string $sectionCode
     * @return array
     */
    public function getFieldCodesForSection($sectionCode)
    {
        $fields = [];
        if (isset($this->fieldToSectionRelation[$sectionCode])) {
            $fields = $this->fieldToSectionRelation[$sectionCode];
        }

        return $fields;
    }

    /**
     * Process value with filter and return prepared value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function getValue($value)
    {
        return $this->getFilter()->filter($value);
    }

    /**
     * @return ValueFilterInterface
     */
    public function getFilter()
    {
        if (isset($this->filters[$this->getType()])) {
             $filter = $this->objectManager->get($this->filters[$this->getType()]);
         } else {
             $filter = $this->objectManager->get(\Mirasvit\Giftr\Service\Field\NoFilter::class);
         }

        return $filter;
    }

    /************************/
}
