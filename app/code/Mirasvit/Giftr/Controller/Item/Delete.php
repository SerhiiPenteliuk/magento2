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

class Delete extends \Mirasvit\Giftr\Controller\Item
{
    /**
     * @return void
     */
    public function execute()
    {
        $status = null;
        $message = null;
        $id = $this->getRequest()->getParam('item_id');
        if ($this->getRequest()->isPost() && $id) {
            try {
                /** @var \Mirasvit\Giftr\Model\Item $item * */
                $item = $this->itemFactory->create()->load($id);
                if ($item) {
                    $item->delete();
                    $status = self::SUCCESS;
                    $message = __('Product was successfully deleted');
                }
            } catch (\Exception $e) {
                $status = self::ERROR;
                $message = $e->getMessage();
            }
        }

        $this->messageManager->addMessage(
            $this->messageManager->createMessage($status)
                ->setText($message)
        );
        $this->getResponse()
            ->clearHeaders()
            ->setHeader('Content-Type', 'application/json')
            ->setHeader('Cache-Control', 'no-cache')
            ->setBody(\Zend\Json\Json::encode([
                'status' => $status,
                'message' => $message,
                'itemId' => $id,
            ]));
    }
}
