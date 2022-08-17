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



namespace Mirasvit\Giftr\Model\Plugin\Checkout\Webapi;

/**
 * Patch for returning cart ID if client is a guest and if purchase related with Gift Registry
 */
class ParamOverriderCartId
{
    /**
     * @var \Mirasvit\Giftr\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @param \Mirasvit\Giftr\Helper\Data       $helper
     * @param \Magento\Checkout\Model\Session   $checkoutSession
     */
    public function __construct(
        \Mirasvit\Giftr\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Quote\Model\Webapi\ParamOverriderCartId $subject
     * @param null|int $result
     *
     * @return null|int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetOverriddenValue(
        \Magento\Quote\Model\Webapi\ParamOverriderCartId $subject,
        $result
    ) {
        if ($result === null && $this->helper->isGiftrPurchase()) {
            $cart = $this->checkoutSession->getQuote();
            if ($cart) {
                $result = $cart->getId();
            }
        }

        return $result;
    }
}