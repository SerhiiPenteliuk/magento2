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



namespace Mirasvit\Giftr\Controller\Registry;

use Magento\Framework\Controller\ResultFactory;

class Save extends \Mirasvit\Giftr\Controller\Registry
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/');
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect;
        }

        $data = $this->getRequest()->getParams();
        if ($this->getRequest()->isPost() && !empty($data)) {
            try {
                $id = $this->saveRegistry($data);
                $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_getSession()->setSharingForm($this->getRequest()->getParams());
            }
        }

        return $resultRedirect;
    }

    /**
     * Save registry or create registry if it not exists yet based on data passed with request
     *
     * @param array $data
     *
     * @return int gift registry ID
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function saveRegistry(array $data)
    {
        if (isset($data['shipping_address_id']) && $data['shipping_address_id'] > 0) {
            if (isset($data['registry_id'])) {
                $registry = $this->registryProvider->getRegistry($data['registry_id']);
                if (!$registry) {
                    throw new \Magento\Framework\Exception\NoSuchEntityException();
                }
            } else {
                $registry = $this->registryFactory->create();
            }

            $registry->updateRegistryData($data);
            $registry->save();

            $this->messageManager->addSuccessMessage(__('Registry successfully saved'));
            $this->_getSession()->setSharingForm($this->getRequest()->getParams());
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Insufficient Data Provided'));
        }

        return $registry->getId();
    }
}
