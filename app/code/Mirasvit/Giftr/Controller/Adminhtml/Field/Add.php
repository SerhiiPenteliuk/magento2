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

class Add extends \Mirasvit\Giftr\Controller\Adminhtml\Field
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->_initField();
        $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('New Registry Form Field'));
        $this->_addBreadcrumb(__('Field  Manager'), __('Field Manager'), $this->getUrl('*/*/'));
        $this->_addBreadcrumb(__('Add Registry Form Field '), __('Add Registry Form Field'));

        return $resultPage;
    }
}
