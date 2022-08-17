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

class View extends \Mirasvit\Giftr\Controller\Registry
{
    const LAYOUT_2COLUMNS_LEFT = '2columns-left';

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($registry = $this->_initRegistry(true)) {
            if (!$registry->getIsPublic() &&
                $this->getRequest()->get('uid') !== $registry->getUid() &&
                $this->_getSession()->getCustomerId() != $registry->getCustomerId()
            ) {
                $this->messageManager->addErrorMessage(
                    'This Gift Registry is private. Please specify the registry\'s access code.'
                );
                $resultRedirect->setRefererUrl();

                return $resultRedirect;
            }

            if ($this->config->getIsShowGiftMessages()) {
                $resultPage->getConfig()->setPageLayout(self::LAYOUT_2COLUMNS_LEFT);
            }

            return $resultPage;
        } else {
            $resultRedirect->setPath('*/*/');

            return $resultRedirect;
        }
    }
}
