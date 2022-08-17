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



namespace Mirasvit\Giftr\Controller\Giftr;

use Magento\Framework\Controller\ResultFactory;

class Resume extends \Mirasvit\Giftr\Controller\Giftr
{
    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $code = $this->getRequest()->getParam('code');
        $registryId = $this->getRequest()->getParam('id');
        $url = $this->context->getUrl()->getBaseUrl();
        if ($code && $registryId) {
            if ($registry = $this->_initRegistry()) {
                if ($code === $registry->getUidMd5()) {
                    $this->giftrData->loginCustomer($registry->getCustomerId());
                }
            }
        }

        if (($path = $this->getRequest()->getParam('path')) && $registryId) {
            $url = $this->context->getUrl()->getUrl($this->giftrData->base64UrlDecode($path), ['id' => $registryId]);
        }

        return $this->_redirect($url);
    }
}
