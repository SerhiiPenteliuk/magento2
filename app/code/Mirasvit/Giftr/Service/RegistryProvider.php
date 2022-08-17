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



namespace Mirasvit\Giftr\Service;


use Mirasvit\Giftr\Api\Service\RegistryProviderInterface;

class RegistryProvider implements RegistryProviderInterface
{
    /**
     * @var \Mirasvit\Giftr\Model\Registry
     */
    protected $registry;

    /**
     * @var \Mirasvit\Giftr\Model\RegistryFactory
     */
    protected $registryFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @param \Mirasvit\Giftr\Model\RegistryFactory $registryFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Mirasvit\Giftr\Model\RegistryFactory $registryFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->request = $request;
        $this->registryFactory = $registryFactory;
        $this->customerSession = $customerSession;
        $this->messageManager = $messageManager;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getRegistry($registryId = null, $shared = false)
    {
        if ($this->registry) {
            return $this->registry;
        }

        try {
            if (!$registryId) {
                $registryId = $this->request->getParam('registry_id');
            }
            $customerId = $this->customerSession->getCustomerId();
            $registry = $this->registryFactory->create();

            /*if (!$registryId && !$customerId) {
                return $registry;
            }*/

            if ($registryId) {
                $registry->load($registryId);
            } elseif ($this->request->getParam('uid')) {
                $registry->loadByUid($this->request->getParam('uid'));
            }

            if (!$registry->getId() || ($registry->getCustomerId() != $customerId && !$shared)) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(
                    __('The requested Gift Registry doesn\'t exist.')
                );
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return false;
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t create the Gift Registry right now.'));
            return false;
        }
        $this->registry = $registry;

        return $registry;
    }
}