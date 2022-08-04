<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Observer;

use Magento\Framework\Event\ObserverInterface;

class LayoutGenerateBlocksAfter implements ObserverInterface
{
    /**
     * @var \Magento\Framework\View\Page\Config
     */
    private $pageConfig;

    /**
     * @param \Magento\Framework\View\Page\Config $pageConfig
     */
    public function __construct(
        \Magento\Framework\View\Page\Config $pageConfig
    ) {
        $this->pageConfig = $pageConfig;
    }

    /**
     * Add rel prev and rel next
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $availableActions = [
            'blog_archive_view',
            'blog_author_view',
            'blog_category_view',
            'blog_index_index',
            'blog_tag_view'
        ];
        if (!in_array($observer->getEvent()->getFullActionName(), $availableActions)) {
            return;
        }

        $productListBlock = $observer->getEvent()->getLayout()->getBlock('blog.posts.list');
        if (!$productListBlock) {
            return;
        }

        $toolbar = $productListBlock->getToolbarBlock();
        $toolbar->setCollection($productListBlock->getPostCollection());

        $pagerBlock = $toolbar->getPagerBlock();
        if (!($pagerBlock instanceof \Magento\Framework\DataObject)) {
            return;
        }

        if (1 < $pagerBlock->getCurrentPage()) {
            $this->pageConfig->addRemotePageAsset(
                $pagerBlock->getPageUrl(
                    $pagerBlock->getCollection()->getCurPage(-1)
                ),
                'link_rel',
                ['attributes' => ['rel' => 'prev']]
            );
        }
        if ($pagerBlock->getCurrentPage() < $pagerBlock->getLastPageNum()) {
            $this->pageConfig->addRemotePageAsset(
                $pagerBlock->getPageUrl(
                    $pagerBlock->getCollection()->getCurPage(+1)
                ),
                'link_rel',
                ['attributes' => ['rel' => 'next']]
            );
        }

    }
}