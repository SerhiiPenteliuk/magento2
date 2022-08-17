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



namespace Mirasvit\Giftr\Block\Search;

/**
 * @method \Mirasvit\Giftr\Model\ResourceModel\Registry\Collection getCollection()
 */
class Result extends \Mirasvit\Giftr\Block\Registry\View
{
    /**
     * \Mirasvit\Giftr\Model\Service\RegistrySearchService
     */
    private $searchService;


    /**
     * \Mirasvit\Giftr\Model\ResourceModel\Type\Collection
     */
    private $typeCollection;

    /**
     * @var null
     */
    private $enteredData = null;

    /**
     * \Mirasvit\Giftr\Model\Config
     */
    private $config;

    /**
     * \Mirasvit\Giftr\Model\Type
     */
    private $type;

    /**
     * @param \Mirasvit\Giftr\Model\Config $config
     * @param \Mirasvit\Giftr\Model\Service\RegistrySearchService $searchService ,
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Mirasvit\Giftr\Model\ResourceModel\Type\Collection $typeCollection
     * @param \Mirasvit\Giftr\Model\Type $type
     * @param array $data
     */
    public function __construct(
        \Mirasvit\Giftr\Model\Config $config,
        \Mirasvit\Giftr\Model\Service\RegistrySearchService $searchService,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Element\Template\Context $context,
        \Mirasvit\Giftr\Model\ResourceModel\Type\Collection $typeCollection,
        \Mirasvit\Giftr\Model\Type $type,
        array $data = []
    ) {
        $this->config = $config;
        $this->searchService = $searchService;
        $this->typeCollection = $typeCollection;
        $this->type = $type;
        parent::__construct($registry, $customerFactory, $customerSession, $context, $data);
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        if ($this->getRegistryType()) {
            $this->prepareSearchResults();
        }

        return parent::_beforeToHtml();
    }

    /**
     * Prepare search result collection.
     *
     * @return $this
     */
    private function prepareSearchResults()
    {
        $collection = array();
        $registryId = trim($this->getRequest()->getParam('registry_id'));
        $name = trim($this->getRequest()->getParam('name'));
        $date = trim($this->getRequest()->getParam('event_date'));
        $location = trim($this->getRequest()->getParam('location'));
        $registryType = $this->getRegistryType();

        $giftrVisibility = $this->_scopeConfig->getValue(
            \Mirasvit\Giftr\Model\Config::XML_PATH_GIFTR_VISIBILITY,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );

        $isGiftrPublicVisible = (int) $giftrVisibility === \Mirasvit\Giftr\Model\Registry::VISIBILITY_PUBLIC;
        $isSearchFieldFilled = $registryId || $name || $date || $location;

        if (($isGiftrPublicVisible || $isSearchFieldFilled) && $registryType) {
            $collection = $this->searchService->search(['registry_id' => $registryId, 'name' =>$name, 'event_at'=>$date, 'location'=>$location]);
            if (!$registryId) {
                $collection->addFieldToFilter('is_public', 1);
            }

            if ($this->getRequest()->getParam('event_type') > 0 || $this->getRequest()->getParam('event_type') == null) {
                $collection->addFieldToFilter('type.type_id', $registryType->getId());
            }

            if ($this->config->getIsHideExpiredEvents()) {
                $now = new \DateTime();
                $collection->addFieldToFilter('main_table.event_at', ['gt' =>$now->format('Y-m-d H:i:s')]);
            }
        }

        $this->setCollection($collection);

        return $this;
    }

    /**
     * @param string $key
     * @return null|string
     */
    public function getEnteredData($key)
    {
        $value = null;

        if (null === $this->enteredData) {
            $this->enteredData = $this->customerSession
                ->getData('search_form', true);
        }

        if (!empty($this->enteredData) && isset($this->enteredData[$key])) {
            $value = $this->enteredData[$key];
        }

        return $this->escapeHtml($value);
    }

    /**
     * @param \Mirasvit\Giftr\Model\Registry $registry
     * @return void
     */
    public function setRegistry(\Mirasvit\Giftr\Model\Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @return string
     */
    public function getRegistryUrl()
    {
        return $this->getRegistry()->getViewUrl();
    }

    /**
     * @param int $width
     * @param int $height
     * @return \Mirasvit\Core\Helper\Image
     */
    public function getImageUrl($width = 135, $height = 135)
    {
        return $this->getRegistry()->getImageUrl($width, $height);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addTitleBlock()
    {
        $this->pageConfig->getTitle()->set(__('Gift Registry Search'));

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
            ])->addCrumb('giftr_search', [
                'label' => __('Gift Registry Search'),
                'title' => __('Gift Registry Search'),
            ]);
        }

        return $this;
    }

    /**
     * @return bool|\Magento\Framework\DataObject
     */
    public function getRegistryType() {
        $eventType = trim($this->getRequest()->getParam('event_type'));
        if (!empty($eventType) && $eventType > 0) {
            return $this->typeCollection->getTypeById($eventType);
        } elseif (trim($this->getRequest()->getParam('registry_type'))) {
            return $this->typeCollection->getTypeByCode(trim($this->getRequest()->getParam('registry_type')));
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getEventTypeOptionsHtml()
    {
        $selectedType = '';

        if ($this->getRequest()->getParam('event_type') > 0 || $this->getRequest()->getParam('event_type') == null) {
            $selectedType = $this->getRegistryType()->getCode();
        }

        $options = [];

        foreach ($this->type->toOptionArray(true, true, true) as $type) {
            if(empty($type['value'])) {
                $type['value'] = 0 ;
            }

            if ($type['code'] == $selectedType) {
                $options[] = '<option data-code="'. $type['code'] .'" value="'. $type['value'] .'" selected >'. $type['label'] .'</option>';
            } else {
                $options[] = '<option data-code="'. $type['code'] .'" value="'. $type['value'] .'" >'. $type['label'] .'</option>';
            }
        }

        return implode(' ', $options);
    }
}
