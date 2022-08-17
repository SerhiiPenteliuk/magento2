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



namespace Mirasvit\Giftr\Block\Html\Field;

class Image extends \Mirasvit\Giftr\Block\Html\Field
{
    /**
     * @return \Mirasvit\Core\Helper\Image|string|null
     */
    public function getValue()
    {
        $value = parent::getValue();
        if ($value) {
            $value = $this->getRegistry()->getImageUrl();
        }

        return $value;
    }

    /**
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getElement()
    {
        return $this->elementFactory
            ->create('\Magento\Framework\Data\Form\Element\Image', [
                'html_id'   => $this->getId(),
                'name'      => $this->getName(),
                'class'     => $this->getClass(),
                'value'     => $this->getValue(),
                'title'     => $this->getTitle(),
            ]);
    }
}
