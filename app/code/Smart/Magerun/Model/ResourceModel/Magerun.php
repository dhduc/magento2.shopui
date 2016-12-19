<?php
/**
 * Copyright © 2015 Smart. All rights reserved.
 */
namespace Smart\Magerun\Model\ResourceModel;

/**
 * Magerun resource
 */
class Magerun extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('magerun_magerun', 'id');
    }

  
}
