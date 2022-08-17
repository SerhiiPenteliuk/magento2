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



namespace Mirasvit\Giftr\Block\Adminhtml\Field\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Mirasvit\Giftr\Model\Config\Source\Field\Type
     */
    protected $configSourceFieldType;

    /**
     * @param \Mirasvit\Giftr\Model\Config\Source\Field\Type $configSourceFieldType
     * @param \Magento\Framework\Data\FormFactory            $formFactory
     * @param \Magento\Framework\Registry                    $registry
     * @param \Magento\Backend\Block\Widget\Context          $context
     * @param array                                          $data
     */
    public function __construct(
        \Mirasvit\Giftr\Model\Config\Source\Field\Type $configSourceFieldType,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->configSourceFieldType = $configSourceFieldType;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare field edit form
     *
     * @return $this
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create()->setData(
            [
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save',
                    [
                        'id' => $this->getRequest()->getParam('id'),
                        'store' => (int) $this->getRequest()->getParam('store')
                    ]
                ),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            ]
        );

        $field = $this->_coreRegistry->registry('current_field');

        $fieldset = $form->addFieldset('edit_fieldset', ['legend' => __('General Information')]);
        if ($field->getId()) {
            $fieldset->addField('field_id', 'hidden', [
                'name' => 'field_id',
                'value' => $field->getId(),
            ]);
        }
        $fieldset->addField('store_id', 'hidden', [
            'name' => 'store_id',
            'value' => (int) $this->getRequest()->getParam('store'),
        ]);

        $fieldset->addField('name', 'text', [
            'label' => __('Title'),
            'required' => true,
            'name' => 'name',
            'value' => $field->getName(),
            'scope_label' => __('[STORE VIEW]'),
        ]);
        $fieldset->addField('code', 'text', [
            'label' => __('Code'),
            'required' => true,
            'name' => 'code',
            'value' => $field->getCode(),
            'note' => __('Internal field. Can contain only letters, digits and underscore. Should be unique.'),
            'disabled' => $field->getIsSystem() ? true : false,
        ]);

        $fieldType = $fieldset->addField('type', 'select', [
            'label' => __('Type'),
            'required' => true,
            'name' => 'type',
            'value' => $field->getType(),
            'values' => $this->configSourceFieldType->toOptionArray(),
            'disabled' => $field->getIsSystem() ? true : false,
        ]);
        $dropdownOptions = $fieldset->addField('values', 'textarea', [
            'label' => __('Options list'),
            'name' => 'values',
            'value' => $field->getValues(),
            'note' => __('Enter each value from the new line using format: <br>value1 | label1<br>value2 | label2'),
            'scope_label' => __('[STORE VIEW]'),
            'disabled' => $field->getIsSystem() ? true : false,
        ]);
        $this->setChild('form_after', $this->getLayout()
            ->createBlock('\Magento\Backend\Block\Widget\Form\Element\Dependence')
            ->addFieldMap($fieldType->getHtmlId(), $fieldType->getName())
            ->addFieldMap($dropdownOptions->getHtmlId(), $dropdownOptions->getName())
            ->addFieldDependence($dropdownOptions->getName(), $fieldType->getName(), 'select')
        );

        $fieldset->addField('description', 'textarea', [
            'label' => __('Description'),
            'name' => 'description',
            'value' => $field->getDescription(),
            'scope_label' => __('[STORE VIEW]'),
        ]);

        $fieldset->addField('is_active', 'select', [
            'label' => __('Active'),
            'name' => 'is_active',
            'value' => $field->getIsActive(),
            'values' => [0 => __('No'), 1 => __('Yes')],
            'disabled' => $field->getIsRequired() ? true : false,
        ]);
        $fieldset->addField('sort_order', 'text', [
            'label' => __('Sort Order'),
            'name' => 'sort_order',
            'value' => $field->getSortOrder(),
        ]);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
    /************************/
}
