<?php
/**
 * @category  Pixlogix
 * @package   Pixlogix_Items
 * @author    Pixlogix
 * @copyright Copyright (c) 2025 Pixlogix
 *
 * Handles image upload and movement from temporary to permanent media directories.
 */
declare(strict_types=1);

namespace Pixlogix\Items\Model;

use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ImageUploader
 *
 * Responsible for handling file uploads to the temporary directory
 * and moving them to the final destination within the Magento media folder.
 */
class ImageUploader
{
    /** @var Database */
    protected $coreFileStorageDatabase;

    /** @var \Magento\Framework\Filesystem\Directory\WriteInterface */
    protected $mediaDirectory;

    /** @var UploaderFactory */
    protected $uploaderFactory;

    /** @var Filesystem */
    protected $filesystem;

    /** @var AdapterFactory */
    protected $adapterFactory;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var LoggerInterface */
    protected $logger;

    /** @var string */
    protected $baseTmpPath;

    /** @var string */
    protected $basePath;

    /** @var array */
    protected $allowedExtensions;

    /** Default temporary base media path */
    public const DEFAULT_BASETMPATH = 'pixlogix/items';

    /** Default permanent base media path */
    public const DEFAULT_BASEPATH = 'items';

    /**
     * Constructor
     *
     * @param Database $coreFileStorageDatabase
     * @param Filesystem $filesystem
     * @param UploaderFactory $uploaderFactory
     * @param AdapterFactory $adapterFactory
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param string|null $baseTmpPath
     * @param string|null $basePath
     * @param array|null $allowedExtensions
     */
    public function __construct(
        Database $coreFileStorageDatabase,
        Filesystem $filesystem,
        UploaderFactory $uploaderFactory,
        AdapterFactory $adapterFactory,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        $baseTmpPath = null,
        $basePath = null,
        $allowedExtensions = null
    ) {
        $this->coreFileStorageDatabase = $coreFileStorageDatabase;
        $this->filesystem = $filesystem;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->uploaderFactory = $uploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->storeManager = $storeManager;
        $this->logger = $logger;

        // Set defaults if no custom values are provided
        $this->baseTmpPath = $baseTmpPath ?: self::DEFAULT_BASETMPATH;
        $this->basePath = $basePath ?: self::DEFAULT_BASEPATH;
        $this->allowedExtensions = $allowedExtensions ?: ['jpg', 'jpeg', 'gif', 'png'];
    }

    /**
     * Build a full file path using the base path and file name.
     *
     * @param string $path
     * @param string $fileName
     * @return string
     */
    public function getFilePath(string $path, string $fileName): string
    {
        return rtrim($path, '/') . '/' . ltrim($fileName, '/');
    }

    /**
     * Move uploaded file from temporary to final destination.
     *
     * @param string $fileName
     * @return string
     * @throws LocalizedException
     */
    public function moveFileFromTmp(string $fileName): string
    {
        $baseTmpPath = $this->baseTmpPath;
        $basePath = $this->basePath;

        // Define full temporary and permanent file paths
        $baseImagePath = $this->getFilePath($basePath, $fileName);
        $baseTmpImagePath = $this->getFilePath($baseTmpPath, $fileName);

        try {
            // Copy file in database storage if applicable
            $this->coreFileStorageDatabase->copyFile($baseTmpImagePath, $baseImagePath);

            // Rename file from tmp to final destination on filesystem
            $this->mediaDirectory->renameFile($baseTmpImagePath, $baseImagePath);
        } catch (\Exception $e) {
            throw new LocalizedException(__('Something went wrong while moving the file.'));
        }

        return $fileName;
    }

    /**
     * Save uploaded file to temporary directory.
     *
     * Handles file upload, renaming, dispersion, and saving to database if configured.
     *
     * @param string $fileId
     * @return array
     * @throws LocalizedException
     */
    public function saveFileToTmpDir(string $fileId): array
    {
        $baseTmpPath = $this->baseTmpPath;

        // Create uploader instance for handling file upload
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setAllowedExtensions($this->allowedExtensions);
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(true);

        // Save uploaded file to the temporary directory
        $result = $uploader->save($this->mediaDirectory->getAbsolutePath($baseTmpPath));
        if (!$result) {
            throw new LocalizedException(__('File cannot be saved to the destination folder.'));
        }

        // Generate media URL for the uploaded file
        $result['url'] = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
            . $this->getFilePath($baseTmpPath, $result['file']);
        $result['name'] = $result['file'];

        // Save file info in DB storage (if enabled)
        if (isset($result['file'])) {
            try {
                $relativePath = rtrim($baseTmpPath, '/') . '/' . ltrim($result['file'], '/');
                $this->coreFileStorageDatabase->saveFile($relativePath);
            } catch (\Exception $e) {
                // Log exception and rethrow as LocalizedException
                $this->logger->critical($e);
                throw new LocalizedException(__('Something went wrong while saving the file.'));
            }
        }

        return $result;
    }
}
