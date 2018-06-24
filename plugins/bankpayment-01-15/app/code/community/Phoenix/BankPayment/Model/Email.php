<?php
class Phoenix_BankPayment_Model_Email
{
	//Sends email to client with information in how
	//to confirm the payment
	public function sendInstructionEmail($order) {
		if (Mage::getStoreConfig('payment/bankpayment/instruction_email_active')) {
			#$templateId = Mage::getStoreConfig('payment/bankpayment/instruction_email/template');
			$templateId = 'bankpayment_instruction_email_template';
			
			$subject = Mage::getStoreConfig('payment/bankpayment/instruction_email_subject');
			if ($subject == null) { 
				$subject = Mage::helper('bankpayment')->__('How to Confirm Your Payment');
			} else {
				$subject = $this->parseText($subject, $order);
			}
			
			$body = Mage::getStoreConfig('payment/bankpayment/instruction_email_body');
			if ($body == null) { 
				$body = Mage::helper('bankpayment')->__('Do not forget to confirm your payment');
			} else { 
				$body = $this->parseText($body, $order);
			}
			
			$customerFullname = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
			$vars = Array('customerFullName' =>  $customerFullname,
						'body' => $body,
						'customerAccountUrl' => $this->getCustomerAccountUrl());
			
			$this->sendEmail($templateId, $subject, $order->getCustomerEmail(), $customerFullname, $vars);
		}
	}
	
	//Sends email to store admin when a new payment is confirmed
	public function sendNotificationEmail($order, $confirmationData) {
		if (Mage::getStoreConfig('payment/bankpayment/notification_email_active')) {
			#$templateId = Mage::getStoreConfig('payment/bankpayment/notification_email/template');	
			$templateId = 'bankpayment_notification_email_template';
			
			$subject = Mage::getStoreConfig('payment/bankpayment/notification_email_subject');
			if ($subject == null) { 
				$subject = Mage::helper('bankpayment')->__('New Payment Confirmed');
			} else {
				$subject = $this->parseText($subject, $order);
			}
			
			$recipient = Mage::getStoreConfig('payment/bankpayment/notification_email_recipient');
			$recipientEmail =  Mage::getStoreConfig('trans_email/ident_'.$recipient.'/email');
			$recipientName = Mage::getStoreConfig('trans_email/ident_'.$recipient.'/name');
						
			$vars = Array('recipientName' =>  $recipientName,
						'confirmationData' => $this->confirmationDataToHtml($confirmationData),
						'adminOrderUrl' => $this->getAdminOrderUrl($order));

			$this->sendEmail($templateId, $subject, $recipientEmail, $recipientName, $vars);
		}
	}
	
	private function sendEmail($templateId, $subject, $sendTo, $name, $data) {
		$storeId = Mage::app()->getStore()->getId(); 
		$sender = Array('name'  => Mage::getStoreConfig('trans_email/ident_general/name'),
					  'email' => Mage::getStoreConfig('trans_email/ident_general/email'));

		$translate  = Mage::getSingleton('core/translate');
		$translate->setTranslateInline(true);
		Mage::getModel('core/email_template')
		  ->setTemplateSubject($subject)
		  ->sendTransactional($templateId, $sender, $sendTo, $name, $data, $storeId);
	}
	
	//Return the parsed subject / body text
	private function parseText($subject, $order) {
		//allowed variables
		$search = array('%order_id%');
		$replace = array($order->getIncrementId());
		
		return str_replace($search, $replace, $subject);
	}
	
	//convert confirmation data array as html 
	private function confirmationDataToHtml($data) {
		if (!is_array($data)) { return null; }
		$model = Mage::getModel('bankpayment/bankPayment');
		
		$htmlData = '<table>';
		foreach ($data as $key => $value) {
			if ($key == 'type') {
				$htmlData = $htmlData . '<tr>' . 
					'<td>' . Mage::helper('bankpayment')->__($key) . '</td>' .
					'<td>' . $model->getConfirmationTypeById($value) . '</td>' .
				'</tr>';
			}
			else {
				$htmlData = $htmlData . '<tr>' . 
					'<td>' . Mage::helper('bankpayment')->__($key) . '</td>' .
					'<td>' . $value . '</td>' .
				'</tr>';
			}
		}		
		$htmlData = $htmlData . '</table>';
		return $htmlData;
	}
	
	//Returns the order admin URL
	private function getAdminOrderUrl($order) {
		return Mage::helper('adminhtml')->getUrl('adminhtml/sales_order/view', array('order_id' => $order->getId()));
	}
	
	private function getCustomerAccountUrl() {
		return Mage::getUrl('customer/account');	
	}
}
