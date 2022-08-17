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



namespace Mirasvit\Giftr\Block\Adminhtml\Registry\Edit\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * @method string getMode()
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class General extends \Magento\Backend\Block\Widget\Form
    implements TabInterface
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Mirasvit\Giftr\Model\SectionFactory
     */
    protected $sectionFactory;

    /**
     * @var \Mirasvit\Giftr\Model\ResourceModel\Type\CollectionFactory
     */
    protected $typeCollectionFactory;

    /**
     * @var \Magento\Store\Model\ResourceModel\Website\CollectionFactory
     */
    protected $websiteCollectionFactory;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @param \Magento\Framework\App\ResourceConnection                        $resource
     * @param \Magento\Customer\Model\CustomerFactory                          $customerFactory
     * @param \Mirasvit\Giftr\Model\SectionFactory                             $sectionFactory
     * @param \Mirasvit\Giftr\Model\ResourceModel\Type\CollectionFactory       $typeCollectionFactory
     * @param \Magento\Store\Model\ResourceModel\Website\CollectionFactory     $websiteCollectionFactory
     * @param \Magento\Framework\Data\FormFactory                              $formFactory
     * @param \Magento\Framework\Registry                                      $registry
     * @param \Magento\Backend\Block\Widget\Context                            $context
     * @param array                                                            $data
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Mirasvit\Giftr\Model\SectionFactory $sectionFactory,
        \Mirasvit\Giftr\Model\ResourceModel\Type\CollectionFactory $typeCollectionFactory,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->resource = $resource;
        $this->customerFactory = $customerFactory;
        $this->sectionFactory = $sectionFactory;
        $this->typeCollectionFactory = $typeCollectionFactory;
        $this->websiteCollectionFactory = $websiteCollectionFactory;
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->context = $context;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $form = $this->formFactory->create();
        $this->setForm($form);

        $this->addGeneralFieldset($form);
        $this->addEventFieldset($form);
        $this->addRegistrantsFieldset($form);
        $this->addShippingFieldset($form);
        $this->prepareAdditionalSections($form);

        return parent::_prepareForm();
    }

    /**
     * @param \Magento\Framework\Data\Form $form
     * @return void
     */
    private function addGeneralFieldset(\Magento\Framework\Data\Form $form)
    {
        $registry = $this->registry->registry('current_registry');
        $fieldset = $form->addFieldset('edit_fieldset', ['legend' => __('General Information')]);
        if ($registry->getId()) {
            $fieldset->addField('registry_id', 'hidden', [
                'name' => 'registry_id',
                'value' => $registry->getId(),
            ]);
            $fieldset->addField('uid', 'hidden', [
                'name' => 'uid',
                'value' => $registry->getUid(),
            ]);
        }
        $fieldset->addField('unique_id', 'note', [
            'label' => __('ID'),
            'name' => 'uid',
            'text' => '<b>' . $registry->getUid() . '</b>',
        ]);
        $fieldset->addField('name', 'text', [
            'label' => __('Title'),
            'name' => 'name',
            'value' => $registry->getName(),
            'required' => true,
        ]);
        $fieldset->addField('description', 'textarea', [
            'label' => __('Description'),
            'name' => 'description',
            'value' => $registry->getDescription(),
        ]);
        $fieldset->addField('type_id', 'select', [
            'label' => __('Type'),
            'name' => 'type_id',
            'value' => $registry->getTypeId(),
            'values' => $this->typeCollectionFactory->create()->addUsedFilter($registry->getTypeId())->toOptionArray(),
        ]);
        $fieldset->addField('is_active', 'select', [
            'label' => __('Active'),
            'name' => 'is_active',
            'value' => $registry->getIsActive(),
            'values' => [0 => __('No'), 1 => __('Yes')],
        ]);
        $fieldset->addField('is_public', 'select', [
            'label' => __('Public'),
            'name' => 'is_public',
            'value' => $registry->getIsPublic(),
            'values' => [0 => __('No'), 1 => __('Yes')],
        ]);
        $fieldset->addField('website_id', 'select', [
            'label' => __('Website'),
            'name' => 'website_id',
            'value' => $registry->getWebsiteId(),
            'values' => $this->websiteCollectionFactory->create()->toOptionArray(),
        ]);
        $fieldset->addField('image', 'image', [
            'label' => __('Image'),
            'name' => 'image',
            'value' => $registry->getImage() ? $registry->getImageUrl() : null,
            'required' => false,
        ]);
    }

    /**
     * @param \Magento\Framework\Data\Form $form
     * @return void
     */
    private function addEventFieldset(\Magento\Framework\Data\Form $form)
    {
        $registry = $this->registry->registry('current_registry');
        $fieldset = $form->addFieldset('event_fieldset', ['legend' => __('Event')]);
        $fieldset->addField('event_at', 'date', [
            'label' => __('Date'),
            'name' => 'event_at',
            'value' => $registry->getEventAt(),
            'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
            'date_format' => $this->context->getLocaleDate()->getDateFormat(\IntlDateFormatter::SHORT),
        ]);
        $fieldset->addField('location', 'text', [
            'label' => __('Location'),
            'name' => 'location',
            'value' => $registry->getLocation(),
        ]);
    }

    /**
     * @param \Magento\Framework\Data\Form $form
     * @return void
     */
    private function addRegistrantsFieldset(\Magento\Framework\Data\Form $form)
    {
        $registry = $this->registry->registry('current_registry');

        // Registrant Information
        $fieldset = $form->addFieldset('registrant_fieldset', ['legend' => __('Registrant')]);
        if ($this->getMode() === \Mirasvit\Giftr\Block\Adminhtml\Registry::MODE_NEW) {
            $fieldset->addField('customer_id', 'select', [
                'label' => __('Customer'),
                'name' => 'customer_id',
                'values' => $this->getCustomersAsOptions(),
                'value' => '',
            ]);
        } else {
            $fieldset->addField('firstname', 'text', [
                'label' => __('First Name'),
                'name' => 'firstname',
                'value' => $registry->getFirstname(),
            ]);
            $fieldset->addField('middlename', 'text', [
                'label' => __('Middle Name'),
                'name' => 'middlename',
                'value' => $registry->getMiddlename(),
            ]);
            $fieldset->addField('lastname', 'text', [
                'label' => __('Last Name'),
                'name' => 'lastname',
                'value' => $registry->getLastname(),
            ]);
            $fieldset->addField('email', 'text', [
                'label' => __('Email'),
                'name' => 'email',
                'value' => $registry->getEmail(),
            ]);
        }

        // Co-Registrant Information
        $fieldset = $form->addFieldset('coregistrant_fieldset', ['legend' => __('Co-Registrant')]);
        $fieldset->addField('co_firstname', 'text', [
            'label' => __('First Name'),
            'name' => 'co_firstname',
            'value' => $registry->getCoFirstname(),
        ]);
        $fieldset->addField('co_middlename', 'text', [
            'label' => __('Middle Name'),
            'name' => 'co_middlename',
            'value' => $registry->getCoMiddlename(),
        ]);
        $fieldset->addField('co_lastname', 'text', [
            'label' => __('Last Name'),
            'name' => 'co_lastname',
            'value' => $registry->getCoLastname(),
        ]);
        $fieldset->addField('co_email', 'text', [
            'label' => __('Email'),
            'name' => 'co_email',
            'value' => $registry->getCoEmail(),
        ]);
    }

    /**
     * @param \Magento\Framework\Data\Form $form
     * @return void
     */
    private function addShippingFieldset(\Magento\Framework\Data\Form $form)
    {
        /* @var $registry \Mirasvit\Giftr\Model\Registry */
        $registry = $this->registry->registry('current_registry');
        if ($registry->hasShippingAddressId()) {
            $afterElementHtml = '';
            $address = $registry->getShippingAddress();
            $customer = $this->customerFactory->create()->load($registry->getCustomerId());
            $options = [];
            foreach ($customer->getAddresses() as $customerAddress) {
                $options[] = [
                    'value' => $customerAddress->getId(),
                    'label' => $customerAddress->format('oneline'),
                ];
            }

            if ($address && $address->hasParentId()) {
                $url = $this->getUrl('customer/index/edit', [
                    'id' => $address->getParentId()
                ]);
                $afterElementHtml = '&nbsp;&nbsp;<a href="' . $url . '">View Address</a>';
            }

            $fieldset = $form->addFieldset('shipping_fieldset', ['legend' => __('Shipping')]);
            $fieldset->addField('shipping_address_id', 'select', [
                'label' => __('Shipping Address'),
                'name' => 'shipping_address_id',
                'value' => $registry->getShippingAddressId(),
                'values' => $options,
                'after_element_html' => $afterElementHtml
            ]);
        }
    }

    /**
     * Add custom sections to the form.
     *
     * @param \Magento\Framework\Data\Form $form
     * @return void
     */
    private function prepareAdditionalSections(\Magento\Framework\Data\Form $form)
    {
        $registry = $this->registry->registry('current_registry');
        $sections = $registry->getSectionCollection(false);
        if ($sections) {
            foreach ($sections as $section) {
                $section = $this->sectionFactory->create()->load($section->getId());
                if ($section->getId()) {
                    $fieldset = $form->addFieldset('section'.$section->getId(),
                        ['legend' => __($section->getName())]);
                    $this->prepareFields($fieldset, $section);
                }
            }
        }
    }

    /**
     * Add custom fields to the sections.
     *
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param \Mirasvit\Giftr\Model\Section                 $section
     * @return void
     */
    private function prepareFields(\Magento\Framework\Data\Form\Element\Fieldset $fieldset,
        \Mirasvit\Giftr\Model\Section $section
    ) {
        $registry = $this->registry->registry('current_registry');
        $fields = $section->getFieldCollection();
        foreach ($fields as $field) {
            $fieldOptions = [
                'label' => __($field->getName()),
                'name' => 'section['.$section->getId().'][field]['.$field->getCode().']',
                'value' => $registry->getValueByCode($field->getCode()),
                'note' => __($field->getDescription()),
            ];
            $this->addOptions($field, $fieldOptions);
            $fieldset->addField($field->getCode(), $field->getType(), $fieldOptions);
        }
    }

    /**
     * Add options to the fields depend on field type.
     *
     * @param \Mirasvit\Giftr\Model\Field $field
     * @param array                       $fieldOptions
     * @return void
     */
    private function addOptions(\Mirasvit\Giftr\Model\Field $field, array &$fieldOptions)
    {
        $registry = $this->registry->registry('current_registry');
        switch ($field->getType()) {
            case 'date':
                $fieldOptions = array_merge($fieldOptions, [
                    'format' => $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT),
                ]);
                break;
            case 'select':
                $fieldOptions = array_merge($fieldOptions, [
                    'values' => $field->getValuesAsOptions(),
                ]);
                break;
            case 'checkbox':
                $fieldOptions = array_merge($fieldOptions, [
                    'checked' => $registry->getValueByCode($field->getCode()),
                    'onclick' => 'this.value = this.checked ? 1 : 0;',
                ]);
                break;
        }
    }

    /**
     * Get customer collection as array of options in a form:
     * "Firstname Lastname (ID:customer_id)".
     *
     * @return array
     */
    public function getCustomersAsOptions()
    {
        $options = [__('-- Please select customer --')];
        $customers = $this->customerFactory->create()->getCollection();
        $firstname = $this->customerFactory->create()->getResource()->getAttribute('firstname');
        $lastname = $this->customerFactory->create()->getResource()->getAttribute('lastname');
        $customers->getSelect()
            ->join(
                ['fname' => $this->resource->getTableName('customer_entity_varchar')],
                'fname.entity_id = e.entity_id AND fname.attribute_id = '.$firstname->getId(),
                ['firstname' => 'value']
            )
            ->join(
                ['lname' => $this->resource->getTableName('customer_entity_varchar')],
                'lname.entity_id = e.entity_id AND lname.attribute_id = '.$lastname->getId(),
                ['lastname' => 'value']
            )
            ->columns(
                new \Zend_Db_Expr(
                    "CONCAT(`fname`.`value`, ' ',`lname`.`value`, ' (ID:', `e`.`entity_id`, ')') AS label"
                )
            )
            ->order('e.entity_id DESC');

        foreach ($customers as $customer) {
            $options[] = [
                'label' => $customer->getLabel(),
                'value' => $customer->getId(),
            ];
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
