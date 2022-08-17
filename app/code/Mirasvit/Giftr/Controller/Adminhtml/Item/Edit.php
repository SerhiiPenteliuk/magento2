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

use Magento\Framework\Controller\ResultFactory;

class Edit extends \Mirasvit\Giftr\Controller\Adminhtml\Item
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page|void
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $item = $this->_initItem();

        if ($item->getId()) {
            $resultPage->getConfig()->getTitle()->prepend(__("Edit Item '%1'", $item->getProduct()->getName()));
            $this->_initAction();
            $this->_addBreadcrumb(__('Gift Registry Products'), __('Gift Registry Products'), $this->getUrl('*/*/'));
            $this->_addBreadcrumb(__('Edit Item '), __('Edit Item '));
            $resultPage->getLayout()->getBlock('head');
            $this->_addContent($resultPage->getLayout()->createBlock('\Mirasvit\Giftr\Block\Adminhtml\Item\Edit'));

            return $resultPage;
        } else {
            $this->messageManager->addError(__('The Item does not exist.'));
            if ($this->getRequest()->has('registry_id')) {
                $this->_redirect('*/adminhtml_registry/edit', ['id' => $this->getRequest()->getParam('registry_id')]);
            } else {
                $this->_redirect('*/adminhtml_registry/');
            }
        }
    }
}
