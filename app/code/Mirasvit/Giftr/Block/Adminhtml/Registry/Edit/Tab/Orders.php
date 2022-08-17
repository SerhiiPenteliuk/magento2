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

class Orders extends \Magento\Backend\Block\Widget\Grid\Extended
    implements TabInterface
{
    /**
     * @var \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $collectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
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
        $this->setDefaultSort('created_at', 'desc');
        $this->setId('edit_tab_orders');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Prepare collection for grid
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $registry = $this->registry->registry('current_registry');
        $orderIds = ($registry->hasOrders()) ? $registry->getOrderIds() : [0];
        $collection = null;

        $collection = $this->collectionFactory->getReport('sales_order_grid_data_source')
            ->addFieldToSelect(
                [
                    'entity_id',
                    'increment_id',
                    'created_at',
                    'grand_total',
                    'billing_name',
                    'shipping_name',
                    'status'
                ]
            )
            ->addFieldToFilter('main_table.entity_id', ['in' => $orderIds]);


        $this->setCollection($collection);
        parent::_prepareCollection();

        $this->prepareTotals(['grand_total' => 0]);

        return $this;
    }

    /**
     * Prepare total row for grid.
     *
     * @param array $fields - fields to count with default values
     *
     * @return $this
     */
    private function prepareTotals(array $fields = [])
    {
        $totals = new \Magento\Framework\DataObject();
        foreach ($this->getCollection() as $item) {
            foreach ($fields as $field => $value) {
                $fields[$field] += $item->getData($field);
            }
        }

        $totals->setData($fields);

        $this->setCountTotals(true);
        $this->setTotals($totals);

        return $this;
    }

    /**
     * Prepare grid columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', [
            'header' => __('Order #'),
            'index' => 'increment_id',
            'align' => 'center',
            'width' => '100px',
            'totals_label' => __('Total'),
        ]);

        $this->addColumn('order_created_at', [
            'header' => __('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
        ]);

        $this->addColumn('billing_name', [
            'header' => __('Bill to Name'),
            'index' => 'billing_name',
        ]);

        $this->addColumn('shipping_name', [
            'header' => __('Ship to Name'),
            'index' => 'shipping_name',
        ]);

        $this->addColumn('status', [
            'header' => __('Order Status'),
            'index' => 'status',
            'width' => '100px',
        ]);

        $this->addColumn('grand_total', [
            'header' => __('Order Total'),
            'index' => 'grand_total',
            'type' => 'currency',
            'currency' => 'order_currency_code',
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
            'totals_label' => '',
            'actions' => [
                [
                    'url' => ['base' => 'sales/order/view/'],
                    'caption' => __('View'),
                    'field' => 'order_id',
                ],
            ],
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @param \Magento\Sales\Model\Order|\Magento\Framework\DataObject $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $row->getId()]);
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
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabLabel()
    {
        return __('Orders');
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
