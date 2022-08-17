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



namespace Mirasvit\Giftr\Service\Registry;


use Magento\Sales\Model\Order;
use Mirasvit\Giftr\Api\Repository\PurchaseRepositoryInterface;
use Mirasvit\Giftr\Api\Service\Registry\OrderProviderInterface;
use Magento\GiftMessage\Model\ResourceModel\Message\Collection as WishCollection;
use Magento\GiftMessage\Model\ResourceModel\Message\CollectionFactory as WishCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

class OrderProvider implements OrderProviderInterface
{
    /**
     * @var PurchaseRepositoryInterface
     */
    private $purchaseRepository;
    /**
     * @var WishCollectionFactory
     */
    private $wishCollectionFactory;
    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * OrderProvider constructor.
     *
     * @param OrderCollectionFactory      $orderCollectionFactory
     * @param WishCollectionFactory       $wishCollectionFactory
     * @param PurchaseRepositoryInterface $purchaseRepository
     */
    public function __construct(
        OrderCollectionFactory $orderCollectionFactory,
        WishCollectionFactory $wishCollectionFactory,
        PurchaseRepositoryInterface $purchaseRepository
    ) {
        $this->purchaseRepository = $purchaseRepository;
        $this->wishCollectionFactory = $wishCollectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderIds($registryId)
    {
        return $this->purchaseRepository->getCollection()
            ->addFieldToFilter('registry_id', $registryId)
            ->addFieldToFilter('order_id', ['notnull' => true])
            ->getColumnValues('order_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getOrders($registryId)
    {
        $orderIds = $this->getOrderIds($registryId);
        $orderCollection = [];
        if (count($orderIds)) {
            $orderCollection = $this->orderCollectionFactory->create();
            $orderCollection->addFieldToFilter('entity_id', ['in' => $orderIds]);
        }

        return $orderCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function getWishes($registryId)
    {
        /** @var WishCollection $wishCollection */
        $wishes          = [];
        $wishIds         = [];
        $orderCollection = $this->getOrders($registryId);
        $wishCollection  = $this->wishCollectionFactory->create();

        /** @var Order $order */
        foreach ($orderCollection as $order) {
            $wishIds[] = $order->getGiftMessageId();
            foreach ($order->getAllItems() as $item) {
                $wishIds[] = $item->getGiftMessageId();
            }
        }

        if (!empty($wishIds)) {
            $wishCollection->addFieldToFilter('gift_message_id', $wishIds);
            $wishes = $wishCollection->getItems();
        }

        return $wishes;
    }
}
