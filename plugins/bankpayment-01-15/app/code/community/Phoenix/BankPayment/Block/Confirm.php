<?php
class Phoenix_BankPayment_Block_Confirm extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
	public function getOrder() { 
		return Mage::getSingleton('sales/order');
	}
	
	public function getConfirmationType() {
		return Mage::getModel('bankpayment/source_confirmationType')->toEnabledOptionArray();
	}
	
	public function getBackUrl() {
		return $this->getUrl('customer/account');
	}

	public function getConfirmationUrl($order_id) {
		return $this->getUrl('bankpayment/order/confirmPost', array('order_id' => $order_id));
	}	
	
	public function getBankAccount() {
		$accounts = unserialize(Mage::getStoreConfig('payment/bankpayment/bank_accounts'));
		$accountsArray = array();
		$fields = is_array($accounts) ? array_keys($accounts) : null;
		if (!empty($fields)) {
			foreach ($accounts[$fields[0]] as $i => $k) {
				if ($k) {
					$account = new Varien_Object();
					foreach ($fields as $field) {
						$account->setData($field,$accounts[$field][$i]);
					}
					$accountsArray[] = $account;
				}
			}
		}
		return $accountsArray;
	}
}