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

/**
 * @method string getBackUrl()
 */
class NewAction extends Form
{
    /**
     * Option that identifies creation of a new event type.
     */
    const NEW_EVENT_TYPE = 'new';

    /**
     * Step for the creation gift registry (first step).
     */
    const STEP_START = 'type_select_form';

    /**
     * Step for the creation gift registry (last step).
     */
    const STEP_FINISH = 'registry_form';

    /**
     * @var null|\Mirasvit\Giftr\Model\ResourceModel\Type\Collection
     */
    private $typeCollection = null;

    /**
     * Return collection of active types.
     *
     * @return \Mirasvit\Giftr\Model\ResourceModel\Type\Collection
     */
    public function getTypeCollection()
    {
        if (null === $this->typeCollection) {
            $this->typeCollection = $this->typeCollectionFactory->create()
                ->addIsActiveFilter();
        }

        return $this->typeCollection;
    }

    /**
     * Get current step of creation gift registry
     * Mull              - step of selecting type | last step
     * Self::STEP_FINISH - last step.
     *
     * @return string|null
     */
    public function getStep()
    {
        return $this->getRequest()->getParam('step');
    }

    /**
     * Get form URL depending on current step
     * self::STEP_FINISH - url to action "save"
     * no step           - url to action "new".
     *
     * @return string
     */
    public function getActionUrl()
    {
        $url = null;
        //if (!$this->getStep()) {
        if ($this->getStep() === self::STEP_START) {
            $url = $this->context->getUrlBuilder()->getUrl('*/*/new', ['_secure' => true]);
        } elseif ($this->getStep() == self::STEP_FINISH) {
            $url = $this->context->getUrlBuilder()->getUrl('*/*/save', ['_secure' => true]);
        }

        return $url;
    }

    /**
     * If current step is null or size of event types greater than 1 show content for select event type
     * Otherwise show main form for editing registry.
     *
     * @return $this
     */
    public function getCurrentContent()
    {
        $block = null;
            //if ($this->getTypeCollection()->getSize() > 1 && !$this->getStep()) {
        if ($this->getStep() === self::STEP_START) {
            $block = $this->getChildHtml('registry.new.step1');
            //} elseif ($this->getStep() == self::STEP_FINISH || $this->getTypeCollection()->getSize() == 1) {
        } elseif ($this->getStep() === self::STEP_FINISH) {
            $block = $this->getChildHtml('registry.new.step2');
        }

        return $block;
    }

    /**
     * Get option array of active types.
     *
     * @param bool $isCreating
     * @return array
     */
    public function getTypes($isCreating = false)
    {
        $types = $this->getTypeCollection()->toOptionArray(true);
        if ($isCreating) {
            $types[] = ['value' => self::NEW_EVENT_TYPE, 'label' => __('Create New')];
        }

        return $types;
    }

    /**
     * Is customers allowed to create new events or not.
     *
     * @return bool
     */
    public function isNewEventsAllowed()
    {
        return (bool) $this->_scopeConfig->getValue('giftr/general/allow_new_events',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $this->_storeManager->getWebsite()
        );
    }
}
