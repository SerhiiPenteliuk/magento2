<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-core
 * @version   1.2.110
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Core\Plugin\Backend\Model\Menu\Builder;

use Magento\Backend\Model\Menu;
use Magento\Backend\Model\Menu\Item;
use Magento\Backend\Model\Menu\ItemFactory;
use Magento\Framework\UrlInterface;
use Mirasvit\Core\Block\Adminhtml\Menu as MenuBlock;
use Mirasvit\Core\Model\Config;
use Mirasvit\Core\Model\ModuleFactory;
use Mirasvit\Core\Service\CompatibilityService;

class BuilderPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ItemFactory
     */
    private $itemFactory;

    /**
     * @var ModuleFactory
     */
    private $moduleFactory;

    /**
     * @var MenuBlock
     */
    private $menuBlock;

    /**
     * @var UrlInterface
     */
    private $urlManager;

    /**
     * BuilderPlugin constructor.
     * @param Config $config
     * @param ItemFactory $itemFactory
     * @param ModuleFactory $moduleFactory
     * @param MenuBlock $menuBlock
     * @param UrlInterface $urlManager
     */
    public function __construct(
        Config $config,
        ItemFactory $itemFactory,
        ModuleFactory $moduleFactory,
        MenuBlock $menuBlock,
        UrlInterface $urlManager
    ) {
        $this->config        = $config;
        $this->itemFactory   = $itemFactory;
        $this->moduleFactory = $moduleFactory;
        $this->menuBlock     = $menuBlock;
        $this->urlManager    = $urlManager;
    }

    /**
     * @param mixed $subject
     * @param Menu $menu
     * @return Menu
     */
    public function afterGetResult($subject, Menu $menu)
    {
        if (!$this->config->isMenuEnabled()
            || CompatibilityService::is20()
            || CompatibilityService::is21()
            || $this->isMarketplace()
        ) {
            return $this->removeMenu($menu);
        }

        $installedModules = $this->moduleFactory->create()
            ->getInstalledModules();

        $moduleItems = [];

        foreach ($installedModules as $moduleName) {
            if ($moduleName === 'Mirasvit_Core') {
                continue;
            }

            $module = $this->moduleFactory->create()->load($moduleName);

            $group = $module->getGroup();

            if (!$group) {
                $group = 'Other';
            }

            switch ($moduleName) {
                case 'Mirasvit_Report':
                case 'Mirasvit_Dashboard':
                case 'Mirasvit_ReportBuilder':
                    $group = 'Advanced Reports';
                    break;

                case 'Mirasvit_SearchLanding':
                case 'Mirasvit_SearchReport':
                    $group = 'Search';
                    break;
            }

            if (!isset($moduleItems[$group])) {
                $moduleItems[$group] = [];
            }

            $nativeMenuItems = $this->filterItems($menu, $moduleName);

            foreach ($nativeMenuItems as $idx => $item) {
                $data = $item->toArray();
                unset($data['sub_menu']);

                if (!$data['action']) {
                    continue;
                }

                $url    = $this->urlManager->getUrl($data['action']);
                $urlKey = $this->normalizeUrlKey($url);

                $moduleItems[$group][$urlKey] = $data;
            }

            $items = $this->menuBlock->getItemsByModuleName($moduleName);
            foreach ($items as $idx => $item) {
                if (!is_object($item)) {
                    continue;
                }

                // retrieve action from url
                $action = preg_replace('/\/key\/.*/', '', $item->getUrl());
                $action = str_replace($this->urlManager->getBaseUrl(), '', $action);
                $action = preg_replace('/^\w*\//', '', $action);

                $urlKey = $this->normalizeUrlKey($item->getData('url'));

                $moduleItems[$group][$urlKey] = [
                    'id'       => $item->getData('url'),
                    'module'   => $moduleName,
                    'resource' => $item->getData('resource'),
                    'title'    => (string)$item->getData('title'),
                ];

                // need this for external links
                if(preg_match('/^https?:/', $action)) {
                    $moduleItems[$group][$urlKey]['path'] = $item->getData('url');
                } else {
                    $moduleItems[$group][$urlKey]['action'] = $action;
                }
            }
        }

        ksort($moduleItems);

        $filteredItems = [];

        foreach ($moduleItems as $group => $items) {
            if ($items) {
                $filteredItems[$group] = $items;
            }
        }

        if (count($filteredItems) <= 1) {
            return $this->removeMenu($menu);
        }

        $idx = 0;
        foreach ($filteredItems as $group => $items) {
            $moduleData = [
                'title'    => $group,
                'id'       => hash('sha256', $group),
                'resource' => 'Mirasvit_Core::menu',
            ];

            foreach ($items as $item) {
                $item['id'] = 'Mirasvit_Core::menu::' . $idx++;

                $moduleData['sub_menu'][] = $item;

            }
            $moduleItem = $this->itemFactory->create([
                'data' => $moduleData,
            ]);

            $menu->add($moduleItem, 'Mirasvit_Core::menu');
        }

        return $menu;
    }

    /**
     * @param Menu   $menu
     * @param string $moduleName
     *
     * @return Item[]
     */
    private function filterItems(Menu $menu, $moduleName)
    {
        $items = [];

        /** @var Item $item */
        foreach ($menu->getIterator() as $item) {
            $id = $item->getId();

            if (strpos($id, $moduleName) !== false) {
                $items[] = $item;
            }

            if ($item->getChildren()) {
                $items = array_merge($items, $this->filterItems($item->getChildren(), $moduleName));
            }
        }

        return $items;
    }

    /**
     * @param string $url
     * @return string
     */
    private function normalizeUrlKey($url)
    {
        $url = str_replace('/index/', '', $url);
        $url = rtrim($url, '/');

        return $url;
    }

    /**
     * @param Menu $menu
     * @return Menu
     */
    private function removeMenu(Menu $menu)
    {
        $menu->remove('Mirasvit_Core::menu');

        return $menu;
    }

    /**
     * @return bool
     */
    public function isMarketplace()
    {
        $flag = true;

        /** mp comment start */

        $flag = false;

        /** mp comment end */

        return $flag;
    }
}