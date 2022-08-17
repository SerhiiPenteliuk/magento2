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



namespace Mirasvit\Giftr\Block\Registry;


use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Mirasvit\Giftr\Api\Service\Registry\OrderProviderInterface;
use Mirasvit\Giftr\Controller\RegistryConstants;

class Wish extends Template
{
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var OrderProviderInterface
     */
    private $orderProvider;

    /**
     * Wish constructor.
     *
     * @param Registry               $registry
     * @param OrderProviderInterface $orderProvider
     * @param Template\Context       $context
     * @param array                  $data
     *
     * @internal param WishCollectionFactory $wishCollectionFactory
     */
    public function __construct(
        Registry $registry,
        OrderProviderInterface $orderProvider,
        Template\Context $context,
        array $data
    ) {
        $this->registry = $registry;
        $this->orderProvider = $orderProvider;
        parent::__construct($context, $data);
    }

    /**
     * Get collection of gift messages related with current registry.
     *
     * @return \Magento\GiftMessage\Api\Data\MessageInterface[]
     */
    public function getWishes()
    {
        return $this->orderProvider->getWishes($this->registry->registry(RegistryConstants::CURRENT_REGISTRY)->getId());
    }
}
