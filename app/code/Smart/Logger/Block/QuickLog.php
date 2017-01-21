<?php
namespace Smart\Logger\Block;

/**
 * Class QuickLog
 * @package Smart\Logger\Block
 */
class QuickLog extends \Magento\Framework\View\Element\Template
{
    /**
     * QuickLog constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context, array $data = array()
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @function setLog
     * @return;
     */
    public function setLog() {
        /*
         * Method 1
         * Using DI
         */
        $this->logger->addDebug("Quick log method 1");

        /**
         * Method 2
         * Using object manager
         * You can use info() alternative for debug() function
         */
        \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->debug('Quick log method 2');

        /**
         * Method 3
         * Using Zend Log
         */
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/info.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('Quick log method 3');

        return;
    }
}
