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

abstract class Share extends Action
{
    /**
     * @var \Mirasvit\Giftr\Model\RegistryFactory
     */
    protected $registryFactory;

    /**
     * @var \Mirasvit\Giftr\Helper\Mail
     */
    protected $giftrMail;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

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
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @param \Magento\Framework\Data\Form\FormKey\Validator    $formKeyValidator
     * @param \Mirasvit\Giftr\Model\RegistryFactory             $registryFactory
     * @param \Mirasvit\Giftr\Helper\Mail                       $giftrMail
     * @param \Magento\Customer\Model\Url                       $customerUrl
     * @param \Magento\Framework\Registry                       $registry
     * @param \Magento\Customer\Model\Session                   $customerSession
     * @param \Magento\Framework\App\Action\Context             $context
     */
    public function __construct(
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Mirasvit\Giftr\Model\RegistryFactory $registryFactory,
        \Mirasvit\Giftr\Helper\Mail $giftrMail,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->_formKeyValidator = $formKeyValidator;
        $this->registryFactory = $registryFactory;
        $this->giftrMail = $giftrMail;
        $this->customerUrl = $customerUrl;
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->context = $context;
        $this->eventManager = $context->getEventManager();
        $this->resultFactory = $context->getResultFactory();
        parent::__construct($context);
    }

    /**
     * @return \Magento\Customer\Model\Session
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
        if ($action != 'external' && $action != 'postexternal') {
            if (!$this->customerSession->authenticate()) {
                $this->getActionFlag()->set('', self::FLAG_NO_DISPATCH, true);
            }
        }

        return parent::dispatch($request);
    }

    /**
     * @return \Mirasvit\Giftr\Model\Registry|bool
     */
    protected function _initRegistry()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $registry = $this->registryFactory->create()->load($id);
            if ($registry->getId()) {
                $this->registry->register('current_registry', $registry);

                return $registry;
            } else {
                $this->messageManager->addError(__('There was a problem initializing the gift registry'));
            }
        } else {
            $this->messageManager->addNotice(__('Please select a registry'));
        }

        return false;
    }
}
