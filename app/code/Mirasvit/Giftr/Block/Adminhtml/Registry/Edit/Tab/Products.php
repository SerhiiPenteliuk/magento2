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

class Products extends \Magento\Backend\Block\Widget\Grid\Extended
    implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_config;

    /**
     * @var \Mirasvit\Giftr\Model\ResourceModel\Item\CollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * @var \Mirasvit\Giftr\Helper\Data
     */
    protected $giftrData;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * @param \Magento\Eav\Model\Config                                  $eavConfig
     * @param \Mirasvit\Giftr\Model\ResourceModel\Item\CollectionFactory $itemCollectionFactory
     * @param \Mirasvit\Giftr\Helper\Data                                $giftrData
     * @param \Magento\Framework\Registry                                $registry
     * @param \Magento\Backend\Block\Template\Context                    $context
     * @param \Magento\Backend\Helper\Data                               $backendHelper
     * @param array                                                      $data
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Mirasvit\Giftr\Model\ResourceModel\Item\CollectionFactory $itemCollectionFactory,
        \Mirasvit\Giftr\Helper\Data $giftrData,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->_config = $eavConfig;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->giftrData = $giftrData;
        $this->registry = $registry;
        $this->context = $context;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('edit_tab_products');
        $this->setDefaultSort('item_id', 'desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $registry = $this->registry->registry('current_registry');
        $collection = $this->itemCollectionFactory->create()
            ->addFieldToFilter('registry.registry_id', $registry->getId());
        $this->joinProductAttribute($collection, ['product_name' => 'name']);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        $url = $this->getUrl('*/item/newaction', ['registry_id' => $this->registry->registry('current_registry')->getId()]);
        $this->setChild(
            'add_product_button',
            $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData(
                [
                    'label'     => __('Add Product'),
                    'onclick'   => 'return false;',
                    'class'     => 'task action-primary registry-add-item',
                ]
            )->setDataAttribute(
                [
                    'action' => 'registry-add-item',
                    'mage-init' => [
                        'GiftrNewItem' => [
                            'url' => $url,
                            'formId' => \Mirasvit\Giftr\Block\Adminhtml\Item\NewItem\Form::FORM_ID
                        ]
                    ],
                ]
            )
        );

        return parent::_prepareLayout();
    }

    /**
     * Returns "Add Product" button
     *
     * @return string
     */
    public function getAddProductButtonHtml()
    {
        return $this->getChildHtml('add_product_button');
    }

    /**
     * Generate list of grid buttons
     *
     * @return string
     */
    public function getMainButtonsHtml()
    {
        $html = '';
        if ($this->getFilterVisibility()) {
            $html .= $this->getAddProductButtonHtml();
            $html .= $this->getSearchButtonHtml();
            $html .= $this->getResetFilterButtonHtml();
        }
        return $html;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $registry = $this->registry->registry('current_registry');

        $this->addColumn('item_id', [
            'header' => __('Item Id'),
            'width' => '100',
            'index' => 'item_id',
        ]);

        $this->addColumn('product_name', [
            'header' => __('Name'),
            'index' => 'product_name',
            'filter_index' => 'name_table.value',
        ]);

        $this->addColumn('sku', [
            'header' => __('SKU'),
            'index' => 'sku',
        ]);

        $this->addColumn('qty', [
            'header' => __('QTY'),
            'index' => 'qty',
        ]);

        $this->addColumn('qty_ordered', [
            'header' => __('Ordered QTY'),
            'index' => 'qty_ordered',
        ]);

        $this->addColumn('qty_received', [
            'header' => __('Received QTY'),
            'index' => 'qty_received',
        ]);

        $this->addColumn('priority_name', [
            'header' => __('Priority'),
            'index' => 'priority_name',
            'filter_index' => 'priority.name',
            'frame_callback' => [$this, 'priority']
        ]);

        $this->addColumn('note', [
            'header' => __('Note'),
            'index' => 'note',
        ]);

        $this->addColumn('action', [
            'header' => __('Action'),
            'align' => 'center',
            'filter' => false,
            'sortable' => false,
            'width' => '100px',
            'type' => 'action',
            'getter' => 'getId',
            'header_css_class' => 'a-center',
            'actions' => [
                [
                    'caption' => __('View'),
                    'field' => 'id',
                    'url' => [
                        'base' => '*/item/edit',
                        'params' => ['registry_id' => $registry->getId()],
                    ],
                ],
            ],
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @param string $value
     * @param object $row
     * @param object $column
     * @param bool $isExport
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function priority($value, $row, $column, $isExport)
    {
        return $row->getPriorityName();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $registry = $this->registry->registry('current_registry');
        $this->setMassactionIdField('item_id');
        $this->getMassactionBlock()->setFormFieldName('item_id');
        $this->getMassactionBlock()->addItem('delete', [
            'label' => __('Delete'),
            'url' => $this->getUrl('*/item/massDelete', ['registry_id' => $registry->getId()]),
            'confirm' => __('Are you sure?'),
            'selected' => true
        ]);

        return $this;
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        $registry = $this->registry->registry('current_registry');

        return $this->getUrl('*/item/edit', ['id' => $row->getItemId(), 'registry_id' => $registry->getId()]);
    }

    /**
     * Used for AJAX loading
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true, 'block_id' => $this->getId()]);
    }

    /**
     * Join product attributes to existing collection.
     *
     * @param \Magento\Framework\Data\Collection\AbstractDb $collection
     * @param array                                         $attributes
     *
     * @return void
     */
    public function joinProductAttribute(\Magento\Framework\Data\Collection\AbstractDb $collection, array $attributes)
    {
        foreach ($attributes as $alias => $attributeCode) {
            $tableAlias     = $attributeCode.'_table';
            $attribute      = $this->_config->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
            $productFieldId = 'entity_id';
            if (!$collection->getConnection()->tableColumnExists($attribute->getBackendTable(), $productFieldId)) {
                $productFieldId = 'row_id';
            }

            $collection->getSelect()->joinLeft(
                [$tableAlias => $attribute->getBackendTable()],
                'main_table.product_id = '.$tableAlias.'.' . $productFieldId . ' AND '.$tableAlias.'.store_id = 0 AND '.
                    $tableAlias.'.attribute_id = '.$attribute->getId(),
                [$alias => 'value']
            );
        }
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabLabel()
    {
        return __('Products');
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
