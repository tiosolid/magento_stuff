<?php
class Phoenix_BankPayment_Model_Observer
{
	public function sendInstructionEmail($observer) {
		$quote = $observer['quote'];
		$order = $observer['order'];
		if (Mage::getStoreConfig('payment/bankpayment/instruction_email_active') &&
				($quote->getPayment()->getMethod() == 'bankpayment')) {
			Mage::getModel('bankpayment/email')->sendInstructionEmail($order);
		}
	}
}
