<?php
/**
 * Copyright © 2016 Wise Ltd. All rights reserved.
 * Released under the Open Software License (OSL 3.0)
 * Please visit http://opensource.org/licenses/osl-3.0.php for the full text of the OSL 3.0 license
 */
namespace Wise\Liqpay\Model\ResourceModel;

class Transaction extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    
    protected function _construct()
    {
        $this->_init('liqpay_transaction', 'entity_id');
    }
    
}
