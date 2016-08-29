<?php
/**
 * Copyright © 2016 Wise Ltd. All rights reserved.
 * Released under the Open Software License (OSL 3.0)
 * Please visit http://opensource.org/licenses/osl-3.0.php for the full text of the OSL 3.0 license
 */
namespace Wise\Liqpay\Model\Payment;

use Magento\Sales\Model\Order;
use Magento\Checkout\Model\DefaultConfigProvider;

class Liqpay extends \Magento\Payment\Model\Method\AbstractMethod
{
    
    const API_VERSION = '3';
    
    const A_PAY = 'pay';
    const A_HOLD = 'hold';
    const A_SUBSCRIPTION = 'subscribe';
    const A_DONATION = 'paydonate';
    
    const L_EN = 'en';
    const L_RU = 'ru';
    
    const SS_ON = '1';
    const SS_OFF = '0';
    
    const S_SUCCESS = 'success';
    const S_FAILURE = 'failure';
    const S_ERROR = 'error';
    const S_SUBSCRIBED = 'subscribed';
    const S_UNSUBSCRIBED = 'unsubscribed';
    const S_REVERSED = 'reversed';
    const S_SANDBOX = 'sandbox';
    
    const SV_OTP = 'otp_verify';
    const SV_3DS = '3ds_verify';
    const SV_CVV = 'cvv_verify';
    const SV_SENDER = 'sender_verify';
    const SV_RECEIVER = 'receiver_verify';
    
    const SW_BITCOIN = 'wait_bitcoin';
    const SW_SECURE = 'wait_secure';
    const SW_ACCEPT = 'wait_accept';
    const SW_LC = 'wait_lc';
    const SW_HOLD = 'hold_wait';
    const SW_CASH = 'cash_wait';
    const SW_QR = 'wait_qr';
    const SW_SENDER = 'wait_sender';
    const SW_PROCESS = 'processing';
    
    const P_VERSION = 'version';
    const P_KEY = 'public_key';
    const P_ACTION = 'action';
    const P_LANGUAGE = 'language';
    const P_SANDBOX = 'sandbox';
    
    const P_AMOUNT = 'amount';
    const P_CURRENCY = 'currency';
    const P_ORDER_ID = 'order_id';
    const P_CUSTOMER_ID = 'customer';
    const P_SENDERNAME_FIRST = 'sender_first_name';
    const P_SENDERNAME_LAST = 'sender_last_name';
    const P_URL_RESULT = 'result_url';
    const P_URL_SERVER = 'server_url';
    const P_DSC = 'description';
    
    const URL_CHECKOUT = 'https://www.liqpay.com/api/3/checkout';
    
    const XML_OPT_KEY_PUBLIC = 'public_key';
    const XML_OPT_KEY_PRIVATE = 'private_key';
    
    protected $_code = 'liqpay';
    
    protected $_publicKey = null;
    protected $_privateKey = null;
    
    protected $_configProvider;
    
    public function getPublicKey()
    {
        if ($this->_publicKey === null) {
            $this->_publicKey = $this->getConfigData(self::XML_OPT_KEY_PUBLIC);
        }
        return $this->_publicKey;
    }
    
    public function getPrivateKey()
    {
        if ($this->_privateKey === null) {
            $this->_privateKey = $this->getConfigData(self::XML_OPT_KEY_PRIVATE);
        }
        return $this->_privateKey;
    }
    
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return parent::isAvailable($quote)
            && $this->getPublicKey()
            && $this->getPrivateKey();
    }
    
    public function getDefaultParams()
    {
        return [
            self::P_VERSION => self::API_VERSION,
            self::P_KEY => $this->getPublicKey(),
            self::P_ACTION => self::A_PAY,
            self::P_LANGUAGE => self::L_RU,
            self::P_SANDBOX => self::SS_ON
        ];
    }
    
    public function getParamsEncoded(array $params = [])
    {
        return base64_encode(json_encode(array_merge($this->getDefaultParams(), $params)));
    }
    
    public function getParamsEncodedByOrder(Order $order)
    {
        return $this->getParamsEncoded([
            self::P_AMOUNT => $order->getGrandTotal(),
            self::P_CURRENCY => $order->getOrderCurrencyCode(),
            self::P_ORDER_ID => $order->getIncrementId(),
            self::P_CUSTOMER_ID => $order->getCustomerId(),
            self::P_SENDERNAME_FIRST => $order->getCustomerFirstname(),
            self::P_SENDERNAME_LAST => $order->getCustomerLastname(),
            self::P_URL_RESULT => $order->getDefaultSuccessPageUrl(),
            self::P_URL_SERVER => $order->getReturnCallbackUrl(),
            self::P_DSC => $this->getConfigData(self::P_DSC)
        ]);
    }
    
    public function getSignature($paramsEncoded)
    {
        $privateKey = $this->getPrivateKey();
        return base64_encode(sha1($privateKey . $paramsEncoded . $privateKey, true));
    }
    
    public function getRedirectUrl(Order $order)
    {
        $paramsEncoded = $this->getParamsEncodedByOrder($order);
        return self::URL_CHECKOUT . '?data=' . $paramsEncoded . '&signature=' . $this->getSignature($paramsEncoded);
    }
    
}
