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



namespace Mirasvit\Giftr\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Mirasvit\Core\Block\Adminhtml\AbstractMenu;

class Menu extends AbstractMenu
{
    /**
     * {@inheritdoc}
     */
    public function __construct(
        Context $context
    ) {
        $this->visibleAt(['giftr']);

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function buildMenu()
    {
        $this->addItem([
            'resource' => 'Mirasvit_Giftr::giftr_registry',
            'title'    => __('Registries'),
            'url'      => $this->urlBuilder->getUrl('giftr/registry'),
        ])->addItem([
            'resource' => 'Mirasvit_Giftr::giftr_dictionary_type',
            'title'    => __('Event Types'),
            'url'      => $this->_urlBuilder->getUrl('giftr/type'),
        ])->addItem([
            'resource' => 'Mirasvit_Giftr::giftr_dictionary_priority',
            'title'    => __('Item Priorities'),
            'url'      => $this->_urlBuilder->getUrl('giftr/priority'),
        ])->addItem([
            'resource' => 'Mirasvit_Giftr::giftr_dictionary_section',
            'title'    => __('Registry Form Sections'),
            'url'      => $this->_urlBuilder->getUrl('giftr/section'),
        ])->addItem([
            'resource' => 'Mirasvit_Giftr::giftr_dictionary_field',
            'title'    => __('Registry Form Fields'),
            'url'      => $this->_urlBuilder->getUrl('giftr/field'),
        ])->addSeparator();

        $this->addItem([
            'resource' => 'Mirasvit_Giftr::giftr_settings',
            'title'    => __('Settings'),
            'url'      => $this->_urlBuilder->getUrl('adminhtml/system_config/edit/section/giftr'),
        ]);

        return $this;
    }
}
