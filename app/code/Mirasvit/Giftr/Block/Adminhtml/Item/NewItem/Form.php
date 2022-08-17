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



namespace Mirasvit\Giftr\Block\Adminhtml\Item\NewItem;


use Magento\Backend\Block\Widget\Form\Generic;

class Form extends Generic
{
    const FORM_ID = 'registry_item_add_form';

    /**
     * @var \Mirasvit\Giftr\Model\ResourceModel\Priority\CollectionFactory $priorityCollectionFactory
     */
    protected $priorityCollectionFactory;

    /**
     * Form constructor.
     *
     * @param \Mirasvit\Giftr\Model\ResourceModel\Priority\CollectionFactory $priorityCollectionFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Mirasvit\Giftr\Model\ResourceModel\Priority\CollectionFactory $priorityCollectionFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->priorityCollectionFactory = $priorityCollectionFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return $this|Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();
        $form->setUseContainer(true);
        $form->setId(self::FORM_ID);
        $form->setMethod('post');
        $form->setAction($this->getUrl('*/*/save'));

        $this->addFields($form);

        $this->setForm($form);

        return $this;
    }

    /**
     * Method adds form fields
     *
     * @param \Magento\Framework\Data\Form $form
     *
     * @return $this
     */
    protected function addFields(\Magento\Framework\Data\Form $form)
    {
        $fieldset = $form->addFieldset('general', [
            'legend' => __('General Information')
        ]);
        /* @var $priorityCollection \Mirasvit\Giftr\Model\ResourceModel\Priority\Collection */
        $priorityCollection = $this->priorityCollectionFactory->create();

        $fieldset->addField('qty', 'text', [
            'label' => __('QTY'),
            'required' => true,
            'name' => 'qty',
            'value' => 1,
        ]);
        $fieldset->addField('qty_ordered', 'text', [
            'label' => __('Ordered QTY'),
            'name' => 'qty_ordered',
            'value' => 0,
        ]);
        $fieldset->addField('qty_received', 'text', [
            'label' => __('Received QTY'),
            'name' => 'qty_received',
            'value' => 0,
        ]);
        $fieldset->addField('priority_id', 'select', [
            'label' => __('Priority'),
            'name' => 'priority_id',
            'values' => $priorityCollection->toOptionArray(true),
        ]);
        $fieldset->addField('note', 'textarea', [
            'label' => __('Note'),
            'name' => 'note',
        ]);
        $fieldset->addField('registry_id', 'hidden', [
            'name' => 'registry_id',
            'value' => $this->getRequest()->getParam('registry_id')
        ]);

        // Add "Product Select" widget
        $productField = $fieldset->addField('product_id', 'label', [
            'label' => __('Product'),
            'title' => __('Select Product'),
            'name' => 'product_id',
            'class'     => 'widget-option',
            'required' => true,
        ]);

        $helperData = ['button' => ['open' => __('Select Product')]];
        $this->getLayout()->createBlock(
                '\Magento\Catalog\Block\Adminhtml\Product\Widget\Chooser',
                '',
                $helperData
            )
            ->setConfig($helperData)
            ->setFieldsetId($fieldset->getId())
            ->setTranslationHelper('giftr')
            ->prepareElementHtml($productField);

        return $this;
    }
}