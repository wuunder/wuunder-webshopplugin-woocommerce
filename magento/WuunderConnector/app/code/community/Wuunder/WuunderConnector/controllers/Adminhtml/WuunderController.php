<?php
class Wuunder_WuunderConnector_Adminhtml_WuunderController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/wuunderconnector');
    }

    public function indexAction()
    {
    }

    public function createAction()
    {
        try {

            $wuunderEnabled = Mage::getStoreConfig('wuunderconnector/connect/enabled');

            if ($wuunderEnabled == 0) {

                Mage::getSingleton('adminhtml/session')->addError('Error: WuunderConnector disabled');

            } else {

                $orderId = $this->getRequest()->getParam('id', null);
                Mage::register('wuuder_order_id', $orderId);
                Mage::helper('wuunderconnector')->log('Controller: createAction - Order ID = '.$orderId);

                $this->loadLayout();
                $this->renderLayout();
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }

    public function processLabelAction()
    {

        if ($this->getRequest()->getPost()) {

            try {

                $data = $this->getRequest()->getPost();

                Mage::helper('wuunderconnector')->log('Controller: processLabelAction - Data', null, 'wuunder.log');
                Mage::helper('wuunderconnector')->log($data);

                $messageField = ($infoArray['label_type'] == 'retour') ? 'retour_message' : 'personal_message';

                $infoArray = array (
                    'order_id'          => $data['order_id'],
                    'label_id'          => $data['label_id'],
                    'label_type'        => $data['label_type'],
                    'packing_type'      => $data['type'],
                    'length'            => $data['length'],
                    'width'             => $data['width'],
                    'height'            => $data['height'],
                    'weight'            => $data['weight'],
                    'reference'         => $data['reference'],
                    $messageField       => $data['personal_message'],
                    'phone_number'      => $data['phone_number'],
                );

Mage::helper('wuunderconnector')->log($infoArray);

                $result = Mage::helper('wuunderconnector')->processLabelInfo($infoArray);

                if ($result['error'] === true) {
                    Mage::getSingleton('adminhtml/session')->addError($result['message']);
                } else {
                    Mage::getSingleton('adminhtml/session')->addSuccess($result['message']);
                }

                $this->_redirect('*/sales_order/index');
                return $this;

            } catch (Exception $e) {

                $this->_getSession()->addError(Mage::helper('wuunderconnector')->__('An error occurred while saving the data'));
                Mage::logException($e);
                $this->_redirect('*/*/create');
                return $this;
            }
        }
    }
}