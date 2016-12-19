<?php
namespace Smart\Magerun\Controller\Adminhtml\MagerunGrid;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
		$id = $this->getRequest()->getParam('id');
		try {
				$banner = $this->_objectManager->get('Smart\Magerun\Model\Magerun')->load($id);
				$banner->delete();
                $this->messageManager->addSuccess(
                    __('Delete successfully !')
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
	    $this->_redirect('*/*/');
    }
}
