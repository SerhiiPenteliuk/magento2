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



namespace Mirasvit\Giftr\Controller\Adminhtml\Registry;

class Save extends \Mirasvit\Giftr\Controller\Adminhtml\Registry
{
    /**
     * @return void
     * @throws \Exception
     */
    public function execute()
    {
        if ($data = $this->getRequest()->getParams()) {
            $registry = $this->_initRegistry();

            if (!$registry->getId() && isset($data['customer_id'])) {
                $registry->setCustomerId($data['customer_id']);
                $data['firstname'] = $registry->getCustomer()->getFirstname();
                $data['lastname'] = $registry->getCustomer()->getLastname();
                $data['middlename'] = $registry->getCustomer()->getMiddlename();
                $data['shipping_address_id'] = $registry->getCustomer()->getDefaultShipping();
            }

            if ($registry->getId()) {
                $data['image'] = $registry->getImage();
            }

            $registry->updateRegistryData($data);

            try {
                $registry->save();

                $this->messageManager->addSuccessMessage(__('Registry was successfully saved'));
                $this->backendSession->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $registry->getId()]);

                    return;
                }
                $this->_redirect('*/*/');

                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->backendSession->setFormData($data);
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);

                return;
            }
        }
        $this->messageManager->addErrorMessage(__('Unable to find Registry to save'));
        $this->_redirect('*/*/');
    }
}
