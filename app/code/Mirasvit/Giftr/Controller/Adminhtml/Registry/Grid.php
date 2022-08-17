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

class Grid extends \Mirasvit\Giftr\Controller\Adminhtml\Registry
{
    /**
     * Product grid for AJAX request
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        /* @var $resultPage \Magento\Backend\Model\View\Result\Page */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        /* @var $resultRaw \Magento\Framework\Controller\Result\Raw */
        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);

        $this->_initRegistry();
        $blockId = $this->getRequest()->getParam('block_id');
        $blockSuffix = implode('\\', array_map('ucfirst', explode('_', $blockId)));
        $block = $resultPage->getLayout()->createBlock('Mirasvit\Giftr\Block\Adminhtml\Registry\\' . $blockSuffix);

        return $resultRaw->setContents($block->toHtml());
    }
}