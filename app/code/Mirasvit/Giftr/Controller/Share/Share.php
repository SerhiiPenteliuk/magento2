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



namespace Mirasvit\Giftr\Controller\Share;

use Magento\Framework\Controller\ResultFactory;

class Share extends \Mirasvit\Giftr\Controller\Share
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Zend_Validate_Exception
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $resultRedirect->setPath('*/');

            return $resultRedirect;
        }

        $registry = $this->_initRegistry();
        if (!$registry) {
            $resultRedirect->setPath('*/*/');

            return $resultRedirect;
        }

        $emails = explode(',', $this->getRequest()->getPost('emails'));
        $message = nl2br(htmlspecialchars((string) $this->getRequest()->getPost('message')));
        $error = false;
        if (empty($emails)) {
            $error = __('Email address can\'t be emtpy.');
        } else {
            foreach ($emails as $key => $email) {
                $email = trim($email);
                if (!\Zend_Validate::is($email, 'EmailAddress')) {
                    $error = __('Please enter a valid email address.');
                    break;
                }
                $emails[$key] = $email;
            }
        }

        if ($error) {
            $this->messageManager->addErrorMessage($error);
            $this->_getSession()->setSharingForm($this->getRequest()->getParams());
            $resultRedirect->setPath('*/registry/*', ['id' => $registry->getId()]);

            return $resultRedirect;
        }

        try {
            $emails = array_unique($emails);
            foreach ($emails as $email) {
                $this->giftrMail->sendNotificationSharingEmailTemplate($email, [
                    'customer' => $this->_getSession()->getCustomer(),
                    'message' => $message,
                ]);
            }
            $this->eventManager->dispatch('giftr_share', ['registry' => $registry]);
            $this->messageManager->addSuccessMessage(__('Your Giftr Registry has been shared'));
            $resultRedirect->setPath('*/registry/*', ['id' => $registry->getId()]);

            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_getSession()->setSharingForm($this->getRequest()->getParams());
            $resultRedirect->setPath('*/registry/*', ['id' => $registry->getId()]);

            return $resultRedirect;
        }
    }
}
