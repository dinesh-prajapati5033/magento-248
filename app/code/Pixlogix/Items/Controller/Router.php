<?php
/**
 * @category  Pixlogix
 * @package   Pixlogix_Items
 * @author    Pixlogix
 * @copyright Copyright (c) 2025 Pixlogix
 *
 * Custom Frontend Router for Clean URLs:
 * Example: /pixitems/item_1 → routes to pixitems/index/view
 */

declare(strict_types=1);

namespace Pixlogix\Items\Controller;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Pixlogix\Items\Model\ItemFactory;

class Router implements RouterInterface
{
    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * Constructor.
     *
     * @param ActionFactory $actionFactory
     * @param ItemFactory   $itemFactory
     */
    public function __construct(
        ActionFactory $actionFactory,
        ItemFactory $itemFactory
    ) {
        $this->actionFactory = $actionFactory;
        $this->itemFactory = $itemFactory;
    }

    /**
     * Match incoming frontend request.
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|null
     */
    public function match(RequestInterface $request)
    {
        $identifier = trim($request->getPathInfo(), '/'); // e.g. pixitems/item_1

        // Only handle requests starting with "pixitems"
        if (strpos($identifier, 'pixitems') !== 0) {
            return null;
        }

        $parts = explode('/', $identifier);

        // /pixitems → listing page
        if (count($parts) === 1 && $parts[0] === 'pixitems') {
            $request->setModuleName('pixitems')
                ->setControllerName('index')
                ->setActionName('index');

            return $this->actionFactory->create(
                Forward::class,
                ['request' => $request]
            );
        }

        // /pixitems/{url_key} detail page
        if (count($parts) === 2 && $parts[0] === 'pixitems') {
            $urlKey = $parts[1];

            $item = $this->itemFactory->create()->load($urlKey, 'url_key');
            if ($item && $item->getId()) {
                $request->setModuleName('pixitems')
                    ->setControllerName('index')
                    ->setActionName('view')
                    ->setParam('url_key', $urlKey);

                return $this->actionFactory->create(
                    Forward::class,
                    ['request' => $request]
                );
            }
        }

        return null;
    }
}
