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

class Share extends \Magento\Framework\View\Element\Template
{
    /**
     * @var null|array
     */
    private $enteredData = null;

    /**
     * @var null|\Mirasvit\Giftr\Model\Registry
     */
    //fixme
    //private $registry = null;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $context;

    /**
     * @param \Magento\Framework\Registry                      $registry
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->context = $context;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->registry = $this->registry->registry('current_registry');
    }

    /**
     * @return \Magento\Framework\Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * @param string $key
     * @return void
     */
    public function getEnteredData($key)
    {
        if (null === $this->enteredData) {
            $this->enteredData = $this->customerSession
                ->getData('sharing_form', true);
        }

        if (!$this->enteredData || !isset($this->enteredData[$key])) {
            return;
        } else {
            return $this->escapeHtml($this->enteredData[$key]);
        }
    }

    /**
     * @param string $key
     * @return string
     */
    public function getSocialImage($key)
    {
        return $this->getViewFileUrl('Mirasvit_Giftr::images') . DIRECTORY_SEPARATOR . $key;
    }

    /**
     * @return bool
     */
    public function isFacebookEnabled()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isGoogleEnabled()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isTwitterEnabled()
    {
        return true;
    }

    /**
     * @param string $type
     * @return null|string
     */
    public function getSocialShareUrl($type)
    {
        $url = null;
        $name = urlencode($this->getRegistry()->getName());
        $registryUrl = urlencode($this->getRegistry()->getViewUrl());
        $status = urlencode($this->getRegistry()->getName().' ('.$this->getRegistry()->getViewUrl().')');

        switch ($type) {
            case 'fb':
                $url = 'https://www.facebook.com/sharer/sharer.php?u='.$registryUrl.'&t='.$name;
                break;
            case 'google':
                $url = 'https://plus.google.com/share?url='.$registryUrl;
                break;
            case 'twitter':
                $url = 'http://twitter.com/home/?status='.$status;
                break;
        }

        return $url;
    }

    /**
     * Get form's action URL to share registry.
     *
     * @return string
     */
    public function getActionUrl()
    {
        return $this->getUrl('*/share/share/', ['id' => $this->registry->getId()]);
    }

    /**
     * @return string
     */
    public function getRegistryAccessUrl()
    {
        return $this->registry->getViewUrl();
    }

    /**
     * @return string
     */
    public function getFindRegistryBy()
    {
        return ($this->registry->getIsPublic()) ? 'your name' : 'registry id: <b>'.$this->registry->getUid().'</b>';
    }

    /**
     * @return array
     */
    public function getRegistryEventConfiguration()
    {
        return [
            '*' => [
                'Magento_Ui/js/core/app' => [
                    'components' => [
                        'giftr_share_form' => [
                            'component' => 'Mirasvit_Giftr/js/giftr',
                            'config' => [
                                'shareUrlFb' => $this->getSocialShareUrl('fb'),
                                'shareUrlGoogle' => $this->getSocialShareUrl('google'),
                                'shareUrlTwitter' => $this->getSocialShareUrl('twitter'),
                            ]
                        ],
                    ],
                ],
            ],
        ];
    }
}
