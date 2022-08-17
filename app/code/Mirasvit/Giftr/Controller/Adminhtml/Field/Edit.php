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

class Edit extends \Mirasvit\Giftr\Controller\Adminhtml\Field
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $field = $this->_initField();

        if ($field->getId()) {
            $this->_initAction();
            $resultPage->getConfig()->getTitle()->prepend(__("Edit Registry Form Field '%1'", $field->getName()));
            $this->_addBreadcrumb(__('Fields'), __('Fields'), $this->getUrl('*/*/'));
            $this->_addBreadcrumb(__('Edit Registry Form Field '), __('Edit Registry Form Field '));

            return $resultPage;
        } else {
            $this->messageManager->addError(__('The Registry Form Field does not exist.'));
            $this->_redirect('*/*/');
        }
    }
}
