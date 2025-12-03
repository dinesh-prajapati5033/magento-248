<?php
/**
 * @category  Vendor
 * @package   Vendor_Warranty
 * @author    Vendor
 * @copyright Copyright (c) 2025 Vendor
 */
declare(strict_types=1);

namespace Vendor\Warranty\Model\Registration;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Vendor\Warranty\Model\ResourceModel\WarrantyRegistration\CollectionFactory;
use Magento\Framework\Registry;
use Magento\Framework\App\Request\DataPersistorInterface;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var array
     */
    protected $loadedData = [];

    /**
     * @var Registry
     */
    protected Registry $registry;

    /**
     * @var DataPersistorInterface
     */
    protected DataPersistorInterface $dataPersistor;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param Registry $registry
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        Registry $registry,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->registry = $registry;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data for the UI form
     *
     * @return array
     */
    public function getData(): array
    {
        if (!empty($this->loadedData)) {
            return $this->loadedData;
        }

        // Check for persisted data (after failed save)
        $data = $this->dataPersistor->get('warranty_registration');
        if (!empty($data)) {
            $model = $this->collection->getNewEmptyItem();
            $model->setData($data);
            $this->loadedData[$model->getId()] = $model->getData();
            $this->dataPersistor->clear('warranty_registration');
            return $this->loadedData;
        }

        // Load current registration from registry (set in Edit controller)
        $model = $this->registry->registry('current_registration');
        if ($model && $model->getId()) {
            $this->loadedData[$model->getId()] = $model->getData();
        }

        return $this->loadedData;
    }
}
