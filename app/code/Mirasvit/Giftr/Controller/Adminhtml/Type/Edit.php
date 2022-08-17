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

class Edit extends \Mirasvit\Giftr\Controller\Adminhtml\Type
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page|void
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        if (
            !$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->isSingleStoreMode() &&
            ($switchBlock = $resultPage->getLayout()->getBlock('store_switcher'))
        ) {
            $switchBlock->setSwitchUrl(
                $this->getUrl('*/*/*', ['store' => null, '_current' => true])
            );
        }

        $type = $this->_initType();

        if ($type->getId()) {
            $this->_initAction();
            $resultPage->getConfig()->getTitle()->prepend(__("Edit Event Type '%1'", $type->getName()));
            $this->_addBreadcrumb(__('Event Types'), __('Event Types'), $this->getUrl('*/*/'));
            $this->_addBreadcrumb(__('Edit Event Type '), __('Edit Event Type '));

            return $resultPage;
        } else {
            $this->messageManager->addError(__('The Event Type does not exist.'));
            $this->_redirect('*/*/');
        }
    }
}
