<?php
/**
 * Copyright © 2016 Wise Ltd. All rights reserved.
 * Released under the Open Software License (OSL 3.0)
 * Please visit http://opensource.org/licenses/osl-3.0.php for the full text of the OSL 3.0 license
 */
namespace Wise\Liqpay\Model;

class Transaction extends \Magento\Framework\Model\AbstractModel
{
    
    protected function _construct()
    {
        $this->_init('Wise\Liqpay\Model\ResourceModel\Transaction');
    }
    
}
