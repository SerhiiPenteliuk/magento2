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



namespace Mirasvit\Giftr\Model\ResourceModel;

class Section extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Context
     */
    protected $context;

    /**
     * @var null
     */
    protected $resourcePrefix;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $resourcePrefix = null
    ) {
        $this->context = $context;
        $this->resourcePrefix = $resourcePrefix;
        parent::__construct($context, $resourcePrefix);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mst_giftr_section', 'section_id');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\AbstractModel
     */
    protected function loadFieldIds(\Magento\Framework\Model\AbstractModel $object)
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable('mst_giftr_section_field'))
            ->where('sf_section_id = ?', $object->getId());
        if ($data = $this->getConnection()->fetchAll($select)) {
            $array = [];
            foreach ($data as $row) {
                $array[] = $row['sf_field_id'];
            }
            $object->setData('field_ids', $array);
        }

        return $object;
    }

    /**
     * @param \Mirasvit\Giftr\Model\Section $object
     * @return void
     */
    protected function saveFieldIds($object)
    {
        $condition = $this->getConnection()->quoteInto('sf_section_id = ?', $object->getId());
        $this->getConnection()->delete($this->getTable('mst_giftr_section_field'), $condition);
        foreach ((array) $object->getData('field_ids') as $id) {
            $objArray = [
                'sf_section_id' => $object->getId(),
                'sf_field_id' => $id,
            ];
            $this->getConnection()->insert(
                $this->getTable('mst_giftr_section_field'), $objArray);
        }
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->getIsMassDelete()) {
            $this->loadFieldIds($object);
        }

        return parent::_afterLoad($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->getId()) {
            $object->setCreatedAt((new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));
        }
        $object->setUpdatedAt((new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));
        $this->addSystemFields($object);

        return parent::_beforeSave($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->getIsMassStatus()) {
            $this->saveFieldIds($object);
        }

        return parent::_afterSave($object);
    }

    /**
     * Always add to section required fields
     *
     * @param \Magento\Framework\Model\AbstractModel|\Mirasvit\Giftr\Model\Section $object
     *
     * @return \Magento\Framework\Model\AbstractModel
     */
    private function addSystemFields($object)
    {
        $fieldIds = array_merge(
            array_diff((array) $object->getFieldIds(), $object->getRelatedFieldCollection()->getAllIds()),
            $object->getRelatedFieldCollection()->getAllIds()
        );

        $object->addData(['field_ids' => $fieldIds]);

        return $object;
    }

    /************************/
}
