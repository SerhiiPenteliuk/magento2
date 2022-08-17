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

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;

abstract class Giftr extends Action
{
    /**
     * @var \Mirasvit\Giftr\Model\RegistryFactory
     */
    protected $registryFactory;

    /**
     * @var \Mirasvit\Giftr\Helper\Data
     */
    protected $giftrData;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * @param \Mirasvit\Giftr\Model\RegistryFactory $registryFactory
     * @param \Mirasvit\Giftr\Helper\Data           $giftrData
     * @param \Magento\Framework\Registry           $registry
     * @param \Magento\Customer\Model\Session       $customerSession
     * @param \Magento\Framework\Json\Helper\Data   $jsonEncoder
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Mirasvit\Giftr\Model\RegistryFactory $registryFactory,
        \Mirasvit\Giftr\Helper\Data $giftrData,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Json\Helper\Data $jsonEncoder,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->registryFactory = $registryFactory;
        $this->giftrData = $giftrData;
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->jsonEncoder = $jsonEncoder;
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
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $action = $this->getRequest()->getActionName();
        if ($action != 'external' &&
            $action != 'postexternal' &&
            $action != 'resume'
        ) {
            if (!$this->customerSession->authenticate()) {
                $this->getActionFlag()->set('', self::FLAG_NO_DISPATCH, true);
            }
        }

        return parent::dispatch($request);
    }

    /**
     * @return \Mirasvit\Giftr\Model\Registry
     */
    protected function _initRegistry()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $registry = $this->registryFactory->create()->load($id);
            if ($registry->getId() > 0) {
                $this->registry->register('current_registry', $registry);

                return $registry;
            }
        }
    }
}
