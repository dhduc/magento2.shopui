<?php
/**
 * Copyright © 2015 Smart. All rights reserved.
 */

namespace Smart\Magerun\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
	
        $installer = $setup;

        $installer->startSetup();

		/**
         * Create table 'magerun_magerun'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('magerun_magerun')
        )
		->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'magerun_magerun'
        )
		->addColumn(
            'title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Title'
        )
		->addColumn(
            'command',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Command'
        )
		->addColumn(
            'option',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Option'
        )
		->addColumn(
            'describe',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Describe'
        )
		->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Status'
        )
		->addColumn(
            'running_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false],
            'Running at'
        )
		/*{{CedAddTableColumn}}}*/
		
		
        ->setComment(
            'Smart Magerun magerun_magerun'
        );
		
		$installer->getConnection()->createTable($table);
		/*{{CedAddTable}}*/

        $installer->endSetup();

    }
}
