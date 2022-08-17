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
 * @package   mirasvit/module-gift-registry
 * @version   1.2.34
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Giftr\Setup;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterfaceFactory;
use Magento\Catalog\Model\Product\Action;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;


/**
 * Upgrade Data script
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var BlockRepositoryInterface
     */
    protected $blockRepository;

    /**
     * @var BlockInterfaceFactory
     */
    protected $blockFactory;

    /**
     * @var Product\Action
     */
    protected $productAction;

    /**
     * @var Product\Collection
     */
    protected $productCollection;
    
    /**
     * @var App\State
     */
    protected $appState;

    /**
     * @var \Mirasvit\Giftr\Model\ResourceModel\Type\Collection
     */
    protected $typeCollection;
    
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $moduleReader;

    /**
     * UpgradeData constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param BlockRepositoryInterface $blockRepository
     * @param BlockInterfaceFactory $blockFactory
     * @param Action $productAction
     * @param Collection $productCollection
     * @param State $appState
     * @param \Mirasvit\Giftr\Model\ResourceModel\Type\Collection $typeCollection
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        BlockRepositoryInterface $blockRepository,
        BlockInterfaceFactory $blockFactory,
        Action $productAction,
        Collection $productCollection,
        State $appState,
        \Mirasvit\Giftr\Model\ResourceModel\Type\Collection $typeCollection,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Module\Dir\Reader $moduleReader
    ) {
        $this->eavSetupFactory     = $eavSetupFactory;
        $this->blockRepository     = $blockRepository;
        $this->productAction       = $productAction;
        $this->productCollection   = $productCollection;
        $this->appState            = $appState;
        $this->typeCollection      = $typeCollection;
        $this->fileSystem          = $fileSystem;
        $this->moduleReader        = $moduleReader;
    }

    /**
     * @var string
     */
    const IMAGE_FOLDER_NAME = 'giftr';

    /**
     * @var string
     */
    const DEFAULT_IMAGE_FOLDER_NAME = 'data';

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'use_in_giftr',
                [
                    'type' => 'int',
                    'label' => 'Use in Gift Registry',
                    'input' => 'boolean',
                    'class' => '',
                    'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'group'                 => 'Product Details',
                    'is_used_in_grid'       => false,
                    'is_visible_in_grid'    => false,
                    'is_filterable_in_grid' => false,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '1',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );

            try {
                $this->appState->setAreaCode(Area::AREA_GLOBAL);
            } catch (\Exception $e) {}

            $this->updateProducts();
            $this->updateGiftrAttributes($setup);
        }

        $setup->endSetup();
    }

    /**
     * @return string
     */
    private function updateProducts()
    {
        $ids = $this->productCollection->getAllIds(); //We need permanent fix of this. Move out of upgrade script.
        if (!empty($ids)) {
            $this->productAction->updateAttributes($ids, array('use_in_giftr' => '1'), 0);
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function updateGiftrAttributes(ModuleDataSetupInterface $setup)
    {
        $path = $this->fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
            ->getAbsolutePath() . self::IMAGE_FOLDER_NAME;

        if (!is_dir($path)) {
            $this->fileSystem
                ->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
                ->create($path);
        }

        $dataDir = $this->moduleReader->getModuleDir(\Magento\Framework\Module\Dir::MODULE_VIEW_DIR,'Mirasvit_Giftr');
        $dataDir = str_replace('/view', '/Setup/data', $dataDir);

        $placeholderIconName = 'event-icon-placeholder.png';
        $placeholderImageName = 'event-image-placeholder.jpg';

        foreach ($this->typeCollection as $registryType) {
            $rawFileName = strtolower(str_replace(' ', '-', $registryType->getName()));
            $registryType->setCode($rawFileName);
            $matchingFiles = glob($dataDir .'/*'. $rawFileName.'*');

            if ($matchingFiles && !empty($matchingFiles)) {
                foreach ($matchingFiles as $file) {
                    $fileName = pathinfo($file, PATHINFO_BASENAME);
                    $mediaFilePath = $path .'/'. $fileName;

                    if (!file_exists($mediaFilePath) && file_exists($file)) {

                        if (!is_dir($path)) {
                            @mkdir($path, 0755);
                        }

                        copy($file, $mediaFilePath);
                    }

                    if (strstr($file, 'event-icon')) {
                        $registryType->setEventIcon($fileName);
                    } else {
                        $registryType->setEventImage($fileName);
                    }
                }

            } else {

                if (!file_exists($path.'/'.$placeholderIconName)) {
                    copy($dataDir.'/'.$placeholderIconName, $path.'/'.$placeholderIconName);
                }
                if (!file_exists($path.'/'.$placeholderImageName)){
                    copy($dataDir.'/'.$placeholderImageName, $path.'/'.$placeholderImageName);
                }

                $registryType->setEventIcon($placeholderIconName);
                $registryType->setEventImage($placeholderImageName);
            }

            $registryType->save();
        }
      
    }
}
