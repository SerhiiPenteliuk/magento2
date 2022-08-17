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



namespace Mirasvit\Giftr\Controller\Adminhtml\Item;

class Save extends \Mirasvit\Giftr\Controller\Adminhtml\Item
{
    /**
     * @return void
     */
    public function execute()
    {
        if ($data = $this->getRequest()->getParams()) {
            $item = $this->_initItem();
            $item->addData($data);

            try {

                if ($this->getRequest()->getParam('isAjax')) {
                    $this->createNewItem($item);
                    return;
                }

                $item->save();
                $this->messageManager->addSuccess(__('Item was successfully saved'));
                $this->backendSession->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $item->getId()]);

                    return;
                }
                $this->_redirect('*/registry/edit', ['id' => $item->getRegistryId()]);

                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->backendSession->setFormData($data);
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);

                return;
            }
        }
        $this->messageManager->addError(__('Unable to find Item to save'));
        $this->_redirect('*/registry/');
    }

    /**
     * Create new item for Gift Registry from ajax request
     *
     * @param \Mirasvit\Giftr\Model\Item $item
     *
     * @return void
     */
    private function createNewItem(\Mirasvit\Giftr\Model\Item $item)
    {
        // Prepare buy request
        $buyRequest = new \Magento\Framework\DataObject($this->getRequest()->getParams());
        $buyRequest->setProduct(str_replace('product/', '', $item->getProductId()))
            ->setProductId($buyRequest->getProduct());

        // Add item
        $item->updateItem($buyRequest);

        // Set response
        $this->getResponse()
            ->setHeader('Content-Type', 'application/json')
            ->setBody(\Zend\Json\Json::encode([
                'ajaxExpired' => true,
                'ajaxRedirect' => $this->getUrl('*/registry/edit', [
                    'id' => $item->getRegistryId(),
                    'active_tab' => 'products_section',
                ]),
            ]));

        return;
    }
}
