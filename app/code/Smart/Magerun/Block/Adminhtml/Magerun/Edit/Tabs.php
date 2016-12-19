<?php
namespace Smart\Magerun\Block\Adminhtml\Magerun\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
		
        parent::_construct();
        $this->setId('checkmodule_magerun_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Magerun Information'));
    }
}