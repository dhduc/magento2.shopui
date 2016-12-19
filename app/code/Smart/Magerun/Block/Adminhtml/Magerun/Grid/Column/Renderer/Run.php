<?php

namespace Smart\Magerun\Block\Adminhtml\Magerun\Grid\Column\Renderer;

/**
 * Class Run
 * @package Smart\Magerun\Block\Adminhtml\Magerun\Grid\Column\Renderer
 */
class Run extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{
    /**
     * Render single action as link html
     *
     * @param array $action
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    protected function _toLinkHtml($action, \Magento\Framework\DataObject $row)
    {
        $actionAttributes = new \Magento\Framework\DataObject();

        $actionCaption = '';
        $this->_transformActionData($action, $actionCaption, $row);

        if (isset($action['confirm'])) {
            $action['onclick'] = 'return window.confirm(\'' . addslashes(
                $this->escapeHtml($action['confirm'])
            ) . '\')';
            unset($action['confirm']);
        }

        $actionAttributes->setData($action);
        return '<button><a ' . $actionAttributes->serialize() . '>' . $actionCaption . '</a></button>';
    }
}