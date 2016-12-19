<?php
namespace Smart\Magerun\Block\Adminhtml;
class Magerun extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
		
        $this->_controller = 'adminhtml_magerun';/*block grid.php directory*/
        $this->_blockGroup = 'Smart_Magerun';
        $this->_headerText = __('Magerun');
        $this->_addButtonLabel = __('Add New Entry'); 
        parent::_construct();
		
    }
}
