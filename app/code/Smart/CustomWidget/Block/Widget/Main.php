<?php

namespace Smart\CustomWidget\Block\Widget;

class Main extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context, array $data = array()
    ) {
        parent::__construct($context, $data);
    }

    protected function _construct() {
        parent::_construct();
        $this->setTemplate('widget/main.phtml');
    }

    public function getWidgetName() {
        $widget_name = 'Smart Custom Widget';
        return $widget_name;
    }

}
