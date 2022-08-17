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



namespace Mirasvit\Giftr\Helper;

use \Magento\Framework\Math\Random;

/**
 * @SuppressWarnings(PHPMD)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const PURCHASE_TYPE_QUOTE = 'quote';
    const PURCHASE_TYPE_ORDER = 'order';
    const NOT_REGISTRY_PRODUCT = 0;

    /**
     * @var null|\Mirasvit\Giftr\Model\Registry
     */
    private $registry = null;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Mirasvit\Giftr\Model\RegistryFactory
     */
    protected $registryFactory;

    /**
     * @var \Mirasvit\Giftr\Model\ResourceModel\Item\CollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * @var \Mirasvit\Giftr\Model\ResourceModel\Purchase\CollectionFactory
     */
    protected $purchaseCollectionFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    /**
     * @var \Mirasvit\Giftr\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $storage;
    /**
     * @var Random
     */
    private $random;

    /**
     * @param Random                                                           $random
     * @param \Magento\Framework\Registry                                      $storage
     * @param \Magento\Customer\Model\CustomerFactory                          $customerFactory
     * @param \Mirasvit\Giftr\Model\RegistryFactory                            $registryFactory
     * @param \Mirasvit\Giftr\Model\ResourceModel\Item\CollectionFactory       $itemCollectionFactory
     * @param \Mirasvit\Giftr\Model\ResourceModel\Purchase\CollectionFactory   $purchaseCollectionFactory
     * @param \Magento\Checkout\Model\Session                                  $session
     * @param \Mirasvit\Giftr\Model\Config                                     $config
     * @param \Magento\Checkout\Model\Cart                                     $cart
     * @param \Magento\Framework\App\Helper\Context                            $context
     * @param \Magento\Customer\Model\Session                                  $customerSession
     */
    public function __construct(
        Random $random,
        \Magento\Framework\Registry $storage,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Mirasvit\Giftr\Model\RegistryFactory $registryFactory,
        \Mirasvit\Giftr\Model\ResourceModel\Item\CollectionFactory $itemCollectionFactory,
        \Mirasvit\Giftr\Model\ResourceModel\Purchase\CollectionFactory $purchaseCollectionFactory,
        \Magento\Checkout\Model\Session $session,
        \Mirasvit\Giftr\Model\Config $config,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->random = $random;
        $this->storage = $storage;
        $this->customerFactory = $customerFactory;
        $this->registryFactory = $registryFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->purchaseCollectionFactory = $purchaseCollectionFactory;
        $this->session = $session;
        $this->config = $config;
        $this->cart = $cart;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckoutSession()
    {
        return $this->session;
    }

    /**
     * @param int $length
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generateRandString($length)
    {
        $chars = Random::CHARS_DIGITS . Random::CHARS_UPPERS . '-';
        $randomString = $this->random->getRandomString($length, $chars);

        return $randomString;
    }

    /**
     * Login customer by its id.
     *
     * @param int $customerId
     *
     * @return bool
     */
    public function loginCustomer($customerId)
    {
        $customer = $this->customerFactory->create()->load($customerId);
        $session = $this->customerSession;
        if ($session->isLoggedIn() && $customer->getId() != $session->getCustomerId()) {
            $session->logout();
            $session->setCustomerAsLoggedIn($customer);
        } elseif (!$session->isLoggedIn()) {
            $session->setCustomerAsLoggedIn($customer);
        }

        return false;
    }

    /**
     * Encode data to base64 in URL compatible mode.
     *
     * @param string $data
     *
     * @return string
     */
    public function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Decode data from base64UrlEncode.
     *
     * @param string $data
     *
     * @return string
     */
    public function base64UrlDecode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    /**
     * Retrieve collection of items and registries.
     *
     * @param int $productId
     *
     * @return \Mirasvit\Giftr\Model\ResourceModel\Item\Collection
     */
    public function getAssociatedGiftrItems($productId)
    {
        $collection = $this->itemCollectionFactory->create()
            ->addFieldToFilter('product_id', $productId)
            ->addActiveFilter();

        return $collection;
    }

    /**
     * Check if giftr item can be added to cart.
     *
     * @param int|null $registryId - registry ID
     *
     * @return bool
     */
    public function canAddToCart($registryId = null)
    {
        $canAdd = true;
        $itemsCount = $this->cart->getItemsCount();
        $origRegistryId = $this->session->getGiftrId();

        if ($itemsCount) {
            $isMixed = $this->isMixedItems($registryId);
            $isForceShipping = $this->config->getForceShipping();
            /*$isMultiShippingAllowed = $this->context
                ->getScopeConfig()->getValue('multishipping/options/checkout_multiple');*/
            $isMultiShippingAllowed = false; // @todo implement ability to use multishipping for gift registry items

            // * if cart contains mixed items AND
            //    - Shipping is force OR
            //    - MS not allowed AND shipping is not force
            // OR
            // * gift registry not the same as the gift registry whose item already in shopping cart -
            // - do not allow items from different registries
            //
            // => do not add item to cart
            if ($isMixed && ($isForceShipping || (!$isForceShipping && !$isMultiShippingAllowed)) ||
                (null != $registryId && null != $origRegistryId && $registryId != $origRegistryId)
            ) {
                $canAdd = false;
            }
        }

        return $canAdd;
    }

    /**
     * Check if current cart will contain mixed items in combination with new item.
     *
     * @param int|null $registryId
     *
     * @return bool
     */
    public function isMixedItems($registryId = null)
    {
        $quote = $this->cart->getQuote();
        $isMixed = false;
        if ($registryId === null) {
            $registryId = $this->session->getGiftrId();
        }

        if ($registryId !== null) {
            foreach ($quote->getAllVisibleItems() as $item) {
                if (!$item->getBuyRequest()->getRegistryId() && $registryId === self::NOT_REGISTRY_PRODUCT) {
                    continue; // Continue if new product and item from cart are not from registry
                } elseif (
                    !$item->getBuyRequest()->getRegistryId() ||
                    $registryId != $item->getBuyRequest()->getRegistryId()
                ) {
                    $isMixed = true;
                    break;
                }
            }
        }

        return $isMixed;
    }

    /**
     * Get registry saved in checkout session.
     *
     * @return \Mirasvit\Giftr\Model\Registry
     */
    public function getRegistry()
    {
        if (null === $this->registry) {
            $this->registry = $this->registryFactory->create()->load($this->session->getGiftrId());
        }

        return $this->registry;
    }

    /**
     * @param string $key
     * @return Object|null
     */
    public function registry($key)
    {
        return $this->storage->registry($key);
    }

    /**
     * Check if purchase made for giftr item
     * and create registry instance.
     *
     * @param string $type
     * @param \Magento\Sales\Model\Order|null $order
     *
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function isGiftrPurchase($type = self::PURCHASE_TYPE_QUOTE, \Magento\Sales\Model\Order $order = null)
    {
        // $result = (bool) $this->getCheckoutSession()->hasGiftrId();
        $result = false;
        if (!$result) {
            $purchaseEntity = null;
            $purchases = null;

            switch ($type) {
                case self::PURCHASE_TYPE_ORDER: // check if order associated with gift registry
                    $purchaseEntity = $order;
                    $purchases = $this->purchaseCollectionFactory->create()
                        ->addQuoteFilter($order->getQuoteId()); // Retrieve purchases related with order's quote
                    break;

                case self::PURCHASE_TYPE_QUOTE: // check if quote associated with gift registry;
                    // Load quote even if it's inactive
                    $this->getCheckoutSession()->setLoadInactive(true);
                    $purchaseEntity = $this->getCheckoutSession()->getQuote();
                    if ($purchaseEntity->getIsActive() || $this->getCheckoutSession()->getGiftrId()) {
                        $purchases = $this->purchaseCollectionFactory->create()
                            ->addQuoteFilter(); // Retrieve purchases related to current quote
                    }
                    break;
            }

            if ($purchases !== null && $purchases->getSize() && $purchaseEntity) {
                foreach ($purchaseEntity->getItemsCollection() as $purchasedItem) {
                    $buyRequest = $purchasedItem->getBuyRequest();
                    // If purchased item associated with giftr
                    if ($buyRequest->getRegistryId() && $buyRequest->getItemId()) {
                        $this->getCheckoutSession()->setGiftrId($purchasedItem->getBuyRequest()->getRegistryId());
                        $result = true;
                        break;
                    }
                }
            }
        }

        if ($result) {
            if ($this->registry === null) {
                $this->registry = $this->registryFactory->create()
                    ->load($this->getCheckoutSession()->getGiftrId());
            }
        }

        return $result;
    }

    /**
     * Method used to clear session, call when you want to get the correct result of the method isGiftrPurchase()
     *
     * @return void
     */
    public function clearSession()
    {
        $this->getCheckoutSession()->unsetData('giftr_id');
    }
    /************************/
}
