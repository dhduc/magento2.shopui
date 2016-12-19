<?php

namespace Smart\Setting\Console\Command;

/**
 * Import Symfony component console
 */
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\State;
use Magento\Config\Model\Config\Factory;
use Magento\Framework\App\Config\Storage\WriterInterface;

/**
 * Class Config
 * @package code\Smart\Setting\Console\Command
 */
class Config extends Command
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Config\Model\Config\Factory
     */
    private $configFactory;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    public function __construct(
        State $appState,
        ObjectManagerInterface $objectManager,
        ManagerInterface $eventManager,
        Factory $configFactory,
        WriterInterface $configWriter
    ) {
        $appState->setAreaCode('adminhtml');
        parent::__construct();
        $this->objectManager = $objectManager;
        $this->eventManager = $eventManager;
        $this->configFactory = $configFactory;
        $this->configWriter = $configWriter;
    }

    protected function configure()
    {
        $this->setName('smart:config')
            ->setDescription('Smart setting configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Start setting configuration ...</info>');
        $globalConfigs = [
            'global' => [
                'web/unsecure/base_url' => 'http://magento2.local/',
                'web/secure/base_url'   => 'https://magento2.local/',
                'web/secure/use_in_frontend' => 0,
                'web/secure/use_in_adminhtml' => 0
            ]
        ];

        /*
         * Global config with scope default
         */
        foreach ($globalConfigs as $section => $configs) {
            foreach ($configs as $path => $value) {
                $this->configWriter->save($path, $value);
            }
        }
    }

}