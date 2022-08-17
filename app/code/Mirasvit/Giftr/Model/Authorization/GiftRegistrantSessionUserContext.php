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



namespace Mirasvit\Giftr\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Checkout\Model\Session;
use Mirasvit\Giftr\Model\RegistryFactory;

class GiftRegistrantSessionUserContext implements UserContextInterface
{
    const USER_TYPE_GIFT_REGISTRANT = 5;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var RegistryFactory
     */
    private $registryFactory;

    /**
     * @var null|\Mirasvit\Giftr\Model\Registry
     */
    private $registry = null;

    /**
     * GiftRegistrantSessionUserContext constructor.
     *
     * @param Session $session
     * @param RegistryFactory $registryFactory
     */
    public function __construct(
        Session $session,
        RegistryFactory $registryFactory
    ) {
        $this->session = $session;
        $this->registryFactory = $registryFactory;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        $userId = null;
        if ($this->getCheckoutSession()->getGiftrId()) {
            $userId = $this->getRegistry()->getCustomerId();
        }

        return $userId;
    }

    /**
     * @return int
     */
    public function getUserType()
    {
        if ($this->getUserId()) {
            return self::USER_TYPE_INTEGRATION;
        }
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    private function getCheckoutSession()
    {
        return $this->session;
    }

    /**
     * @return \Mirasvit\Giftr\Model\Registry
     */
    private function getRegistry()
    {
        if (null === $this->registry) {
            $this->registry = $this->registryFactory->create()->load($this->getCheckoutSession()->getGiftrId());
        }

        return $this->registry;
    }
}
