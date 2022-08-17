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

class Registry extends \Magento\Backend\Block\Widget\Grid\Container
{
    const MODE_NEW = 'new';
    const MODE_EDIT = 'edit';

    protected function _construct()
    {
        parent::_construct();
        $this->_controller = 'adminhtml_registry';
        $this->_blockGroup = 'Mirasvit_Giftr';
        $this->_headerText = __('Registries');
        $this->removeButton('add');
    }

    /**
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/add');
    }

    /************************/
}
