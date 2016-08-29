<?php
/**
 * Copyright © 2016 Wise Ltd. All rights reserved.
 * Released under the Open Software License (OSL 3.0)
 * Please visit http://opensource.org/licenses/osl-3.0.php for the full text of the OSL 3.0 license
 */
namespace Wise\Liqpay\Controller\Api;

use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\Payment\TransactionFactory;
use Wise\Liqpay\Model\Payment\Liqpay as Payment;

class Callback extends \Magento\Framework\App\Action\Action
{
    
    protected $_orderFactory;
    protected $_transactionFactory;
    
    public function __construct(
        Context $context,
        OrderFactory $orderFactory,
        TransactionFactory $transactionFactory
    ) {
        parent::__construct($context);
        $this->_orderFactory = $orderFactory;
        $this->_transactionFactory = $transactionFactory;
    }
    
    public function getOrderFactory()
    {
        return $this->_orderFactory;
    }
    
    public function getTransactionFactory()
    {
        return $this->_transactionFactory;
    }
    
    public function execute()
    {
        
        $request = $this->getRequest();
        if ($data = $request->getParam('data', false)) {
            if ($data = base64_decode($data)) {
                if ($json = json_decode($data, false, 2)) {
                    
                    if (
                            isset (
                                $json->status,
                                $json->transaction_id,
                                $json->order_id
                            )
                        &&  $json->transaction_id
                        &&  $json->order_id
                    ) {
                        
                        $order = $this->getOrderFactory()->create()->loadByIncrementId($json->order_id);
                        if ($orderId = $order->getId()) {
                            
                            switch ($json->status) {
                                
                                case Payment::S_SUCCESS:
                                case Payment::S_SANDBOX:
                                    
                                    $payment = $order->getPayment();
                                    $payment->setTransactionId($json->transaction_id);
                                    
                                    $invoice = $order->prepareInvoice();
                                    $invoice->register()->pay();
                                    
                                    $order->setState($order::STATE_PROCESSING);
                                    
                                    $transaction = $this->getTransactionFactory()->create()
                                        ->setOrderId($orderId)
                                        ->setPaymentId($payment->getId())
                                        ->setTxnId($json->transaction_id);
                                    
                                    $transaction->setAdditionalInformation($transaction::RAW_DETAILS, (array)$json);
                                    
                                    if (isset($json->type) && $json->type == 'buy') {
                                        $transaction->setTxnType($transaction::TYPE_PAYMENT);
                                    }
                                    
                                    $transaction->save();
                                    break;
                                    
                                case Payment::S_FAILURE:
                                    $order->setState($order::STATE_CANCELED);
                                    break;
                                    
                                case Payment::S_REVERSED:
                                    break;
                                    
                                default:
                                    $order->setState($order::STATE_PROCESSING);
                                
                            }
                            
                            $order->save();
                            
                        }
                        
                    }
                    
                }
            }
        }
        
    }
    
}
