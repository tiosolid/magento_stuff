<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Mage
 * @package    Phoenix_BankPayment
 * @copyright  Copyright (c) 2010 Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Phoenix_BankPayment_Model_BankPayment extends Mage_Payment_Model_Method_Abstract
{
    /**
    * unique internal payment method identifier
    *
    * @var string [a-z0-9_]
    */
    protected $_code = 'bankpayment';

    protected $_formBlockType = 'bankpayment/form';
    protected $_infoBlockType = 'bankpayment/info';
    protected $_accounts;

    /**
     * @deprecated since 0.3.0
     * support BC for old templates
     */
    public function getAccountHolder()
    {
        if ($accounts = $this->getAccounts()) {
            return $accounts[0]->getAccountHolder();
        }
        return null;
    }

    /**
     * @deprecated since 0.3.0
     * support BC for old templates
     */
    public function getAccountNumber()
    {
        if ($accounts = $this->getAccounts()) {
            return $accounts[0]->getAccountNumber();
        }
        return null;
    }

    /**
     * @deprecated since 0.3.0
     * support BC for old templates
     */
    public function getSortCode()
    {
        if ($accounts = $this->getAccounts()) {
            return $accounts[0]->getSortCode();
        }
        return null;
    }

    /**
     * @deprecated since 0.3.0
     * support BC for old templates
     */
    public function getBankName()
    {
        if ($accounts = $this->getAccounts()) {
            return $accounts[0]->getBankName();
        }
        return null;
    }

    /**
     * @deprecated since 0.3.0
     * support BC for old templates
     */
    public function getIBAN()
    {
        if ($accounts = $this->getAccounts()) {
            return $accounts[0]->getIBAN();
        }
        return null;
    }

    /**
     * @deprecated since 0.3.0
     * support BC for old templates
     */
    public function getBIC()
    {
        if ($accounts = $this->getAccounts()) {
            return $accounts[0]->getBIC();
        }
        return null;
    }

    public function getPayWithinXDays()
    {
        return $this->getConfigData('paywithinxdays');
    }

    public function getCustomText($addNl2Br = true)
    {
        $customText = $this->getConfigData('customtext');
        if ($addNl2Br) {
            $customText = nl2br($customText);
        }
        return $customText;
    }

    public function getAccounts()
    {
        if (!$this->_accounts) {
            $paymentInfo = $this->getInfoInstance();
            if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
                $storeId = $paymentInfo->getOrder()->getStoreId();
            } else {
                $storeId = $paymentInfo->getQuote()->getStoreId();
            }

            $currentOrder = Mage::registry('current_order');
            $storeId = $currentOrder ? $currentOrder->getStoreId() : null;
            $accounts = unserialize(Mage::getStoreConfig('payment/bankpayment/bank_accounts',$storeId));

            $this->_accounts = array();
            $fields = is_array($accounts) ? array_keys($accounts) : null;
            if (!empty($fields)) {
                foreach ($accounts[$fields[0]] as $i => $k) {
                    if ($k) {
                        $account = new Varien_Object();
                        foreach ($fields as $field) {
                            $account->setData($field,$accounts[$field][$i]);
                        }
                        $this->_accounts[] = $account;
                    }
                }
            }
        }
        return $this->_accounts;
    }
	
	public function addOrderTransaction($payment, $additionalInfo) { 
		$payment->setTransactionId($additionalInfo['transfer_number']);
		foreach ($additionalInfo as $key => $value) {
			if ($key == 'type') {
				$payment->setTransactionAdditionalInfo(Mage::helper('bankpayment')->__($key), $this->getConfirmationTypeById($value));
			}
			else {
				$payment->setTransactionAdditionalInfo(Mage::helper('bankpayment')->__($key), $value);
			}
		}
		$payment->AddTransaction('payment', null, true);
		$payment->save();	
		return $this;
	}
	
	public function updateOrderStatus($order) {
		$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING,
					Mage_Sales_Model_Order::STATE_PROCESSING,
					Mage::getStoreConfig('payment/bankpayment/confirmation_message'),
					Mage::getStoreConfig('payment/bankpayment/confirmation_email_active')
				);
		$order->save();
		return $this;
	}
	
	public function getConfirmationTypeById($confirmation_id) {
		return Mage::getModel('bankpayment/source_confirmationType')->getTypeById($confirmation_id);
	}
	
	/* Checks if:
	 * - An order belongs to the current logged in user;
	 * - order was really paid using the Bank Payment Method;
	 * - If its still in NEW state (you cant [shouldnt] confirm already processing orders)
	 */
	public function canConfirmOrder($order_id) {
		$user = Mage::getSingleton('customer/session')->getCustomer();
		$order = Mage::getModel('sales/order')->load($order_id);
		if (($user != null) && ($order != null) && 
				($user->getId() == $order->getCustomerId()) &&
				$order->getPayment()->getMethod() == 'bankpayment' &&
				$order->getState() == (Mage_Sales_Model_Order::STATE_NEW)) {
			return true;
		}
		else { return false; }
	}
}
