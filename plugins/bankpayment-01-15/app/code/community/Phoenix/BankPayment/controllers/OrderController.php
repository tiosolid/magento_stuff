<?php
class Phoenix_BankPayment_OrderController extends Mage_Core_Controller_Front_Action
{
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }
	
	//Shows the confirmation form
	public function confirmAction() {
		$order_id = $this->getRequest()->getParam('order_id');
		//if (!$this->_validateFormKey()) {
        //    return $this->_redirect('*/*/');
        //}
		//User must be logged in 
		if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
			$this->_getSession()->setBeforeAuthUrl($this->getRequest()->getRequestUri());
			return $this->_redirect('customer/account/login');
		}
		$model = Mage::getModel('bankpayment/bankPayment');
  		if($order_id == null || $order_id == '' || !$model->canConfirmOrder($order_id)) {
			$this->_getSession()->addError($this->__('Invalid Order'));
			$this->_redirect('customer/account');			
		}
		else {
			Mage::getSingleton('sales/order')->load($order_id);
			$handles = array('default');
			if (Mage::getSingleton('customer/session')->isLoggedIn()) {
				$handles[] = 'customer_account';
			}
			$this->loadLayout($handles);
			$this->renderLayout();			
		}
	}
	
	public function confirmPostAction() {
		if ($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getParams();
			$order = Mage::getModel('sales/order')->load($data['order_id']);
			if ($order != null) {
				$payments = $order->getPaymentsCollection();
				foreach ($payments as $payment) {
					if ($payment->getMethod() == 'bankpayment') {						
						$model = Mage::getModel('bankpayment/bankPayment');
						
						//Save the confirmation as a transaction
						$model->addOrderTransaction($payment, $data);
						
						//Update order status
						$model->updateOrderStatus($order);
						
						//Send confirmation email to administrator
						Mage::getModel('bankpayment/email')->sendNotificationEmail($order, $data);
						
						//Redirect the customer
						$this->_getSession()->addSuccess($this->__('Payment Successfully Confirmed'));
						$this->_redirectSuccess(Mage::getUrl('customer/account'));			
						return;						
					}
				}
			}
			else { 
				$this->_getSession()->addError($this->__('Invalid Order'));
				$this->_redirect('customer/account');
			}
		}
		$this->_redirect('customer/account');
	}
}