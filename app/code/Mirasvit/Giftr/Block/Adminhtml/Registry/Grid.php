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



namespace Mirasvit\Giftr\Block\Adminhtml\Registry;


class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Mirasvit\Giftr\Model\ResourceModel\Registry\CollectionFactory
     */
    protected $registryCollectionFactory;

    /**
     * @var \Mirasvit\Giftr\Model\ResourceModel\Type\CollectionFactory
     */
    protected $typeCollectionFactory;

    /**
     * @var \Mirasvit\Giftr\Helper\Data
     */
    protected $giftrData;

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * @var \Magento\Store\Model\ResourceModel\Website\CollectionFactory
     */
    protected $websiteCollectionFactory;

    /**
     * @param \Magento\Store\Model\ResourceModel\Website\CollectionFactory   $websiteCollectionFactory
     * @param \Mirasvit\Giftr\Model\ResourceModel\Registry\CollectionFactory $registryCollectionFactory
     * @param \Mirasvit\Giftr\Model\ResourceModel\Type\CollectionFactory     $typeCollectionFactory
     * @param \Mirasvit\Giftr\Helper\Data                                    $giftrData
     * @param \Magento\Backend\Block\Template\Context|Context                $context
     * @param \Magento\Backend\Helper\Data                                   $backendHelper
     * @param array                                                          $data
     */
    public function __construct(
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
        \Mirasvit\Giftr\Model\ResourceModel\Registry\CollectionFactory $registryCollectionFactory,
        \Mirasvit\Giftr\Model\ResourceModel\Type\CollectionFactory $typeCollectionFactory,
        \Mirasvit\Giftr\Helper\Data $giftrData,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->websiteCollectionFactory = $websiteCollectionFactory;
        $this->registryCollectionFactory = $registryCollectionFactory;
        $this->typeCollectionFactory = $typeCollectionFactory;
        $this->giftrData = $giftrData;
        $this->context = $context;
        parent::__construct($context, $backendHelper, $data);
    }
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('grid');
        $this->setDefaultSort('registry_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $collection = $this->registryCollectionFactory->create()
            ->joinRegistrantName()
            ->joinOrdersQty();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn('uid', [
            'header' => __('ID'),
            'width' => '150px',
            'index' => 'uid',
            'filter_index' => 'main_table.uid',
        ]);
        $this->addColumn('registrant', [
            'header' => __('Registrant Name'),
            'index' => 'registrant_name',
            'filter_index' => $this->registryCollectionFactory->create()->joinRegistrantName(true),
        ]);
        $this->addColumn('title', [
            'header' => __('Title'),
            'index' => 'name',
            'filter_index' => 'main_table.name',
        ]);
        $this->addColumn('sum_order_qty', [
            'header' => __('Number Of Orders'),
            'index' => 'sum_order_qty',
            'filter' => false
        ]);
        $this->addColumn('created_at', [
            'header' => __('Created At'),
            'index' => 'created_at',
            'filter_index' => 'main_table.created_at',
            'type' => 'datetime',
        ]);
        $this->addColumn('type_id', [
            'header' => __('Type'),
            'index' => 'type_id',
            'filter_index' => 'main_table.type_id',
            'type' => 'options',
            'options' => $this->typeCollectionFactory->create()->getOptionArray(),
        ]);
        $this->addColumn('website_id', [
            'header' => __('Website'),
            'index' => 'website_id',
            'filter_index' => 'main_table.website_id',
            'type' => 'options',
            'options' => $this->websiteCollectionFactory->create()->toOptionHash(),
        ]);
        $this->addColumn('is_active', [
            'header' => __('Status'),
            'align' => 'center',
            'width' => '100px',
            'index' => 'is_active',
            'filter_index' => 'main_table.is_active',
            'type' => 'options',
            'options' => [
                0 => __('Archived'),
                1 => __('Visible'),
            ],
        ]);
        $this->addColumn('link', [
            'header' => __('Link'),
            'align' => 'center',
            'width' => '100px',
            'frame_callback' => [$this, 'link'],
            'filter' => false,
            'sortable' => false,
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @param var $value
     * @param var $row
     * @param var $column
     * @param var $isExport
     *
     * @return var $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function link($value, $row, $column, $isExport)
    {
        /** @var \Mirasvit\Giftr\Model\Registry $row */
        $store = $this->_storeManager->getStore($row->getStoreId());
        $link = $store->getBaseUrl() . 'giftr/registry/view' . '?' . http_build_query(['uid' => $row->getUid()]);

        return '<a href="' . $link . '" target="_blank">' . __('Guest View') . '</a>';
    }

    /**
     * @return $this|\Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('registry_id');
        $this->getMassactionBlock()->setFormFieldName('registry_id');
        $statuses = [
                ['label' => '', 'value' => ''],
                ['label' => __('Disabled'), 'value' => 0],
                ['label' => __('Enabled'), 'value' => 1],
        ];
        $this->getMassactionBlock()->addItem('is_active', [
             'label' => __('Change status'),
             'url' => $this->getUrl('*/*/massChange', ['_current' => true]),
             'additional' => [
                    'visibility' => [
                         'name' => 'is_active',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => __('Status'),
                         'values' => $statuses,
                     ],
             ],
        ]);
        $this->getMassactionBlock()->addItem('delete', [
            'label' => __('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => __('Are you sure?'),
        ]);

        return $this;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }
    
    /**
     * {@inheritdoc}
     * Used for AJAX loading
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true, 'block_id' => $this->getId()]);
    }

    /************************/
}
