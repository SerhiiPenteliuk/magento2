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

use Magento\Framework\Controller\ResultFactory;

class Configure extends \Mirasvit\Giftr\Controller\Item
{
    /**
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $id = (int) $this->getRequest()->getParam('id');
        try {
            $item = $this->itemFactory->create()->loadWithOptions($id);
            if (!$item->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Cannot load gift registry item'));
            }
            $registry = $item->getRegistry();
            if (!$registry) {
                $this->_forward('noroute');
                return;
            }

            $this->registry->register('registry_item', $item);

            $params = new \Magento\Framework\DataObject();
            $params->setCategoryId(false);
            $params->setConfigureMode(true);
            $buyRequest = $item->getBuyRequest();
            if (!$buyRequest->getQty() && $item->getQty()) {
                $buyRequest->setQty($item->getQty());
            }
            if ($buyRequest->getQty() && !$item->getQty()) {
                $item->setQty($buyRequest->getQty());
            }
            $params->setBuyRequest($buyRequest);
            $this->_objectManager->get('Magento\Catalog\Helper\Product\View')
                ->prepareAndRender(
                    $resultPage,
                    $item->getProductId(),
                    $this,
                    $params
                );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            $resultRedirect->setPath('*');

            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Cannot configure product'));
            $resultRedirect->setPath('*');

            return $resultRedirect;
        }
    }
}
