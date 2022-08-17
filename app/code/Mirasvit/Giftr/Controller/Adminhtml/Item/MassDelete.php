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



namespace Mirasvit\Giftr\Controller\Adminhtml\Item;

class MassDelete extends \Mirasvit\Giftr\Controller\Adminhtml\Item
{
    /**
     * @return void
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParam('item_id');
        $registryId = $this->getRequest()->getParam('registry_id');
        if (!is_array($ids)) {
            $this->messageManager->addError(__('Please select Item(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    $item = $this->itemFactory->create()
                        ->setIsMassDelete(true)
                        ->load($id);
                    $item->delete();
                }
                $this->messageManager->addSuccess(__('Total of %1 record(s) were successfully deleted', count($ids)));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        if ($registryId) {
            $this->_redirect('*/registry/edit', ['id' => $registryId, 'active_tab' => 'products_section']);
        } else {
            $this->_redirect('*/registry/index');
        }
    }
}
