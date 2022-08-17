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



namespace Mirasvit\Giftr\Controller\Item;


use Mirasvit\Giftr\Api\Service\RegistryProviderInterface;
use \Mirasvit\Giftr\Model\Item;
use \Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;

class Addtocart extends \Mirasvit\Giftr\Controller\Item
{
    /**
     * @var \Mirasvit\Giftr\Model\Service\CartService
     */
    private $cartService;
    /**
     * @var \Mirasvit\Giftr\Helper\RequestProcessor
     */
    private $requestProcessor;

    /**
     * @param RegistryProviderInterface $registryProvider
     * @param \Mirasvit\Giftr\Helper\RequestProcessor $requestProcessor
     * @param \Mirasvit\Giftr\Model\Service\CartService $cartService ,
     * @param \Mirasvit\Giftr\Model\RegistryFactory $registryFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Mirasvit\Giftr\Model\ItemFactory $itemFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        RegistryProviderInterface $registryProvider,
        \Mirasvit\Giftr\Helper\RequestProcessor $requestProcessor,
        \Mirasvit\Giftr\Model\Service\CartService $cartService,
        \Mirasvit\Giftr\Model\RegistryFactory $registryFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Mirasvit\Giftr\Model\ItemFactory $itemFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->requestProcessor = $requestProcessor;
        $this->cartService = $cartService;
        parent::__construct(
            $registryProvider, $registryFactory, $productFactory,
            $itemFactory, $registry, $customerSession, $context
        );
    }


    /**
     * @return void|\Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $message = '';
        $status = self::SUCCESS;
        /* @var $resultRedirect \Magento\Framework\Controller\Result\Redirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $buyRequest = new DataObject($this->getRequest()->getParams());
        try {
            $this->requestProcessor->validateItemAddtocartBuyRequest($buyRequest);
            $this->cartService->addToCart($buyRequest);

            $addToCartUrl = $this->_objectManager->get('\Magento\Checkout\Helper\Cart')->getCartUrl();
            $message = __('Product successfully added to cart. <a href="%1">Go to Cart</a>', $addToCartUrl);

            // Redirect to cart if item added from email
            if ($this->getRequest()->has('is_email')) {
                return $resultRedirect->setUrl($addToCartUrl);
            }
        } catch (LocalizedException $e) {
            $status = self::ERROR;
            if ($e->getCode() === Item::EXCEPTION_CODE_ITEM_OPTION_OUT_OF_STOCK) {
                $message = __('Selected required option(s) are not available: %1.', $e->getMessage());
            } else {
                $message = __($e->getMessage());
            }
        } catch (\Exception $e) {
            $status = self::ERROR;
            $message = __('There was a problem during adding product to cart.');
        }

        $message = is_string($message) ? $message : $message->__toString();
        $this->messageManager->addMessage(
            $this->messageManager->createMessage($status, 'addGiftrComplexMessage')
                ->setData(['message' => $message])
                ->setText($message)
        );
    }
}
