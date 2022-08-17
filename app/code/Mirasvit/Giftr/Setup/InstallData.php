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



namespace Mirasvit\Giftr\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * @var \Mirasvit\Giftr\Model\Type
     */
    protected $type;

    /**
     * @var \Mirasvit\Giftr\Model\Section
     */
    protected $section;

    /**
     * @var \Mirasvit\Giftr\Model\Field
     */
    protected $field;

    /**
     * InstallData constructor.
     * @param \Mirasvit\Giftr\Model\Type $type
     * @param \Mirasvit\Giftr\Model\Section $section
     * @param \Mirasvit\Giftr\Model\Field $field
     */
    public function __construct(
        \Mirasvit\Giftr\Model\Type $type,
        \Mirasvit\Giftr\Model\Section $section,
        \Mirasvit\Giftr\Model\Field $field
    ) {
        $this->type = $type;
        $this->section = $section;
        $this->field = $field;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->insertTypes($setup);
        $this->insertPriorities($setup);
        $this->insertSections($setup);
        $fields = $this->insertFields($setup);

        $this->bindFieldsWithSections($setup, $fields);
        $this->bindSectionsWithTypes($setup);
    }

    /**
     * Populate table `mst_giftr_section_field`
     * Association between fields and sections depends on fields indexes (array keys)
     * 0  - 6  to General
     * 7  - 10 to Registrant
     * 11 - 14 to Co-Registrant.
     *
     * @param ModuleDataSetupInterface $setup
     * @param array                    $fields
     * @return void
     */
    private function bindFieldsWithSections(ModuleDataSetupInterface $setup, $fields)
    {
        $sectionIdGeneral = null;
        $sectionIdRegistrant = null;
        $sectionIdCoRegistrant = null;
        foreach ($this->field->getCollection() as $field) {
            $row = null;
            $sectionId = null;
            $idx = array_search($field->getCode(), array_column($fields, 'code'));

            if ($idx <= 6) {
                if ($sectionIdGeneral === null) {
                    $query = 'SELECT `section_id` FROM '.$setup->getTable('mst_giftr_section').
                        ' WHERE `code` = "general" AND `is_system` = 1';
                    $sectionIdGeneral = $setup->getConnection()->fetchOne($query);
                }
                $sectionId = $sectionIdGeneral;
            } elseif ($idx <= 10) {
                if ($sectionIdRegistrant === null) {
                    $query = 'SELECT `section_id` FROM '.$setup->getTable('mst_giftr_section').
                        ' WHERE `code` = "registrant" AND `is_system` = 1';
                    $sectionIdRegistrant = $setup->getConnection()->fetchOne($query);
                }
                $sectionId = $sectionIdRegistrant;
            } elseif ($idx <= 14) {
                if ($sectionIdCoRegistrant === null) {
                    $query = 'SELECT `section_id` FROM '.$setup->getTable('mst_giftr_section').
                        ' WHERE `code` = "co_registrant" AND `is_system` = 1';
                    $sectionIdCoRegistrant = $setup->getConnection()->fetchOne($query);
                }
                $sectionId = $sectionIdCoRegistrant;
            }

            if ($sectionId !== null) {
                $row = ['sf_section_id' => $sectionId, 'sf_field_id' => $field->getId()];
                $setup->getConnection()->insertForce($setup->getTable('mst_giftr_section_field'), $row);
            }
        }
    }

    /**
     * Populate table `mst_giftr_type_section`
     * Association between types and sections.
     *
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function bindSectionsWithTypes(ModuleDataSetupInterface $setup)
    {
        $data = [];
        foreach ($this->section->getCollection() as $section) {
            foreach ($this->type->getCollection() as $type) {
                $data[] = [$type->getId(), $section->getId()];
            }
        }
        $setup->getConnection()->insertArray(
            $setup->getTable('mst_giftr_type_section'),
            ['ts_type_id', 'ts_section_id'],
            $data
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function insertTypes(ModuleDataSetupInterface $setup)
    {
        $data = [
            [
                'type_id' => 1,
                'name' => 'Birthday',
                'is_active' => true,
                'sort_order' => 0,
            ],
            [
                'type_id' => 2,
                'name' => 'Wedding',
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'type_id' => 3,
                'name' => 'Baby Shower',
                'is_active' => true,
                'sort_order' => 20,
            ],
        ];
        $setup->getConnection()->insertArray($setup->getTable('mst_giftr_type'), array_keys($data[0]), $data);
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function insertPriorities(ModuleDataSetupInterface $setup)
    {
        $data = [
            [
                'priority_id' => 1,
                'name' => 'High',
                'sort_order' => 1,
            ],
            [
                'priority_id' => 2,
                'name' => 'Medium',
                'sort_order' => 10,
            ],
            [
                'priority_id' => 3,
                'name' => 'Low',
                'sort_order' => 20,
            ],
        ];
        $setup->getConnection()->insertArray($setup->getTable('mst_giftr_priority'), array_keys($data[0]), $data);
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function insertSections(ModuleDataSetupInterface $setup)
    {
        $data = [
            [
                'section_id' => 1,
                'name' => 'General Information',
                'code' => 'general',
                'is_active' => 1,
                'sort_order' => 0,
                'is_system' => 1,
            ],
            [
                'section_id' => 2,
                'name' => 'Registrant',
                'code' => 'registrant',
                'is_active' => 1,
                'sort_order' => 5,
                'is_system' => 1,
            ],
            [
                'section_id' => 3,
                'name' => 'Co-Registrant',
                'code' => 'co_registrant',
                'is_active' => 1,
                'sort_order' => 10,
                'is_system' => 1,
            ],
            [
                'section_id' => 4,
                'name' => 'Shipping Address',
                'code' => 'shipping',
                'is_active' => 1,
                'sort_order' => 15,
                'is_system' => 1,
            ],
        ];
        $setup->getConnection()->insertArray($setup->getTable('mst_giftr_section'), array_keys($data[0]), $data);
    }

    /**
     * @param ModuleDataSetupInterface $setup
     *
     * @return array
     * @SuppressWarnings(PHPMD)
     */
    private function insertFields(ModuleDataSetupInterface $setup)
    {
        $data = [
            [
                'field_id' => 1,
                'name' => 'Gift Registry Title',
                'code' => 'name',
                'type' => 'text',
                'values' => null,
                'description' => null,
                'is_required' => true,
                'is_active' => 1,
                'sort_order' => 0,
                'is_system' => 1,
            ],
            [
                'field_id' => 2,
                'name' => 'Description',
                'code' => 'description',
                'type' => 'textarea',
                'values' => null,
                'description' => null,
                'is_required' => false,
                'is_active' => 1,
                'sort_order' => 5,
                'is_system' => 1,
            ],
            [
                'field_id' => 3,
                'name' => 'Location',
                'code' => 'location',
                'type' => 'text',
                'values' => null,
                'description' => null,
                'is_required' => false,
                'is_active' => 1,
                'sort_order' => 10,
                'is_system' => 1,
            ],
            [
                'field_id' => 4,
                'name' => 'Date',
                'code' => 'event_at',
                'type' => 'date',
                'values' => null,
                'description' => null,
                'is_required' => false,
                'is_active' => 1,
                'sort_order' => 15,
                'is_system' => 1,
            ],
            [
                'field_id' => 5,
                'name' => 'Active',
                'code' => 'is_active',
                'type' => 'select',
                'values' => serialize(["1|Yes\n0|No"]),
                'description' => null,
                'is_required' => true,
                'is_active' => 1,
                'sort_order' => 20,
                'is_system' => 1,
            ],
            [
                'field_id' => 6,
                'name' => 'Visibility',
                'code' => 'is_public',
                'type' => 'select',
                'values' => serialize(["1|Open for public view\n0|Open for private view only"]),
                'description' => null,
                'is_required' => false,
                'is_active' => 1,
                'sort_order' => 25,
                'is_system' => 1,
            ],
            [
                'field_id' => 7,
                'name' => 'Image',
                'code' => 'image',
                'type' => 'image',
                'values' => null,
                'description' => null,
                'is_required' => false,
                'is_active' => 1,
                'sort_order' => 30,
                'is_system' => 1,
            ],
            [
                'field_id' => 8,
                'name' => 'First Name',
                'code' => 'firstname',
                'type' => 'text',
                'values' => null,
                'description' => null,
                'is_required' => false,
                'is_active' => 1,
                'sort_order' => 0,
                'is_system' => 1,
            ],
            [
                'field_id' => 9,
                'name' => 'Middle Name/Initial',
                'code' => 'middlename',
                'type' => 'text',
                'values' => null,
                'description' => null,
                'is_required' => false,
                'is_active' => 1,
                'sort_order' => 5,
                'is_system' => 1,
            ],
            [
                'field_id' => 10,
                'name' => 'Last Name',
                'code' => 'lastname',
                'type' => 'text',
                'values' => null,
                'description' => null,
                'is_required' => false,
                'is_active' => 1,
                'sort_order' => 10,
                'is_system' => 1,
            ],
            [
                'field_id' => 11,
                'name' => 'Email',
                'code' => 'email',
                'type' => 'text',
                'values' => null,
                'description' => null,
                'is_required' => false,
                'is_active' => 1,
                'sort_order' => 15,
                'is_system' => 1,
            ],
            [
                'field_id' => 12,
                'name' => 'First Name',
                'code' => 'co_firstname',
                'type' => 'text',
                'values' => null,
                'description' => null,
                'is_required' => false,
                'is_active' => 1,
                'sort_order' => 0,
                'is_system' => 1,
            ],
            [
                'field_id' => 13,
                'name' => 'Middle Name/Initial',
                'code' => 'co_middlename',
                'type' => 'text',
                'values' => null,
                'description' => null,
                'is_required' => false,
                'is_active' => 1,
                'sort_order' => 5,
                'is_system' => 1,
            ],
            [
                'field_id' => 14,
                'name' => 'Last Name',
                'code' => 'co_lastname',
                'type' => 'text',
                'values' => null,
                'description' => null,
                'is_required' => false,
                'is_active' => 1,
                'sort_order' => 10,
                'is_system' => 1,
            ],
            [
                'field_id' => 15,
                'name' => 'Email',
                'code' => 'co_email',
                'type' => 'text',
                'values' => null,
                'description' => null,
                'is_required' => false,
                'is_active' => 1,
                'sort_order' => 15,
                'is_system' => 1,
            ],
        ];
        $setup->getConnection()->insertArray($setup->getTable('mst_giftr_field'), array_keys($data[0]), $data);

        return $data;
    }
}
