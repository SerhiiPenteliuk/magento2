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



namespace Mirasvit\Giftr\Block\Item;

class Options extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * @var \Magento\Catalog\Helper\Product\ConfigurationPool
     */
    protected $_helperPool;

    /**
     * List of product options rendering configurations by product type
     *
     * @var array
     */
    protected $_optionsCfg = [
        'default' => [
            'helper' => 'Magento\Catalog\Helper\Product\Configuration',
            'template' => 'Mirasvit_Giftr::item/options.phtml'
        ]
    ];

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Catalog\Helper\Product\ConfigurationPool $helperPool
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Helper\Product\ConfigurationPool $helperPool,
        array $data = []
    ) {
        $this->_helperPool = $helperPool;
        parent::__construct($context, $data);
    }

    /**
     * Initialize block
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        //$this->context->getEventManager()->dispatch('product_option_renderer_init', ['block' => $this]);
    }

    /**
     * Adds config for rendering product type options.
     *
     * @param string      $productType
     * @param string      $helperName
     * @param null|string $template
     *
     * @return \Mirasvit\Giftr\Block\Item\Options
     */
    public function addOptionsRenderCfg($productType, $helperName, $template = null)
    {
        $this->_optionsCfg[$productType] = ['helper' => $helperName, 'template' => $template];

        return $this;
    }

    /**
     * Get item options renderer config.
     *
     * @param string $productType
     *
     * @return array|null
     */
    public function getOptionsRenderCfg($productType)
    {
        if (isset($this->_optionsCfg[$productType])) {
            return $this->_optionsCfg[$productType];
        } elseif (isset($this->_optionsCfg['default'])) {
            return $this->_optionsCfg['default'];
        } else {
            return;
        }
    }

    /**
     * Retrieve product configured options.
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getConfiguredOptions()
    {
        $item = $this->getItem();
        $data = $this->getOptionsRenderCfg($item->getProduct()->getTypeId());
        if (empty($data['helper']) ||
            !$this->_helperPool->get($data['helper']) instanceof
                \Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Helper for gift registry options rendering doesn't implement required interface.")
            );
        }

        return $this->_helperPool->get($data['helper'])->getOptions($item);
    }

    /**
     * Retrieve block template.
     *
     * @return string
     */
    public function getTemplate()
    {
        $template = parent::getTemplate();
        if ($template) {
            return $template;
        }

        $item = $this->getItem();
        $data = $this->getOptionsRenderCfg($item->getProduct()->getTypeId());
        if (empty($data['template'])) {
            $data = $this->getOptionsRenderCfg('default');
        }

        return empty($data['template']) ? '' : $data['template'];
    }

    /**
     * Render block html.
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->setOptionList($this->getConfiguredOptions());

        return parent::_toHtml();
    }
}
