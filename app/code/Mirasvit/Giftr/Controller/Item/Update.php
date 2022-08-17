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


class Update extends \Mirasvit\Giftr\Controller\Item
{
    /**
     * @return void
     */
    public function execute()
    {
        $productId = (int) $this->getRequest()->getParam('product');
        if (!$productId) {
            $this->_redirect('*/');

            return;
        }

        $product = $this->productFactory->create()->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $this->messageManager->addError(__('Cannot specify product.'));
            $this->_redirect('*/');

            return;
        }

        $itemId = $this->getRequest()->getParam('id');
        try {
            $item = $this->itemFactory->create()->load($itemId);
            $registry = $item->getRegistry();
            if (!$registry) {
                $this->_redirect('*/');

                return;
            }
            $buyRequest = new \Magento\Framework\DataObject($this->getRequest()->getParams());
            $buyRequest->setRegistryId($registry->getId());
            $item->updateItem($buyRequest, true);
            $message = __('%1$s has been updated in your gift registry.', $product->getName());
            $this->messageManager->addSuccess($message);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('An error occurred while updating giftr registry.'));
        }
        $this->_redirect('*/*/manage', ['id' => $registry->getId()]);
    }
}
