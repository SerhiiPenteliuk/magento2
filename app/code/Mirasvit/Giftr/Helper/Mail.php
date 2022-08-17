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



namespace Mirasvit\Giftr\Helper;

class Mail extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Mirasvit\Giftr\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    protected $context;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    protected $blockFactory;

    /**
     * @var array
     */
    public $emails = [];
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    private $inlineTranslation;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @param \Magento\Framework\View\Element\BlockFactory          $blockFactory
     * @param \Mirasvit\Giftr\Model\Config                          $config
     * @param \Magento\Framework\App\Helper\Context                 $context
     * @param \Magento\Store\Model\StoreManagerInterface            $storeManager
     * @param \Magento\Framework\Registry                           $registry
     * @param \Magento\Framework\Mail\Template\TransportBuilder     $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface    $inlineTranslation
     */
    public function __construct(
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Mirasvit\Giftr\Model\Config $config,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
    ) {
        $this->blockFactory = $blockFactory;
        $this->config = $config;
        $this->context = $context;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        parent::__construct($context);
    }

    /**
     * @return \Mirasvit\Giftr\Model\Config
     */
    protected function getConfig()
    {
        return $this->config;
    }

    /**
     * @return string
     */
    protected function getSender()
    {
        return 'general';
    }

    /**
     * @param string $templateName
     * @param string $senderName
     * @param string $senderEmail
     * @param string $recipientEmail
     * @param null $recipientName
     * @param array $variables
     * @return bool
     * @throws \Magento\Framework\Exception\MailException
     */
    protected function send(
        $templateName,
        $senderName,
        $senderEmail,
        $recipientEmail,
        $recipientName = null,
        $variables = []
    ) {
        if ($templateName == 'none' || !$senderEmail || !$recipientEmail) {
            return false;
        }

        $this->inlineTranslation->suspend();
        $this->transportBuilder
            ->setTemplateIdentifier($templateName)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($variables);

        $this->transportBuilder
            ->setFrom(
                [
                    'name' => $senderName,
                    'email' => $senderEmail,
                ]
            )
            ->addTo($recipientEmail, $recipientName)
            ->setReplyTo($senderEmail);

        $transport = $this->transportBuilder->getTransport();

        /* @var \Magento\Framework\Mail\Transport $transport */
        $transport->sendMessage();

        $this->inlineTranslation->resume();
    }

    /**
     * @param string $recipientEmail
     * @param string $recipientName
     * @param array $variables
     * @return bool
     */
    public function sendNotificationOwnerEmail($recipientEmail, $recipientName = '', $variables = [])
    {
        $templateName = $this->getConfig()->getNotificationOwnerEmailTemplate();
        if ($templateName == 'none') {
            return false;
        }
        $senderName = $this->context->getScopeConfig()->getValue("trans_email/ident_{$this->getSender()}/name");
        $senderEmail = $this->context->getScopeConfig()->getValue("trans_email/ident_{$this->getSender()}/email");
        $this->send($templateName, $senderName, $senderEmail, $recipientEmail, $recipientName, $variables);
    }

    /**
     * Send giftr sharing email template.
     *
     * @param string $recipientEmail
     * @param array  $variables      - variables for use in email template
     *
     * @return bool
     */
    public function sendNotificationSharingEmailTemplate($recipientEmail, $variables = [])
    {
        $templateName = $this->getConfig()->getNotificationSharingEmailTemplate();
        if ($templateName == 'none') {
            return false;
        }
        $registry = $this->registry->registry('current_registry');
        $giftrBlock = $this->blockFactory->createBlock('\Mirasvit\Giftr\Block\Mail\Items')
            ->setItemCollection($registry->getItemCollection())
            ->toHtml();

        $variables = array_merge($variables, [
            'salable' => $registry->isSalable() ? 'yes' : '',
            'items' => $giftrBlock,
            'registry' => $registry,
            'addAllLink' => ''
        ]);
        $senderName = $this->config->getNotificationSenderName();
        $senderEmail = $this->config->getNotificationSenderEmail();
        $this->send($templateName, $senderName, $senderEmail, $recipientEmail, null, $variables);
    }

    /**
     * @param string $recipientEmail
     * @param string $recipientName
     * @param array $variables
     * @return bool
     */
    public function sendNotificationOutOfStockItemEmail($recipientEmail, $recipientName = '', $variables = [])
    {
        $templateName = $this->getConfig()->getNotificationOutOfStockEmailTemplate();
        if ($templateName == 'none') {
            return false;
        }
        $senderName = $this->context->getScopeConfig()->getValue("trans_email/ident_{$this->getSender()}/name");
        $senderEmail = $this->context->getScopeConfig()->getValue("trans_email/ident_{$this->getSender()}/email");
        $this->send($templateName, $senderName, $senderEmail, $recipientEmail, $recipientName, $variables);
    }

    /**
     * @return bool
     */
    public function sendNotificationUpdateEmail()
    {
        $templateName = $this->getConfig()->getNotificationUpdateEmailTemplate();
        if ($templateName == 'none') {
            return false;
        }
        $recipientEmail = 'john_test@example.com';
        $recipientName = 'John Doe';
        $customer = new \Magento\Framework\DataObject(['name' => 'John Doe', 'email' => 'john_test@example.com']);
        $variables = [
            'customer' => $customer,
        ];
        $senderName = $this->context->getScopeConfig()->getValue("trans_email/ident_{$this->getSender()}/name");
        $senderEmail = $this->context->getScopeConfig()->getValue("trans_email/ident_{$this->getSender()}/email");
        $this->send($templateName, $senderName, $senderEmail, $recipientEmail, $recipientName, $variables);
    }
}
