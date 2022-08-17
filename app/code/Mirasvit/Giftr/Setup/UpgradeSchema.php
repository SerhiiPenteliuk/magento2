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

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $setup->getConnection()->addColumn($setup->getTable('mst_giftr_type'), 'description', 
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'comment' => 'Description'
                ]);

            $setup->getConnection()->addColumn($setup->getTable('mst_giftr_type'), 'event_icon', 
                [
                    'type' => Table::TYPE_BLOB,
                    'nullable' => false,
                    'comment' => 'Event Icon'
                ]);

            $setup->getConnection()->addColumn($setup->getTable('mst_giftr_type'), 'event_image', 
                [
                    'type' => Table::TYPE_BLOB,
                    'nullable' => false,
                    'comment' => 'Event Image'
                ]);
        }
    }
}
