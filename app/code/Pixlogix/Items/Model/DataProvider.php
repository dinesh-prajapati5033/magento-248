<?php
/**
 * @category  Pixlogix
 * @package   Pixlogix_Items
 * @author    Pixlogix
 * @copyright Copyright (c) 2025
 *
 * Data provider for the Item form UI component.
 * Loads data from the collection and prepares it for form rendering.
 */

declare(strict_types=1);

namespace Pixlogix\Items\Model;

use Pixlogix\Items\Model\ResourceModel\Item\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class DataProvider
 *
 * Responsible for supplying form data to the admin UI form.
 * It fetches collection data, processes image fields, and handles persisted form data.
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var array|null Cached data to prevent re-loading
     */
    protected $loadedData;

    /**
     * @var CollectionFactory Item collection factory
     */
    protected $collectionFactory;

    /**
     * @var DataPersistorInterface Used to persist form data between requests
     */
    protected $dataPersistor;

    /**
     * @var StoreManagerInterface Provides store and media base URLs
     */
    protected $storeManager;

    /**
     * @var Filesystem Used for filesystem operations
     */
    protected $filesystem;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param StoreManagerInterface $storeManager
     * @param Filesystem $filesystem
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        array $meta = [],
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        // Initialize the collection for form data
        $this->collection = $this->collectionFactory->create();
    }

    /**
     * Load data for the edit form.
     *
     * Processes image field and prepares formatted data
     * for the UI component form (including persisted data).
     *
     * @return array
     */
    public function getData(): array
    {
        // If data already loaded, return cached result
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        // Fetch items from the collection
        $items = $this->collection->getItems();

        foreach ($items as $item) {
            $data = $item->getData();

            // Handle image preview formatting for UI form
            if (!empty($data['image'])) {
                $imageName = $data['image'];
                $data['image'] = [
                    [
                        'name' => basename($imageName), // phpcs:ignore Magento2.Functions.DiscouragedFunction
                        'url'  => $this->getMediaUrl($imageName),
                    ]
                ];
            }

            $this->loadedData[$item->getId()] = $data;
        }

        // Handle persisted data (e.g., when a save fails)
        $persistedData = $this->dataPersistor->get('pixlogix_items_form');
        if (!empty($persistedData)) {
            // Create a new empty model and repopulate with persisted values
            $item = $this->collection->getNewEmptyItem();
            $item->setData($persistedData);
            $this->loadedData[$item->getId()] = $item->getData();
            $this->dataPersistor->clear('pixlogix_items_form');
        }

        return $this->loadedData ?? [];
    }

    /**
     * Build the full media URL for an uploaded image.
     *
     * Ensures consistent URL structure for the admin preview image field.
     *
     * @param string $file Image path or name
     * @return string Full media URL
     */
    private function getMediaUrl(string $file): string
    {
        // Get base media URL
        $baseUrl = $this->storeManager
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        // Normalize the file path (remove leading slash)
        $file = ltrim($file, '/');

        // Avoid duplicating "items/" prefix if already included
        if (strpos($file, 'items/') === 0) {
            return $baseUrl . $file;
        }

        // Default case: prepend "items/" folder
        return $baseUrl . 'items/' . $file;
    }
}
