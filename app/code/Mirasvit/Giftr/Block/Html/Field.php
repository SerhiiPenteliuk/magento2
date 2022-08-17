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



namespace Mirasvit\Giftr\Block\Html;

/**
 * @method \Mirasvit\Giftr\Model\Field getField() return current field model
 * @method int getSectionId() return id of current section
 * @method int getIsSystem()
 * @method \Mirasvit\Giftr\Model\Registry getRegistry()
 */
abstract class Field extends \Magento\Framework\View\Element\AbstractBlock implements
    \Magento\Framework\View\Element\BlockInterface
{
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $context;

    /**
     * @var array
     */
    private $enteredData = [];

    /**
     * @var null
     */
    protected $form = null;
    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    private $formFactory;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Mirasvit\Giftr\Helper\Data
     */
    private $giftrData;
    /**
     * @var \Magento\Framework\View\Element\Html\Date
     */
    protected $dateElement;
    /**
     * @var \Magento\Framework\Data\Form\ElementFactory
     */
    protected $elementFactory;
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @param \Magento\Framework\Locale\ResolverInterface      $localeResolver
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param \Magento\Framework\Data\FormFactory              $formFactory
     * @param \Magento\Framework\Data\Form\ElementFactory      $elementFactory
     * @param \Magento\Framework\View\Element\Html\Date        $dateElement
     * @param \Mirasvit\Giftr\Helper\Data                      $giftrData
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Data\Form\ElementFactory $elementFactory,
        \Magento\Framework\View\Element\Html\Date $dateElement,
        \Mirasvit\Giftr\Helper\Data $giftrData,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->localeResolver = $localeResolver;
        $this->customerSession = $customerSession;
        $this->formFactory = $formFactory;
        $this->elementFactory = $elementFactory;
        $this->dateElement = $dateElement;
        $this->giftrData = $giftrData;
        $this->context = $context;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    abstract protected function getElement();

    /**
     * @return bool
     */
    public function isSystem()
    {
        return (bool)$this->getField()->getIsSystem();
    }

    /**
     * Return data from registry or entered into the form(from session) or from customer model by key
     *
     * @param  string $key
     *
     * @return string|null
     */
    public function getEnteredData($key)
    {
        $value = ($this->IsSystem())
            ? $this->getRegistry()->getData($key)
            : $this->getRegistry()->getValueByCode($key);

        if (!strlen($value)) {
            if (empty($this->enteredData)) {
                $this->enteredData = $this->customerSession
                    ->getData('sharing_form', true);
            }

            if (!empty($this->enteredData) && isset($this->enteredData[$key])) {
                $value = $this->enteredData[$key];
            }
        }

        if ((null === $value || is_string($value) && !strlen($value)) && $this->isSystem()) {
            $value = $this->customerSession->getCustomer()->getData($key);
        }

        return $value;
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        return $this->getElement()
            ->setRequired($this->isRequired())
            ->setForm($this->getForm())
            ->toHtml();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->getField()->getCode();
    }

    /**
     * @return null|string
     */
    public function getTitle()
    {
        return $this->getField()->getName();
    }

    /**
     * @return string
     */
    public function getName()
    {
        $name = $this->getId();
        if (!$this->IsSystem()) {
            $name = 'section[' . $this->getSectionId() . '][field][' . $name . ']';
        }

        return $name;
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return $this->getField()->getDescription();
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return (bool) $this->getField()->getIsRequired();
    }

    /**
     * @return null|string
     */
    protected function getValue()
    {
        return $this->getEnteredData($this->getId());
    }

    /**
     * @return string
     */
    protected function getClass()
    {
        return $this->getField()->getClass();
    }

    /**
     * @return \Magento\Framework\Data\Form|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getForm()
    {
        if (null === $this->form) {
            $this->form = $this->formFactory->create();
        }

        return $this->form;
    }
}
