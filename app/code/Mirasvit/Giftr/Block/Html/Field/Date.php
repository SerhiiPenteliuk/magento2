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

class Date extends \Mirasvit\Giftr\Block\Html\Field
{
    /**
     * Method returns HTML element object
     *
     * @return \Magento\Framework\View\Element\Html\Date
     */
    public function getElement()
    {
        $field = $this->dateElement->setData([
            'name' => $this->getName(),
            'id' => $this->getId(),
            'class' => $this->getClass(),
            'value' => $this->getDate(),
            'image' => $this->getViewFileUrl('Magento_Theme::calendar.png'),
            'date_format' => $this->getDateFormat(),
        ]);

        return $field;
    }

    /**
     * Get current date format pattern, depends on used locale
     *
     * @return string - e.g. 'd/MM/yyyy'
     */
    public function getDateFormat()
    {
        return $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
    }

    /**
     * Filter date value
     *
     * @return string
     */
    public function getDate()
    {
        return (new \Magento\Framework\Data\Form\Filter\Date(
            $this->getDateFormat(),
            $this->localeResolver
        ))->outputFilter($this->getValue());
    }
}
