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



namespace Mirasvit\Giftr\Block\Registry;

class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $storage;

    /**
     * @var \Mirasvit\Giftr\Model\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $context;

    /**
     * @var \Mirasvit\Giftr\Model\Registry|null
     */
    private $fields = null;

    /**
     * @param \Magento\Framework\Registry                      $registry
     * @param \Magento\Customer\Model\CustomerFactory          $customerFactory
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->storage = $registry;
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
        $this->context = $context;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->addTitleBlock();
        $this->addBreadcrumbBlock();

        return $this;
    }

    /**
     * @return \Mirasvit\Giftr\Model\Registry
     */
    public function getRegistry()
    {
        if (null === $this->registry) {
            $this->registry = $this->storage->registry('current_registry');
        }

        return $this->registry;
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        return $this->customerFactory->create()->load($this->customerSession->getCustomerId());
    }

    /**
     * @return string
     */
    public function getRegistrantName()
    {
        return sprintf('%s %s', $this->getRegistry()->getFirstname(), $this->getRegistry()->getLastname());
    }

    /**
     * @return string
     */
    public function getCoRegistrantName()
    {
        return sprintf('%s %s', $this->getRegistry()->getSafe('co_firstname'), $this->getRegistry()->getSafe('co_lastname'));
    }

    /**
     * @param int $format
     * @return bool|string
     */
    public function getEventDate($format = \IntlDateFormatter::LONG)
    {
        return $this->_localeDate->formatDateTime($this->getRegistry()->getEventAt(), $format, \IntlDateFormatter::NONE, null, 'UTC');
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addTitleBlock()
    {
        $this->pageConfig->getTitle()->set(__('Gift Registry %1', $this->getRegistry()->getSafe('name')));

        return $this;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addBreadcrumbBlock()
    {
        if ($breadcrumb = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumb->addCrumb('home', [
                    'label' => __('Home'),
                    'title' => __('Home Page'),
                    'link' => $this->getBaseUrl(),
                ])
                ->addCrumb('giftr_search', [
                    'label' => __('Gift Registry Search'),
                    'title' => __('Gift Registry Search'),
                    'link' => $this->getUrl('*/search/result'),
                ])
                ->addCrumb('giftr_view', [
                    'label' => $this->getRegistry()->getSafe('name'),
                    'title' => $this->getRegistry()->getSafe('name'),
                ]);
        }

        return $this;
    }

    /**
     * @return \Mirasvit\Giftr\Model\ResourceModel\Field\Collection
     */
    public function getFieldCollection()
    {
        if (null === $this->fields) {
            $this->fields = $this->getRegistry()->getFieldCollection();
        }

        return $this->fields;
    }

    /**
     * Get value of registry's additional field by its code.
     *
     * @param \Mirasvit\Giftr\Model\Field $field
     *
     * @return string
     */
    public function getFieldValue($field)
    {
        $value = $this->getRegistry()->escape($this->getRegistry()->getValueByCode($field->getCode()));
        switch ($field->getType()) {
            case 'select':
                $value = $field->getOptionByValue($value);
                break;
            case 'date':
                $value = date('F d Y', strtotime($value));
                break;
        }

        return $this->escapeHtml($value);
    }

    /**
     * @param int $width
     * @param int $height
     * @return \Mirasvit\Core\Helper\Image
     */
    public function getImageUrl($width = 300 , $height = 200)
    {
        return $this->getRegistry()->getImageUrl($width, $height);
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->_storeManager;
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /************************/
}
