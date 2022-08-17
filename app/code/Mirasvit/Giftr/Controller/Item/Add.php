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

use \Magento\Framework\DataObject;

class Add extends \Mirasvit\Giftr\Controller\Item
{
    /**
     * @return void
     */
    public function execute()
    {
        $message = null;
        $status = null;
        $buyRequest = new DataObject($this->getRequest()->getParams());
        if ($this->_getSession()->isLoggedIn()) {
            if ($this->getRequest()->isPost() && !$buyRequest->isEmpty()) {
                if (!$buyRequest->getData('qty')) {
                    $buyRequest->setData('qty', 1);
                }

                try {
                    $product = $this->getProductFromBuyRequest($buyRequest);
                    $this->createItemFromBuyRequest($buyRequest);

                    $link = $this->createTargetLink($buyRequest);
                    $status = self::SUCCESS;
                    $message = __(
                        '%1 has been added to your Gift Registry.
                            Click <a href="%2">here</a> to view your Gift Registry(ies).',
                        $product->getName(),
                        $link
                    );
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $message = $e->getMessage();
                    $status = self::ERROR;
                }
            }
        } else {
            $this->_getSession()->setBeforeAuthUrl($this->_getSession()->getLastUrl());
            $message = __('Please, log in before adding products to Gift Registry.');
            $status = self::NOTICE;
        }

        $this->messageManager->addMessage(
            $this->messageManager->createMessage($status, 'addGiftrComplexMessage')
                ->setData(['message' => is_string($message) ? $message : $message->__toString()])
        );

        $this->getResponse()
            ->clearHeaders()
            ->setHeader('Content-Type', 'application/json')
            ->setHeader('Cache-Control', 'no-cache')
            ->setBody(\Zend\Json\Json::encode([
                'status' => $status,
                'message' => $message,
            ]));
    }

    /**
     * Retrieve product from buy request and return its instance
     *
     * @param DataObject $buyRequest
     *
     * @return $this|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getProductFromBuyRequest(DataObject $buyRequest)
    {
        $product = ($buyRequest->getProduct())
            ? $this->productFactory->create()->load($buyRequest->getProduct())
            : null;

        if (!$product || !$product->getId() || !$product->isVisibleInCatalog()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Cannot specify product.'));
        }

        return $product;
    }

    /**
     * Create gift registry items from passed buy request
     *
     * @param DataObject $buyRequest
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function createItemFromBuyRequest(DataObject $buyRequest)
    {
        if ($buyRequest->getRegistries()) {
            $buyRequest->setRegistries(explode(',', $buyRequest->getRegistries()));
            foreach ($buyRequest->getRegistries() as $registryId) {
                $buyRequest->setRegistryId($registryId);
                $this->itemFactory->create()->updateItem($buyRequest);
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Please, specify the registry to which to assign this product.')
            );
        }
    }

    /**
     * Create target link based on the size of gift registries
     *
     * @param DataObject $buyRequest
     * @return string
     */
    private function createTargetLink(DataObject $buyRequest)
    {
        $link = $link = $this->context->getUrl()->getUrl('giftr/registry/');
        if (count($buyRequest->getRegistries()) == 1) {
            $link = $this->context->getUrl()->getUrl('giftr/item/manage', ['id' => $buyRequest->getRegistries()[0]]);
        }

        return $link;
    }
}
