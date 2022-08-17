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



namespace Mirasvit\Giftr\Model\Service;

use Mirasvit\Giftr\Model\ResourceModel\Registry\CollectionFactory;

/**
 * Class provides search functionality through the Gift Registries
 */
class RegistrySearchService
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * RegistrySearchService constructor.
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Search for registries
     * by default returns only active and associated with current website registries
     *
     * @param array $searchParams           - array of search queries ('search_type' => 'search_query')
     * @param array $searchableAttributes   - actual searchable field names (table `mst_giftr_registry`)
     *
     * @return \Mirasvit\Giftr\Model\ResourceModel\Registry\Collection
     */
    public function search(
        $searchParams,
        array $searchableAttributes = ['name'=>['registrant_name', 'co_firstname', 'co_lastname']]
    ) {
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('main_table.is_active', 1)
            ->addWebsiteFilter();

        $collection->getSelect()->joinLeft(
            ['c' => $collection->getResource()->getTable('customer_entity')],
            'c.entity_id = main_table.customer_id',
            []);

        $orConditions = [];
        $andConditions = [];

        foreach ($searchParams as $key => $value) {
            $value = $this->escape(trim($value));
            if (!$value) {
                continue;
            }

            $arrValue = explode(' ',$value);

            switch ((string)$key) {
                case 'name':
                    foreach ($searchableAttributes['name'] as $attribute) {
                        foreach ($arrValue as $valuePart){
                            if ($attribute == 'registrant_name') {
                                $orConditions[] = 'CONCAT_WS(" ", `main_table`.`firstname`, `main_table`.`lastname`, `c`.`firstname`, `c`.`lastname`) LIKE ("%' . addslashes($valuePart) . '%")';
                            } else {
                                $orConditions[] = $attribute . ' LIKE ("%' . addslashes($valuePart) . '%")';
                            }
                        }
                    }
                    break;

                case 'event_at':
                            $andConditions[] = $key . ' LIKE ("%' . addslashes(date("Y-m-d", strtotime($value))) . '%")';
                    break;

                case 'registry_id':
                            $orConditions[] = $key . ' LIKE ("%' . addslashes($value) . '%")';
                            $orConditions[] = 'uid LIKE ("%' . addslashes($value) . '%")';
                    break;

                default:
                    foreach ($arrValue as $valuePart){
                        $andConditions[] = $key . ' LIKE ("%' . addslashes($valuePart) . '%")';
                    }
                    break;
            }
        }

        if (!empty($orConditions)) {
            $orConditions = implode(' OR ', $orConditions);
            $collection->getSelect()->where($orConditions);
        }

        if (!empty($andConditions)) {
            $andConditions = implode(' AND ', $andConditions);
            $collection->getSelect()->where($andConditions);
        }

        return $collection;
    }

    /**
     * @param string $value
     * @return string|string[]|null
     */
    private function escape($value)
    {
        $pattern = '/(\+|-|\/|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';
        $replace = '\\\$1';

        return preg_replace($pattern, $replace, $value);
    }
}