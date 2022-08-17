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



namespace Mirasvit\Giftr\Block\Adminhtml\Item;


use Magento\Backend\Block\Widget\Form\Container;

class NewItem extends Container
{
    protected function _construct()
    {
        parent::_construct();

        $this->_blockGroup = 'Mirasvit_Giftr';
        $this->_controller = 'adminhtml_item';
        $this->_mode = 'newItem';
        $this->_headerText = __('Add Product');

        $this->removeButton('reset');
        $this->removeButton('back');
        $this->buttonList->update('save', 'label', __('Add Product'));
        $this->buttonList->update('save', 'class', 'action-primary add-product');
        $this->buttonList->update('save', 'id', 'add_button');
        $this->buttonList->update('save', 'region', 'footer');
        $this->buttonList->update('save', 'data_attribute', [
            'bind' => 'click: $root.save'
        ]);
    }
}