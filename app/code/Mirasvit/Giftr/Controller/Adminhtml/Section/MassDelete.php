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



namespace Mirasvit\Giftr\Controller\Adminhtml\Section;

class MassDelete extends \Mirasvit\Giftr\Controller\Adminhtml\Section
{
    /**
     * @return void
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParam('section_id');
        if (!is_array($ids)) {
            $this->messageManager->addError(__('Please select Registry Form Section(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    $section = $this->sectionFactory->create()
                        ->setIsMassDelete(true)
                        ->load($id);

                    if ($section && $section->getIsSystem()) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('System sections cannot be deleted.')
                        );
                    }

                    $section->delete();
                }
                $this->messageManager->addSuccess(__('Total of %d record(s) were successfully deleted', count($ids)));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}
