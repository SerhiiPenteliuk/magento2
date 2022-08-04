<?php

namespace Elogic\CustomModel\ViewModel;


use Magento\Framework\View\Element\Block\ArgumentInterface;

class SubcategoryViewModel implements ArgumentInterface
{
    public function __construct() {
    }

    public function getSubcategories() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $category = $objectManager->get('Magento\Framework\Registry')->registry('current_category');
        $subcats = $category->getChildrenCategories();
        return $subcats;
    }
}
