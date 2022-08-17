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



namespace Mirasvit\Giftr\Controller\Adminhtml\Field;

class Delete extends \Mirasvit\Giftr\Controller\Adminhtml\Field
{
    /**
     * @return void
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $field = $this->fieldFactory->create()->load($this->getRequest()->getParam('id'));
                if ($field && $field->getIsSystem()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('System fields cannot be deleted.')
                    );
                }

                $field->delete();

                $this->messageManager->addSuccess(__('Registry Form Field was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id'), ]);
            }
        }
        $this->_redirect('*/*/');
    }
}
