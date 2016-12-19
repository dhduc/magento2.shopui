<?php
namespace Smart\Magerun\Block\Adminhtml\Magerun\Edit\Tab;
class General extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = array()
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
		/* @var $model \Magento\Cms\Model\Page */
        $model = $this->_coreRegistry->registry('magerun_magerun');
		$isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('General')));

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array('name' => 'id'));
        }

		$fieldset->addField(
            'title',
            'text',
            array(
                'name' => 'title',
                'label' => __('Title'),
                'title' => __('Title'),
                'required' => true,
            )
        );

        $fieldset->addField(
            'command',
            'text',
            array(
                'name' => 'command',
                'label' => __('Command'),
                'title' => __('Command'),
                'required' => true,
            )
        );

        $fieldset->addField(
            'option',
            'text',
            array(
                'name' => 'option',
                'label' => __('Option'),
                'title' => __('Option'),
                /*'required' => true,*/
            )
        );

        $fieldset->addField(
            'describe',
            'text',
            array(
                'name' => 'describe',
                'label' => __('Describe'),
                'title' => __('Describe'),
                /*'required' => true,*/
            )
        );

        if ($model->getId()) {
            $fieldset->addField(
                'running_at',
                'text',
                array(
                    'name' => 'running_at',
                    'label' => __('Running at'),
                    'title' => __('Running at'),
                    /*'required' => true,*/
                )
            );
        }

        $fieldset->addField(
            'status',
            'text',
            array(
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                /*'required' => true,*/
            )
        );

        if (!$model->getId()) {
            $model->setData('status', $isElementDisabled ? '2' : '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();   
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('General');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
