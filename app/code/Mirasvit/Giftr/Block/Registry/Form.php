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

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Form extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Mirasvit\Giftr\Model\RegistryFactory
     */
    protected $registryFactory;

    /**
     * @var \Mirasvit\Giftr\Model\TypeFactory
     */
    protected $typeFactory;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $addressFactory;

    /**
     * @var \Magento\Config\Model\Config\Source\YesnoFactory
     */
    protected $systemConfigSourceYesnoFactory;

    /**
     * @var \Magento\Customer\Block\Address\Edit
     */
    protected $addressEdit;

    /**
     * @var \Mirasvit\Giftr\Helper\Block
     */
    protected $giftrBlock;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $context;

    /**
     * @var \Mirasvit\Giftr\Model\ResourceModel\Type\CollectionFactory
     */
    protected $typeCollectionFactory;

    /**
     * @var null|\Mirasvit\Giftr\Model\ResourceModel\Section\Collection
     */
    private $sectionCollection  = null;

    /**
     * @var array
     */
    private $sections           = [];
    /**
     * @var array
     */
    private $layoutProcessors;
    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    private $addressConfig;
    /**
     * @var \Magento\Customer\Model\Address\Mapper
     */
    private $addressMapper;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param \Magento\Customer\Model\Session                               $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface             $customerRepository
     * @param \Magento\Customer\Model\Address\Mapper                        $addressMapper
     * @param \Magento\Customer\Model\Address\Config                        $addressConfig
     * @param \Mirasvit\Giftr\Model\RegistryFactory                         $registryFactory
     * @param \Mirasvit\Giftr\Model\TypeFactory                             $typeFactory
     * @param \Magento\Customer\Model\AddressFactory                        $addressFactory
     * @param \Magento\Config\Model\Config\Source\YesnoFactory              $systemConfigSourceYesnoFactory
     * @param \Magento\Customer\Block\Address\Edit                          $addressEdit
     * @param \Mirasvit\Giftr\Helper\Block                                  $giftrBlock
     * @param \Magento\Framework\Registry                                   $registry
     * @param \Mirasvit\Giftr\Model\ResourceModel\Type\CollectionFactory    $typeCollectionFactory
     * @param \Magento\Framework\View\Element\Template\Context              $context
     * @param array $layoutProcessors
     * @param array                                                         $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Mirasvit\Giftr\Model\RegistryFactory $registryFactory,
        \Mirasvit\Giftr\Model\TypeFactory $typeFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Config\Model\Config\Source\YesnoFactory $systemConfigSourceYesnoFactory,
        \Magento\Customer\Block\Address\Edit $addressEdit,
        \Mirasvit\Giftr\Helper\Block $giftrBlock,
        \Magento\Framework\Registry $registry,
        \Mirasvit\Giftr\Model\ResourceModel\Type\CollectionFactory $typeCollectionFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        array $layoutProcessors = [],
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->addressMapper = $addressMapper;
        $this->addressConfig = $addressConfig;
        $this->registryFactory = $registryFactory;
        $this->typeFactory = $typeFactory;
        $this->addressFactory = $addressFactory;
        $this->systemConfigSourceYesnoFactory = $systemConfigSourceYesnoFactory;
        $this->typeCollectionFactory = $typeCollectionFactory;
        $this->addressEdit = $addressEdit;
        $this->giftrBlock = $giftrBlock;
        $this->registry = $registry;
        $this->context = $context;
        $this->layoutProcessors = $layoutProcessors;
        parent::__construct($context, $data);
    }

    /**
     * @return \Mirasvit\Giftr\Model\Registry
     */
    public function getRegistry()
    {
        $registry = $this->registry->registry('current_registry');
        if (!$registry) {
            $registry = $this->registryFactory->create();
        }

        return $registry;
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer()
    {
        return $this->customerRepository->getById($this->customerSession->getCustomerId());
    }

    /**
     * @return \Mirasvit\Giftr\Model\Type|null
     */
    public function getType()
    {
        $eventType = null;
        if ($id = $this->getRequest()->getParam('type_id')) {
            $eventType = $this->typeFactory->create()->load($id);
        } elseif ($registry = $this->getRegistry()) {
            $eventType = $registry->getType();
        }

        return $eventType;
    }

    /**
     * @return \Magento\Customer\Model\Address $address
     */
    public function getAddress()
    {
        $address = $this->addressFactory->create()
            ->setPrefix($this->getCustomer()->getPrefix())
            ->setFirstname($this->getCustomer()->getFirstname())
            ->setMiddlename($this->getCustomer()->getMiddlename())
            ->setLastname($this->getCustomer()->getLastname())
            ->setSuffix($this->getCustomer()->getSuffix());

        if ($postedData = $this->customerSession->getAddressFormData(true)) {
            $address->addData($postedData);
        }

        return $address;
    }

    /**
     * @return string
     */
    public function getAddressHtmlSelect()
    {
        return $this->giftrBlock->getAddressHtmlSelect($this->getRegistry()->getShippingAddressId())->toHtml();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getNameBlockHtml()
    {
        $nameBlock = $this->getLayout()
            ->createBlock('\Magento\Customer\Block\Widget\Name')
            ->setObject($this->getAddress());

        return $nameBlock->toHtml();
    }

    /**
     * @return string
     */
    public function getCountryHtmlSelect()
    {
        return $this->addressEdit->getCountryHtmlSelect(null, 'shipping[country_id]', 'shipping:country');
    }

    /**
     * @return bool
     */
    public function hasCoRegistrant()
    {
        return $this->getRegistry()->hasCoRegistrant();
    }

    /**
     * @return string
     */
    public function getCoRegistrantStyle()
    {
        return $this->hasCoRegistrant() ? 'block' : 'none';
    }

    /**
     * @return \Mirasvit\Giftr\Model\ResourceModel\Section\Collection
     */
    public function getSectionCollection()
    {
        if (null === $this->sectionCollection) {
            $this->sectionCollection = $this->getType()->getSectionCollection();
        }

        return $this->sectionCollection;
    }

    /**
     * Instantiate section blocks and create child group for them.
     *
     * @return void
     */
    public function prepareFormSections()
    {
        foreach ($this->getSectionCollection() as $section) {
            $section = $section->load($section->getId());
            $sectionBlockName = 'giftr_section_' . $section->getId();
            $sectionClass = '\Mirasvit\Giftr\Block\Html\Section\\' . ucfirst($section->getCode());
            if (!class_exists($sectionClass)) {
                $sectionClass = '\Mirasvit\Giftr\Block\Html\Section';
            }

            $sectionBlock = $this->getLayout()
                ->createBlock($sectionClass, $sectionBlockName)
                ->setSection($section)
                ->setRegistry($this->getRegistry())
                ->setType($this->getType());

            $this->sections[$sectionBlockName] = $sectionBlock;
        }
    }

    /**
     * Get group of section blocks.
     *
     * @return array
     */
    public function getSections()
    {
        if (!$this->sections) {
            $this->prepareFormSections();
        }

        return $this->sections;
    }

    /**
     * @return string
     */
    public function getCoRegistrantBtnText()
    {
        $text = __('Add Co-Registrant');
        if ($this->hasCoRegistrant()) {
            $text = __('Remove Co-Registrant');
        }

        return $text;
    }

    /**
     * Get current customer data in JSON format
     *
     * @return string
     */
    public function getCustomerData()
    {
        $customer = $this->getCustomer();
        $customerData = $customer->__toArray();
        foreach ($customer->getAddresses() as $key => $address) {
            $customerData['addresses'][$key]['inline'] = $this->getCustomerAddressInline($address);
        }

        return \Zend_Json::encode($customerData);
    }

    /**
     * Set additional customer address data
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return string
     */
    private function getCustomerAddressInline($address)
    {
        $builtOutputAddressData = $this->addressMapper->toFlatArray($address);
        return $this->addressConfig
            ->getFormatByCode(\Magento\Customer\Model\Address\Config::DEFAULT_ADDRESS_FORMAT)
            ->getRenderer()
            ->renderArray($builtOutputAddressData);
    }

    /**
     * Processes JS layout, encodes it into JSON and returns the result
     *
     * @return string
     */
    public function getJsLayout()
    {
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout);
        }

        return \Zend_Json::encode($this->jsLayout);
    }
}
