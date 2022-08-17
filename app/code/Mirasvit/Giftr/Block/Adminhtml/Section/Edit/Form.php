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



namespace Mirasvit\Giftr\Block\Adminhtml\Section\Edit;

class Form extends \Magento\Backend\Block\Widget\Form
{
    /**
     * @var \Mirasvit\Giftr\Model\ResourceModel\Field\CollectionFactory
     */
    protected $fieldCollectionFactory;

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
     * @param \Mirasvit\Giftr\Model\ResourceModel\Field\CollectionFactory $fieldCollectionFactory
     * @param \Magento\Framework\Data\FormFactory                         $formFactory
     * @param \Magento\Framework\Registry                                 $registry
     * @param \Magento\Backend\Block\Widget\Context                       $context
     * @param array                                                       $data
     */
    public function __construct(
        \Mirasvit\Giftr\Model\ResourceModel\Field\CollectionFactory $fieldCollectionFactory,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->fieldCollectionFactory = $fieldCollectionFactory;
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->context = $context;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Backend\Block\Widget\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $form = $this->formFactory->create()->setData(
            [
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save', ['id' => $this->getRequest()->getParam('id'), 'store' => (int) $this->getRequest()->getParam('store')]),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            ]
        );

        /* @var $section \Mirasvit\Giftr\Model\Section */
        $section = $this->registry->registry('current_section');

        $fieldset = $form->addFieldset('edit_fieldset', ['legend' => __('General Information')]);
        if ($section->getId()) {
            $fieldset->addField('section_id', 'hidden', [
                'name' => 'section_id',
                'value' => $section->getId(),
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
            'value' => $section->getName(),
            'scope_label' => __('[STORE VIEW]'),
        ]);
        $fieldset->addField('sort_order', 'text', [
            'label' => __('Sort Order'),
            'name' => 'sort_order',
            'value' => $section->getSortOrder(),
        ]);
        $fieldset->addField('field_ids', '\Mirasvit\Giftr\Block\Adminhtml\Data\Form\Element\Multiselect', [
            'label' => __('Section Fields'),
            'name' => 'field_ids[]',
            'value' => $section->getFieldIds(),
            'values' => $section->getRelatedFieldCollection(true)->toOptionArray(false, true),
        ]);
        $fieldset->addField('is_active', 'select', [
            'label' => __('Active'),
            'name' => 'is_active',
            'value' => $section->getIsActive(),
            'values' => [0 => __('No'), 1 => __('Yes')],
            'disabled' => $section->getIsSystem() && !in_array($section->getCode(), $section->getOptionalSections()) ? true : false,
        ]);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
    /************************/
}
