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

class Delete extends \Mirasvit\Giftr\Controller\Registry
{
    /**
     * @return void
     */
    public function execute()
    {
        $registry = $this->_initRegistry();
        if ($registry) {
            $registry->delete();
            $this->messageManager->addSuccessMessage(__('Registry Successfully Deleted'));
        } else {
            $this->messageManager->addErrorMessage(__('Problem loading registry. Please try again'));
        }

        $this->_redirect('*/*/');
    }
}
