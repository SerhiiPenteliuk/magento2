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
 * @method \Mirasvit\Giftr\Model\Section getSection() return current section model
 * @method \Mirasvit\Giftr\Model\Registry getRegistry()
 * @method \Mirasvit\Giftr\Model\Type getType()
 */
class Section extends \Magento\Framework\View\Element\Template
{
    const SECTION_TEMPLATE_BASE_PATH = 'html/section/';

    /**
     * @var null
     */
    private $fieldCollection = null;
    /**
     * @var array
     */
    private $fields = [];

    protected function _construct()
    {
        $this->setTemplate('html/section.phtml');
        parent::_construct();
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        $template = self::SECTION_TEMPLATE_BASE_PATH . $this->getSection()->getCode() . '.phtml';
        if (!$this->getTemplateFile($template)) {
            $template = $this->_template;
        }

        return $template;
    }

    /**
     * return \Mirasvit\Giftr\Model\ResourceModel\Field\Collection
     * @return \Mirasvit\Giftr\Model\ResourceModel\Field\Collection|null
     */
    public function getFieldCollection()
    {
        if (null === $this->fieldCollection) {
            $this->fieldCollection = $this->getSection()->getFieldCollection();
        }

        return $this->fieldCollection;
    }

    /**
     * Instantiate field blocks and create child group for them
     */
    public function prepareSectionFields()
    {
        foreach ($this->getFieldCollection() as $field) {
            $fieldBlockName = $field->getCode().'_'.$field->getId();
            $fieldBlock = $this->getFieldBlockInstance($field->getType())
                ->setNameInLayout($fieldBlockName)
                ->setField($field)
                ->setSectionId($this->getSection()->getId())
                ->setRegistry($this->getRegistry());

            $this->fields[$fieldBlockName] = $fieldBlock;
        }
    }

    /**
     * Get group of field blocks
     *
     * @return array
     */
    public function getFields()
    {
        if (!$this->fields) {
            $this->prepareSectionFields();
        }

        return $this->fields;
    }

    /**
     * Create instance of field block depend on type
     *
     * @param string $type
     *
     * @return \Mirasvit\Giftr\Block\Html\Field
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getFieldBlockInstance($type)
    {
        if (null == $type || !is_string($type)) {
            throw new \Magento\Framework\Exception\LocalizedException('The block type is required');
        }

        return $this->getLayout()->createBlock('\Mirasvit\Giftr\Block\Html\Field\\' . ucfirst($type));
    }

    /**
     * Section title
     *
     * @return string
     */
    public function getName()
    {
        return $this->getSection()->getName();
    }
}
