<?php
/**
 * Copyright © 2016 Wise Ltd. All rights reserved.
 * Released under the Open Software License (OSL 3.0)
 * Please visit http://opensource.org/licenses/osl-3.0.php for the full text of the OSL 3.0 license
 */
namespace Wise\Liqpay\Controller\Api;

use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Magento\Checkout\Model\DefaultConfigProvider;

use Wise\Liqpay\Model\Payment\Liqpay;

class RedirectUrl extends \Magento\Framework\App\Action\Action
{
    
    protected $_method;
    protected $_session;
    protected $_orderFactory;
    protected $_configProvider;
    
    public function __construct(
        Context $context,
        Liqpay $method,
        Session $session,
        DefaultConfigProvider $configProvider
    ) {
        parent::__construct($context);
        $this->_method = $method;
        $this->_session = $session;
        $this->_configProvider = $configProvider;
    }
    
    public function getMethod() { return $this->_method; }
    public function getSession() { return $this->_session; }
    public function getConfigProvider() { return $this->_configProvider; }
    
    public function execute()
    {
        
        $response = $this->getResponse();
        $status = false;
        
        $defaultSuccessPageUrl = $this->getConfigProvider()->getDefaultSuccessPageUrl();
        
        try {
            
            $order = $this->getSession()->getLastRealOrder();
            if ($order->getId()) {
                
                $order->setDefaultSuccessPageUrl($defaultSuccessPageUrl);
                $order->setReturnCallbackUrl($this->_url->getUrl('liqpay/api/callback'));
                
                $response->setBody($this->getMethod()->getRedirectUrl($order));
                $status = true;
                
            }
            
        } catch (\Exception $e) { }
        
        if (!$status) {
            $response->setBody($defaultSuccessPageUrl);
        }
        
    }
    
}
