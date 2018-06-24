<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Phoenix
 * @package    Phoenix_BankPayment
 * @copyright  Copyright (c) 2010 Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)
 */

class Phoenix_BankPayment_Model_Source_ConfirmationType
{
    protected $_options;   
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = array(
                array(
                    'value' => 1,
                    'label' => Mage::helper('bankpayment')->__('Money Order'),
                    ),
                array(
                    'value' => 2,
                    'label' => Mage::helper('bankpayment')->__('Wire Transfer'),
                    )
                );
        }
        return $this->_options;
    }
	
	//Return only enabled types
	public function toEnabledOptionArray() {
		if (Mage::getStoreConfig('payment/bankpayment/type_active')) {
			$options = $this->toOptionArray();
			$enabledOptions = $this->getEnabledOptionIdArray();
			$enabledArray = array();
			foreach ($options as $option) {
				if (in_array($option['value'], $enabledOptions)) {
					$enabledArray[] = $option;
				}
			}
		}
		else { return null; };
		if (count($enabledArray)) { return $enabledArray;}  else { return null; }
	}
	
	//Return an array with the IDs enabled in the admin panel
	private function getEnabledOptionIdArray() {
		return explode(",", Mage::getStoreConfig('payment/bankpayment/confirmation_type'));
	}
	
	public function getTypeById($confirmation_id) {
		$options = $this->toOptionArray();
		foreach ($options as $option) {
			if ($option['value'] == $confirmation_id) { return $option['label']; }
		}
		return null;
	}
}