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

class Add extends \Mirasvit\Giftr\Controller\Adminhtml\Type
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
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

        $this->_initType();
        $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('New Event Type'));
        $this->_addBreadcrumb(__('Type  Manager'), __('Type Manager'), $this->getUrl('*/*/'));
        $this->_addBreadcrumb(__('Add Event Type '), __('Add Event Type'));

        return $resultPage;
    }
}
