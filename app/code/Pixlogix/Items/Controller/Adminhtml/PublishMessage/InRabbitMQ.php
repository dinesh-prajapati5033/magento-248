<?php

namespace Pixlogix\Items\Controller\Adminhtml\PublishMessage;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class InRabbitMQ extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Magento\Framework\MessageQueue\PublisherInterface
     */
    private $publisher;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param JsonHelper $jsonHelper
     * @param \Magento\Framework\MessageQueue\PublisherInterface $publisher
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        JsonHelper $jsonHelper,
        \Magento\Framework\MessageQueue\PublisherInterface $publisher
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->jsonHelper = $jsonHelper;
        $this->publisher = $publisher;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        try {
            $resultJson = $this->resultJsonFactory->create();

            /**
             * Here we are using random product id and product data as $item for message publish
             * @var int $productId 
             * @var array $item
             */
            $publishData = ['mage_pro_id' => $productId, 'item' => $item];
            // yourtopibname.topic same as you add in communication.xml file
            $this->publisher->publish('yourtopibname.topic', $this->jsonHelper->jsonEncode($publishData));
            $result = ['msg' => 'success'];
            return $resultJson->setData($result);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage()];
            return $resultJson->setData($result);
        }
        
    }

    /**
     * Check product import permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('NameSpace_ModuleName::product_import');
    }
}