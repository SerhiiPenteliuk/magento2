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



namespace Mirasvit\Giftr\Block\Adminhtml\Item\Edit;

class Form extends \Magento\Backend\Block\Widget\Form
{
    /**
     * @var \Mirasvit\Giftr\Model\ResourceModel\Priority\CollectionFactory
     */
    protected $priorityCollectionFactory;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $backendUrlManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * @param \Mirasvit\Giftr\Model\ResourceModel\Priority\CollectionFactory $priorityCollectionFactory
     * @param \Magento\Framework\Data\FormFactory                            $formFactory
     * @param \Magento\Backend\Model\Url                                     $backendUrlManager
     * @param \Magento\Framework\Registry                                    $registry
     * @param \Magento\Backend\Block\Widget\Context                          $context
     * @param array                                                          $data
     */
    public function __construct(
        \Mirasvit\Giftr\Model\ResourceModel\Priority\CollectionFactory $priorityCollectionFactory,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Model\Url $backendUrlManager,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->priorityCollectionFactory = $priorityCollectionFactory;
        $this->formFactory = $formFactory;
        $this->backendUrlManager = $backendUrlManager;
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
                'action' => $this->getUrl('*/*/save', ['id' => $this->getRequest()->getParam('id')]),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            ]
        );

        $item = $this->registry->registry('current_item');

        $fieldset = $form->addFieldset('edit_fieldset', ['legend' => __('General Information')]);
        if ($item->getId()) {
            $fieldset->addField('item_id', 'hidden', [
                'name' => 'item_id',
                'value' => $item->getId(),
            ]);
        }

        $url = $this->backendUrlManager->getUrl('catalog/product/edit', ['id' => $item->getProductId()]);
        $fieldset->addField('product_name', 'hidden', [
            'label' => __('Product Name'),
            'after_element_html' => '<tr><td class="label"><label for="title">'.__('Product Name').': </label></td>'.
                '<td class="value"><a href="'.$url.'">' . $item->getProduct()->getName().'</a></td></tr>',
        ]);

        $fieldset->addField('qty', 'text', [
            'label' => __('QTY'),
            'required' => true,
            'name' => 'qty',
            'value' => $item->getQty(),
        ]);
        $fieldset->addField('qty_ordered', 'text', [
            'label' => __('Ordered QTY'),
            'name' => 'qty_ordered',
            'value' => $item->getQtyOrdered(),
        ]);
        $fieldset->addField('qty_received', 'text', [
            'label' => __('Received QTY'),
            'name' => 'qty_received',
            'value' => $item->getQtyReceived(),
        ]);

        $collection = $this->priorityCollectionFactory->create();
        $fieldset->addField('priority_id', 'select', [
            'label' => __('Priority'),
            'name' => 'priority_id',
            'value' => $item->getPriorityId(),
            'values' => $collection->toOptionArray(true),
        ]);

        $fieldset->addField('note', 'textarea', [
            'label' => __('Note'),
            'name' => 'note',
            'value' => $item->getNote(),
        ]);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
    /************************/
}
