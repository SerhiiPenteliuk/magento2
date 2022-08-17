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



namespace Mirasvit\Giftr\Repository;


use Mirasvit\Giftr\Api\Repository\RegistryRepositoryInterface;
use Mirasvit\Giftr\Model\FieldFactory;
use Mirasvit\Giftr\Model\Field;
use Mirasvit\Giftr\Model\Registry;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Customer;

class RegistryRepository implements RegistryRepositoryInterface
{
    /**
     * @var FieldFactory
     */
    private $fieldFactory;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * RegistryRepository constructor.
     * @param CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param FieldFactory $fieldFactory
     */
    public function __construct(
        CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession,
        FieldFactory $fieldFactory
    ) {
        $this->fieldFactory = $fieldFactory;
        $this->customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
    }


    /**
     * {@inheritDoc}
     */
    public function save(Registry $model, array $data = [])
    {
        if (!empty($data)) {
            $data = $model->escape($data);
            $customer = ($model->getCustomer())
                ? $model->getCustomer()
                : $this->customerFactory->create()->load($this->customerSession->getCustomerId());

            $websiteId = ($model->getWebsite()) ? $model->getWebsite()->getId() : $customer->getWebsiteId();
            $sections = isset($data['section']) ? $data['section'] : [];

            if ($model->getId()) {
                $data['image'] = $model->getImage();
            } else {
                $data['image'] = '';
            }

            $model->setData($data)
                ->setCustomerId($customer->getId())
                ->setWebsiteId($websiteId)
                ->setEventAt($this->getFieldByCode('event_at')->getValue($model->getEventAt()))
                ->setValues($this->prepareValues($sections));
        } else {
            throw new \Exception('Error processing request: Insufficient data provided');
        }

        return $model;
    }

    /**
     * Prepare custom registry fields' values for save.
     *
     * @param array $sections - array of fields values grouped by sections.
     *
     * @return string
     */
    private function prepareValues(array $sections = [])
    {
        foreach ($sections as $sectionIdx => $section) {
            foreach ($section as $fieldsIdx => $fields) {
                foreach ($fields as $fieldCode => $fieldValue) {
                    $field = $this->getFieldByCode($fieldCode);
                    $sections[$sectionIdx][$fieldsIdx][$fieldCode] = $field->getValue($fieldValue);
                }
            }
        }

        return serialize($sections);
    }

    /**
     * @param string $fieldCode
     *
     * @return Field
     */
    private function getFieldByCode($fieldCode)
    {
        return $this->fieldFactory->create()->load($fieldCode, 'code');
    }
}