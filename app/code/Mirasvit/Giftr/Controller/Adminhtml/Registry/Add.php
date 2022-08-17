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

use Magento\Framework\Controller\ResultFactory;

class Add extends \Mirasvit\Giftr\Controller\Adminhtml\Registry
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $resultPage->getConfig()->getTitle()->prepend(__('New Gift Registry'));
        $this->_initRegistry();
        $this->_initAction();
        $this->_addBreadcrumb(__('Gift Registry Manager'), __('Gift Registry Manager'), $this->getUrl('*/*/'));
        $this->_addBreadcrumb(__('Add Gift Registry'), __('Add Gift Registry'));

        $resultPage->getLayout()
            ->getBlock('head')
            ;

        $this->_addContent($resultPage->getLayout()->createBlock('\Mirasvit\Giftr\Block\Adminhtml\Registry\Edit'))
            ->_addLeft($resultPage->getLayout()->createBlock('\Mirasvit\Giftr\Block\Adminhtml\Registry\Edit\Tabs')
                ->setMode(\Mirasvit\Giftr\Block\Adminhtml\Registry::MODE_NEW)
            );

        return $resultPage;
    }
}
