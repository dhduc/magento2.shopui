<?php

namespace Smart\Setting\Console\Command;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\BlockFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Setup
 */
class Setup extends Command
{
    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    private $blockFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $pageFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $categoryCollection;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directory_list;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     *
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $regionFactory;


    public function __construct(
        BlockFactory $blockFactory,
        PageFactory $pageFactory,
        CategoryFactory $categoryFactory,
        StoreManagerInterface $storeManagerInterface,
        CollectionFactory $categoryCollection,
        DirectoryList $directory_list,
        Registry $registry,
        ObjectManagerInterface $objectManager,
        RegionFactory $regionFactory
    )
    {
        parent::__construct();
        $this->objectManager = $objectManager;
        $this->pageFactory = $pageFactory;
        $this->blockFactory = $blockFactory;
        $this->categoryFactory = $categoryFactory;
        $this->storeManager = $storeManagerInterface;
        $this->categoryCollection = $categoryCollection;
        $this->directory_list = $directory_list;
        $this->registry = $registry;
        $this->regionFactory = $regionFactory;
        $registry->register('isSecureArea', true);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('smart:setup')
            ->setDescription('Smart setting setup');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Start setting blocks ...</info>');
        $this->setupCmsPage();

        $output->writeln('<info>Start setting pages ...</info>');
        $this->setupStaticBlock();
    }

    protected function setupCmsPage()
    {

        $cmsPages = [
            [
                'title' => 'Our Collection',
                'identifier' => 'our-collection',
                'page_layout' => '1column',
                'meta_keywords' => 'Our Collection Landing Page',
                'meta_description' => 'Our Collection Landing Page',
                'content' => "
                    &nbsp;
                "
            ]
        ];
        foreach ($cmsPages as $data) {
            $page = $this->pageFactory->create();
            $page->getResource()->load($page, $data['identifier']);
            if (!$page->getData()) {
                $page->setData($data);
            } else {
                $page->addData($data);
            }
            $page->setStores([\Magento\Store\Model\Store::DEFAULT_STORE_ID]);
            $page->setIsActive(1);
            $page->save();
        }
    }

    protected function setupStaticBlock()
    {
        /*
        * Define blocks
        */
        $dataBlocks = [
            [
                'title' => 'Header Top Links',
                'identifier' => 'header_top_links',
                'content' => '
<ul>
<li><a href="{{store url="contact"}}">Contact</a></li>
<li><a href="{{store url="blog"}}">Blog</a></li>
<li><a href="{{store url="faqs"}}">Faqs</a></li>
<li><a href="{{store url="customer/account"}}">Login</a></li>
</ul>
'
            ],
            [
                'title' => 'Footer Terms and Condition',
                'identifier' => 'footer_terms_and_condition',
                'content' => '
<ul>
<li><a href="{{store url="terms_and_privacy"}}">Terms & Privacy</a></li>
<li><a href="{{store url="sitemap"}}">Sitemap</a></li>
</ul>
'
            ],
        ];

        /*
         * Static cms block
         */
        foreach ($dataBlocks as $data) {
            $cmsBlock = $this->blockFactory->create();
            $cmsBlock->getResource()->load($cmsBlock, $data['identifier']);
            if (!$cmsBlock->getData()) {
                $cmsBlock->setData($data);
            } else {
                $cmsBlock->addData($data);
            }
            $cmsBlock->setStores([\Magento\Store\Model\Store::DEFAULT_STORE_ID]);
            $cmsBlock->setIsActive(1);
            $cmsBlock->save();
        }
    }

}
