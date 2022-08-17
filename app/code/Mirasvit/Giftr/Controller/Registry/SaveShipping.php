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



namespace Mirasvit\Giftr\Controller\Registry;


use Magento\Framework\Controller\ResultFactory;

class SaveShipping extends \Mirasvit\Giftr\Controller\Registry
{

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = [];
        $data = $this->getRequest()->getParams();

        if ($this->getRequest()->isAjax() && !empty($data)) {
            $address = $this->saveAddress($data);
            if ($address instanceof \Magento\Framework\Model\AbstractModel) {
                $result = [
                    'success' => true,
                    'value' => $address->getId(),
                    'label' => $address->format('inline')
                ];
            }
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);

        return $resultJson;
    }

    /**
     * Address book form.
     *
     * @param array $addressData
     *
     * @return \Magento\Customer\Model\Address|void
     */
    protected function saveAddress(array $addressData)
    {
        $errors = [];
        $customer = $this->_getSession()->getCustomer();
        $address = $this->addressFactory->create();
        $addressForm = $this->formFactory->create();
        $addressForm->setFormCode('customer_address_edit')
            ->setEntity($address);

        $addressErrors = $addressForm->validateData($addressData);
        if ($addressErrors !== true) {
            $errors = $addressErrors;
        }

        $addressForm->compactData($addressData);
        $address->setCustomerId($customer->getId())
            ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
            ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));

        $addressErrors = $address->validate();
        if ($addressErrors !== true) {
            $errors = array_merge($errors, $addressErrors);
        }

        if (count($errors) === 0) {
            $address->save();
            $this->messageManager->addSuccessMessage(__('The address has been saved.'));

            return $address;
        } else {
            $this->_getSession()->setAddressFormData($addressData);
            foreach ($errors as $errorMessage) {
                $this->messageManager->addErrorMessage($errorMessage);
            }
        }
    }
}