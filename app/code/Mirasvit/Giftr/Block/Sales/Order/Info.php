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



namespace Mirasvit\Giftr\Block\Sales\Order;

class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Mirasvit\Giftr\Model\RegistryFactory
     */
    protected $registryFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $context;

    /**
     * @param \Mirasvit\Giftr\Model\RegistryFactory            $registryFactory
     * @param \Magento\Framework\Registry                      $registry
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array                                            $data
     */
    public function __construct(
        \Mirasvit\Giftr\Model\RegistryFactory $registryFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->registryFactory = $registryFactory;
        $this->registry = $registry;
        $this->context = $context;
        parent::__construct($context, $data);
    }

    /**
     * @var \Mirasvit\Giftr\Model\Registry
     */
    protected $_registry = null;

    /**
     * @return null|\Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->registry->registry('current_order');
    }

    /**
     * @return \Magento\Core\Model\Abstract|\Mirasvit\Giftr\Model\Registry
     */
    public function getRegistry()
    {
        if (null === $this->_registry) {
            $this->_registry = $this->registryFactory->create()->loadByOrder($this->getOrder()->getId());
        }

        return $this->_registry;
    }

    /**
     * Is order associated with the gift registry.
     *
     * @return bool
     */
    public function isAllowed()
    {
        $result = false;
        if ($this->getRegistry()->getId()) {
            $result = true;
        }

        return $result;
    }

    /**
     * @return bool|string
     */
    public function getEventDate()
    {
        return date('F d Y', strtotime($this->getRegistry()->getEventAt()));
    }
}
