<?php
class Wuunder_WuunderConnector_Model_Mysql4_Shipments_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct() {
        parent::_construct();
        $this->_init('wuunderconnector/shipments');
    }

}