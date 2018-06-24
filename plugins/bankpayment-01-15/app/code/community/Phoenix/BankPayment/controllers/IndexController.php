<?php
class Phoenix_BankPayment_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/deposito?id=15 
    	 *  or
    	 * http://site.com/deposito/id/15 	
    	 */
    	
		$deposito_id = $this->getRequest()->getParam('id');
die('bankpayment');
  		if($deposito_id != null && $deposito_id != '')	{
			$deposito = Mage::getModel('bankpayment/deposito')->load($deposito_id)->getData();
			//$deposito = "pedido numero " . $deposito_id;
		} else {
			$deposito = null;
		}	
		
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($deposito == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$depositoTable = $resource->getTableName('deposito');
			
			$select = $read->select()
			   ->from($depositoTable,array('deposito_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$deposito = $read->fetchRow($select);
		} */
		Mage::register('deposito', $deposito);
		

			
		$this->loadLayout();     
		$this->renderLayout();
    }
	
	public function confirmAction() {
		$deposito_id = $this->getRequest()->getParam('id');
die('bankpayment');
  		if($deposito_id != null && $deposito_id != '')	{
			$deposito = Mage::getModel('bankpayment/deposito')->load($deposito_id)->getData();
			//$deposito = "pedido numero " . $deposito_id;
		} else {
			$deposito = null;
		}		
	
	}
}