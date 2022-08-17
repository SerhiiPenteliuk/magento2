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



namespace Mirasvit\Giftr\Model\ResourceModel;

class Registry extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Mirasvit\Giftr\Model\Config
     */
    protected $config;

    /**
     * @var \Mirasvit\Giftr\Helper\Data
     */
    protected $giftrData;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Context
     */
    protected $context;

    /**
     * @var null
     */
    protected $resourcePrefix;

    /**
     * @param \Mirasvit\Giftr\Model\Config                      $config
     * @param \Mirasvit\Giftr\Helper\Data                       $giftrData
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param null $resourcePrefix
     */
    public function __construct(
        \Mirasvit\Giftr\Model\Config $config,
        \Mirasvit\Giftr\Helper\Data $giftrData,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $resourcePrefix = null
    ) {
        $this->config = $config;
        $this->giftrData = $giftrData;
        $this->context = $context;
        $this->resourcePrefix = $resourcePrefix;
        parent::__construct($context, $resourcePrefix);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mst_giftr_registry', 'registry_id');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->getIsMassDelete()) {
        }

        return parent::_afterLoad($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->getUid()) {
            $uid = $this->giftrData->generateRandString(\Mirasvit\Giftr\Model\Registry::UID_LENGTH);
            while ($object->getCollection()->addFieldToFilter('uid', $uid)->getSize() > 0) {
                $uid = $this->giftrData->generateRandString(\Mirasvit\Giftr\Model\Registry::UID_LENGTH);
            }
            $object->setUid($uid);
        }

        if (!$object->getId()) {
            $object->setCreatedAt((new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));
        }

        if (!$object->hasIsPublic()) {
            $object->setIsPublic(true);
        }

        $object->setUpdatedAt((new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));

        if (isset($_FILES['image']['name']) && $_FILES['image']['name']) {
            $this->saveImage($object);
        } elseif (isset($_POST['image']['delete']) && $_POST['image']['delete'] == 1) {
            $this->deleteImage($object->getImage());
            $object->setData('image', '');
        }

        return parent::_beforeSave($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->getIsMassStatus()) {
        }

        return parent::_afterSave($object);
    }

    /**
     * Function for saving image
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function saveImage(\Magento\Framework\Model\AbstractModel $object)
    {
        $sizeLimit = 10485760; // 10 Mb
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $oldFileName = $object->getImage();
        $newFileName = $object->getUid() . '_' . md5($_FILES['image']['name']) . '.' . $ext;

        $allowedFileExtensions = ['png', 'jpeg', 'jpg', 'gif'];
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        if (!in_array($ext, $allowedFileExtensions)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('File type not allowed (only JPG, JPEG, PNG & GIF files are allowed)')
            );
        }
        if ($_FILES['image']['size'] > $sizeLimit) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('File uploaded exceeds maximum upload size (10MB)')
            );
        }

        try {
            $uploader = new \Magento\Framework\File\Uploader($_FILES['image']);
            $uploader->setAllowedExtensions($allowedFileExtensions)
                ->setAllowRenameFiles(false)
                ->setFilesDispersion(false)
                ->setAllowCreateFolders(true)
                ->setAllowRenameFiles(false)
                ->setFilesDispersion(false);
            $uploader->save($this->config->getBaseMediaPath(), $newFileName);
            $object->setImage($newFileName);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }

        if ($newFileName != $oldFileName) {
            $this->deleteImage($oldFileName);
        }

        return;
    }

    /**
     * Remove giftr image by its name, do not remove placeholder
     *
     * @param string $fileName
     * @return void
     */
    private function deleteImage($fileName)
    {
        if ($fileName === $this->config->getPlaceholder()) {
            return;
        }

        $path = $this->config->getBaseMediaPath();
        if ($fileName && file_exists($path.'/'.$fileName)) {
            unlink($path.'/'.$fileName);
        }

        return;
    }

    /************************/
}
