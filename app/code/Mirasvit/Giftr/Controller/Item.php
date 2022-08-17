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



namespace Mirasvit\Giftr\Controller;

use Mirasvit\Giftr\Api\Service\RegistryProviderInterface;
use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;

abstract class Item extends Action
{
    const SUCCESS = 'success';
    const CONFIRM = 'confirm';
    const ERROR = 'error';
    const LOGIN = 'login';
    const NOTICE = 'notice';

    /**
     * @var \Mirasvit\Giftr\Model\RegistryFactory
     */
    protected $registryFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Mirasvit\Giftr\Model\ItemFactory
     */
    protected $itemFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;
    /**
     * @var RegistryProviderInterface
     */
    private $registryProvider;

    /**
     * @param RegistryProviderInterface             $registryProvider
     * @param \Mirasvit\Giftr\Model\RegistryFactory $registryFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Mirasvit\Giftr\Model\ItemFactory     $itemFactory
     * @param \Magento\Framework\Registry           $registry
     * @param \Magento\Customer\Model\Session       $customerSession
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        RegistryProviderInterface $registryProvider,
        \Mirasvit\Giftr\Model\RegistryFactory $registryFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Mirasvit\Giftr\Model\ItemFactory $itemFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->registryProvider = $registryProvider;
        $this->registryFactory = $registryFactory;
        $this->productFactory = $productFactory;
        $this->itemFactory = $itemFactory;
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->context = $context;
        $this->resultFactory = $context->getResultFactory();
        parent::__construct($context);
    }

    /**
     * @return Session
     */
    protected function _getSession()
    {
        return $this->customerSession;
    }

    /**
     * @return $this|bool
     */
    protected function _initRegistry()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $registry = $this->registryProvider->getRegistry($id);
            if ($registry) {
                $this->registry->register('current_registry', $registry);

                return $registry;
            }
        } else {
            $this->messageManager->addNotice(__('Please select a registry'));
        }

        return false;
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $action = $this->getRequest()->getActionName();
        if (!in_array($action, ['external', 'postexternal', 'addtocart', 'allcart', 'add'])) {
            if (!$this->customerSession->authenticate()) {
                $this->getActionFlag()->set('', self::FLAG_NO_DISPATCH, true);
            }
        }

        return parent::dispatch($request);
    }
}
