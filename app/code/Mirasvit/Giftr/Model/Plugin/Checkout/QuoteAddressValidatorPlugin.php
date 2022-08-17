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



namespace Mirasvit\Giftr\Model\Plugin\Checkout;

class QuoteAddressValidatorPlugin
{
    /**
     * @var \Mirasvit\Giftr\Helper\Data
     */
    private $helper;

    /**
     * @param \Mirasvit\Giftr\Helper\Data           $helper
     */
    public function __construct(\Mirasvit\Giftr\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Quote\Model\QuoteAddressValidator $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Api\Data\AddressInterface $addressData
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundValidate(
        \Magento\Quote\Model\QuoteAddressValidator $subject,
        \Closure $proceed,
        \Magento\Quote\Api\Data\AddressInterface $addressData
    ) {
        $returnedValue = false;
        try {
            $returnedValue = $proceed($addressData);
        } catch(\Magento\Framework\Exception\NoSuchEntityException $e) {
            if ($this->helper->isGiftrPurchase()) {
                if ($this->helper->getRegistry()->getShippingAddressId() == $addressData->getCustomerAddressId()) {
                    $returnedValue = true;
                }
            }
        }

        return $returnedValue;
    }

    /**
     * @param \Magento\Quote\Model\QuoteAddressValidator $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Api\Data\CartInterface $cart
     * @param \Magento\Quote\Api\Data\AddressInterface $addressData
     * @return bool|mixed
     */
    public function aroundValidateForCart(
        \Magento\Quote\Model\QuoteAddressValidator $subject,
        \Closure $proceed,
        \Magento\Quote\Api\Data\CartInterface $cart,
        \Magento\Quote\Api\Data\AddressInterface $addressData
    ) {
        $returnedValue = false;
        try {
            $returnedValue = $proceed($cart, $addressData);
        } catch(\Magento\Framework\Exception\NoSuchEntityException $e) {
            if ($this->helper->isGiftrPurchase()) {
                if ($this->helper->getRegistry()->getShippingAddressId() == $addressData->getCustomerAddressId()) {
                    $returnedValue = true;
                }
            }
        }

        return $returnedValue;
    }
}