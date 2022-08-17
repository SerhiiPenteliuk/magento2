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



namespace Mirasvit\Giftr\Model\System\Message;

class GiftrTypeInactive implements \Magento\Framework\Notification\MessageInterface
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendHelper;

    /**
     * @var \Mirasvit\Giftr\Model\Type
     */
    protected $_giftrTypeList;

    /**
     * GiftrTypeInactive constructor.
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Mirasvit\Giftr\Model\Type $giftrTypeList
     */
    public function __construct(
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Backend\Helper\Data $backendHelper,
        \Mirasvit\Giftr\Model\Type $giftrTypeList
    ) {
        $this->_authorization = $authorization;
        $this->_backendHelper = $backendHelper;
        $this->_giftrTypeList = $giftrTypeList;
    }

    /**
     * Get array of cache types which require data refresh
     *
     * @return array
     */
    protected function _getGiftrTypesForRefresh()
    {
        $output = [];
        foreach ($this->_giftrTypeList->getInactiveTypes() as $type) {
            $output[] = $type->getName();
        }
        return $output;
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return md5('giftr' . implode(':', $this->_getGiftrTypesForRefresh()));
    }

    /**
     * Check whether
     *
     * @return bool
     */
    public function isDisplayed()
    {
        return count($this->_getGiftrTypesForRefresh()) > 0;
    }

    /**
     * Retrieve message text
     *
     * @return string
     */
    public function getText()
    {
        $giftrTypes = implode(', ', $this->_getGiftrTypesForRefresh());
        $message = __('One or more of the Giftr Types are inactive: %1. ', $giftrTypes) . ' ';
        $url = $this->_backendHelper->getUrl('giftr/type',[]);
        $message .= __('Please go to <a href="%1">Giftr Event Types</a> and verify or delete inactive types.', $url);
        return $message;
    }

    /**
     * Retrieve problem management url
     *
     * @return string|null
     */
    public function getLink()
    {
        return $this->_backendHelper->getUrl('giftr/type',[]);
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        return \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL;
    }
}
