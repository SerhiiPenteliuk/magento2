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



namespace Mirasvit\Giftr\Block\Adminhtml\Sales\Order\View;

class Giftoptions extends \Magento\GiftMessage\Block\Adminhtml\Sales\Order\View\Giftoptions
{
    /**
     * @var \Magento\Backend\Block\Template\Context
     */
    private $context;

    /**
     * @var \Mirasvit\Giftr\Model\RegistryFactory
     */
    private $registryFactory;

    /**
     * @var null|\Mirasvit\Giftr\Model\Registry
     */
    private $registry = null;

    /**
     * @param \Mirasvit\Giftr\Model\RegistryFactory $registryFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Mirasvit\Giftr\Model\RegistryFactory $registryFactory,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->registryFactory = $registryFactory;
        $this->context = $context;
        parent::__construct($context, $data);
    }

    /**
     * @return null|\Mirasvit\Giftr\Model\Registry
     */
    public function getRegistry()
    {
        if ($this->registry === null) {
            $this->registry = $this->registryFactory->create()->loadByOrder($this->getOrder()->getId());
        }

        return $this->registry;
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getParentBlock()->getOrder();
    }

    /**
     * Is order associated with the gift registry.
     *
     * @return bool
     */
    public function isAllowed()
    {
        $result = false;
        if ($this->getRegistry() && $this->getRegistry()->getId()) {
            $result = true;
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return __('Gift Registry <strong>"%1"</strong>', $this->getRegistry()->getName());
    }

    /**
     * @return string
     */
    public function getRegistrantName()
    {
        return sprintf('%s %s', $this->getRegistry()->getFirstname(), $this->getRegistry()->getLastname());
    }

    /**
     * @return string
     */
    public function getCoRegistrantName()
    {
        return sprintf('%s %s', $this->getRegistry()->getCoFirstname(), $this->getRegistry()->getCoLastname());
    }

    /**
     * @return bool|string
     */
    public function getEventDate()
    {
        return date('F d, Y', strtotime($this->getRegistry()->getEventAt()));
    }

    /**
     * @return string
     */
    public function getRegistryLink()
    {
        return sprintf('<a href="%s">%s</a>', $this->getUrl(
                'giftr/registry/edit',
                array('id' => $this->getRegistry()->getId())
            ),
            $this->getRegistry()->getName()
        );
    }
}