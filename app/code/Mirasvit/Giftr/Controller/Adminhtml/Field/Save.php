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

use Magento\Framework\Controller\ResultFactory;
use \Magento\Framework\Exception\LocalizedException;

class Save extends \Mirasvit\Giftr\Controller\Adminhtml\Field
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect $resultRedirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($data = $this->getRequest()->getParams()) {
            $field = $this->_initField();
            $field->addData($data);

            try {
                foreach ($field->getForbiddenFieldNames() as $fieldName) {
                    if ($field->dataHasChangedFor($fieldName)) {
                        throw new LocalizedException(__(
                            'The field: "%1" is system and cannot be changed.',
                            $fieldName
                        ));
                    }
                }

                $field->save();

                $this->messageManager->addSuccess(__('Registry Form Field was successfully saved'));
                $this->backendSession->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', [
                        'id' => $field->getId(), 'store' => $field->getStoreId()
                    ]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->backendSession->setFormData($data);

                return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
        }
        $this->messageManager->addError(__('Unable to find Registry Form Field to save'));

        return $resultRedirect->setPath('*/*/');
    }
}
