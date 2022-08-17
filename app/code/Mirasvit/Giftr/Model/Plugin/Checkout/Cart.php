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

/**
 * Plugin for Magento method Add product to shopping cart (quote)
 */
class Cart
{
    /**
     * @var \Mirasvit\Giftr\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Checkout\Helper\Cart $checkoutCart
     */
    private $checkoutCart;

    /**
     * @var \Magento\Framework\Message\Manager $messageManager
     */
    private $messageManager;

    /**
     * @param \Mirasvit\Giftr\Helper\Data   $helper
     * @param \Magento\Checkout\Helper\Cart $checkoutCart
     * @param \Magento\Framework\Message\Manager $messageManager
     */
    public function __construct(
        \Mirasvit\Giftr\Helper\Data $helper,
        \Magento\Checkout\Helper\Cart $checkoutCart,
        \Magento\Framework\Message\Manager $messageManager
    ) {
        $this->checkoutCart = $checkoutCart;
        $this->helper = $helper;
        $this->messageManager = $messageManager;
    }

    /**
     * Do not allow to add mixed items (from different Gift Registries or from Gift Registry and Catalog) to cart
     *
     * @param \Magento\Checkout\Model\Cart $subject
     * @param \Magento\Catalog\Model\Product|int $productInfo
     * @param \Magento\Framework\DataObject|int|array $requestInfo
     *
     * @return array - array of arguments passed to this method
     * @throws \Exception|\Magento\Framework\Exception\LocalizedException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeAddProduct(\Magento\Checkout\Model\Cart $subject, $productInfo, $requestInfo = null)
    {
        $registryId = \Mirasvit\Giftr\Helper\Data::NOT_REGISTRY_PRODUCT;
        if ($requestInfo !== null &&
            $requestInfo instanceof \Magento\Framework\DataObject &&
            $requestInfo->getRegistryId()
        ) {
            $registryId = $requestInfo->getData('registry_id');
        }

        if (!$this->helper->canAddToCart($registryId)) {
            $cartUrl = $this->checkoutCart->getCartUrl();
            if ($registryId) {
                $message = __('Your shopping cart already contains 1 or more items.'
                    .'Please complete your purchase or clear the cart prior to adding gift registry items.'
                    .'<a href="%1">View Shopping Cart</a>',
                    $cartUrl
                );

                throw new \Magento\Framework\Exception\LocalizedException($message);
            } else {
                $message = __('Your shopping cart already contains 1 or more items from Gift Registry.'
                    .'Please complete your purchase or clear the cart prior to adding new items.'
                    .'<a href="%1">View Shopping Cart</a>',
                    $cartUrl
                );
                $this->messageManager->addMessage(
                    $this->messageManager->createMessage(
                            \Magento\Framework\Message\MessageInterface::TYPE_ERROR,
                            'addGiftrComplexMessage'
                        )
                        ->setData(['message' => $message->__toString()])
                );

                throw new \Exception();
            }
        }

        return [$productInfo, $requestInfo];
    }
}