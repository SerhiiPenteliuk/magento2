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



namespace Mirasvit\Giftr\Controller\Item;


class MassUpdate extends \Mirasvit\Giftr\Controller\Item
{
    /**
     * @return void
     */
    public function execute()
    {
        $registry = $this->_initRegistry();
        if (!$registry) {
            $this->_forward('noroute');
            return;
        }

        $data = $this->getRequest()->getParams();
        if ($this->getRequest()->isPost() && !empty($data)) {
            if (isset($data['items']) && count($data['items'])) {
                $this->updateItems($data['items']);
                $this->messageManager->addSuccessMessage(__('Gift Registry Items successfully updated'));
            } else {
                $this->messageManager->addErrorMessage(__('Gift Registry is empty. Please add products first'));
                $this->_redirect('*/registry/');
            }
        }
        $this->_redirect('*/*/manage/', ['id' => $registry->getId()]);
    }

    /**
     * Update gift registry items passed with request and update registry
     *
     * @param array $items
     * @return void
     */
    private function updateItems(array $items)
    {
        $updatedItems = 0;
        foreach ($items as $id => $itemData) {
            $item = $this->itemFactory->create()->load($id);
            list($note, $priority, $qty, $receivedQty) = $this->retrieveItemData($itemData);

            if ($item && $item->getId()) {
                if ($item->getNote() == $note &&
                    $item->getQty() == $qty &&
                    $item->getPriorityId() == $priority &&
                    $item->getQtyReceived() == $receivedQty
                ) {
                    continue;
                }

                $item->setNote($note)
                    ->setPriorityId($priority)
                    ->setQty($qty)
                    ->setQtyReceived($receivedQty)
                    ->save();
                ++$updatedItems;
            }
        }

        if ($updatedItems) {
            $this->registry->registry('current_registry')->save();
        }
    }

    /**
     * Retrieve data from passed item passed in request
     *
     * @param array $itemData
     *
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function retrieveItemData(array $itemData)
    {
        $note = isset($itemData['note']) ? $itemData['note'] : '';
        $priority = isset($itemData['priority_id']) ? $itemData['priority_id'] : null;
        $qty = isset($itemData['qty']) ? $itemData['qty'] : null;
        $receivedQty = isset($itemData['qty_received']) ? $itemData['qty_received'] : null;

        return [$note, $priority, $qty, $receivedQty];
    }
}
