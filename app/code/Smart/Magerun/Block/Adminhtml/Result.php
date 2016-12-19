<?php

namespace Smart\Magerun\Block\Adminhtml;

/**
 * Class Result
 * @package Smart\Magerun\Block\Adminhtml
 */
class Result extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;
    public $catalogSession;

    /**
     * Result constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = array()
    ) {
        $this->registry = $registry;
        $this->catalogSession = $catalogSession;
        parent::__construct($context, $data);
    }

    protected function _construct() {
        parent::_construct();
    }

    public function getHeaderTitle() {
        $header_title = 'The result output:';
        return $header_title;
    }

    public function getResultOutput() {
        return ($this->catalogSession->getData('magerun_output')) ? $this->catalogSession->getData('magerun_output'): '';
    }

    public function flushResultOutput() {
        $this->catalogSession->unsetData('magerun_output');
    }

}