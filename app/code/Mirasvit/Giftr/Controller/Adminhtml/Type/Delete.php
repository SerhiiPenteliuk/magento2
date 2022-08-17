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



namespace Mirasvit\Giftr\Controller\Adminhtml\Type;

use Magento\Framework\Controller\ResultFactory;

class Delete extends \Mirasvit\Giftr\Controller\Adminhtml\Type
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|void
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $type = $this->typeFactory->create()->load($this->getRequest()->getParam('id'));
                if ($type && $type->getIsSystem()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('System types cannot be deleted.'));
                }

                $type->delete();

                $this->messageManager->addSuccess(
                        __('Event Type was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id'), ]);
            }
        }
        $this->_redirect('*/*/');
    }
}
