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



namespace Mirasvit\Giftr\Block;

use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Customer\Model\ResourceModel\CustomerRepository as CustomerRepository;

class Onepage extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Mirasvit\Giftr\Helper\Data
     */
    private $helper;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var \Magento\Customer\Model\Address\Mapper
     */
    protected $addressMapper;
    /**
     * @var AddressConfig
     */
    private $addressConfig;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Address\Mapper $addressMapper
     * @param CustomerRepository $customerRepository
     * @param AddressConfig $addressConfig
     * @param \Mirasvit\Giftr\Helper\Data $helper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        CustomerRepository $customerRepository,
        AddressConfig $addressConfig,
        \Mirasvit\Giftr\Helper\Data $helper,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->addressMapper = $addressMapper;
        $this->customerRepository = $customerRepository;
        $this->addressConfig = $addressConfig;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Decide whether to display registrant shipping address or not
     *
     * @return bool
     */
    public function isVisible()
    {
        return (
            $this->helper->isGiftrPurchase() &&
            $this->customerSession->getCustomerId() != $this->helper->getRegistry()->getCustomerId()
        );
    }

    /**
     * Create JS configuration for Giftr
     *
     * @return array
     */
    public function getRegistrantConfig()
    {
        $config = [];

        if ($this->helper->getRegistry()->getShippingAddressId()) {
            $address = $this->helper->getRegistry()->getShippingAddress();
            $config = $address->getDataModel()->__toArray();
            $addressData = $this->addressMapper->toFlatArray($address->getDataModel());
            $config['inline'] = $this->addressConfig
                ->getFormatByCode(\Magento\Customer\Model\Address\Config::DEFAULT_ADDRESS_FORMAT)
                ->getRenderer()
                ->renderArray($addressData);
        }

        return $config;
    }

    /**
     * JS configuration for Checkout
     * used to add registrang shipping address and logic
     *
     * @return array
     */
    public function getJsConfiguration()
    {
        $config = [
            "#checkout" => [
                "Magento_Ui/js/core/app" => [
                    "components" => [
                        "checkout" => [
                            "children" => [
                                "steps" => [
                                    "children" => [
                                        "shipping-step" => [
                                            "children" => [
                                                "shippingAddress" => [
                                                    "isFormInline" => false,
                                                    "children" => [
                                                        "address-list" => [
                                                          "component" => "Mirasvit_Giftr/js/view/shipping-address/list"
                                                          // "visible" => true, // display registrant address or not
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $config;
    }
}