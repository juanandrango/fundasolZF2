<?php
namespace Fundasol\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController {
    
    /**
    * It is the Entity manager provided by Doctrine
    * @var Service
    */
    protected $objectManager;

    private function updateCounts() {
    	$this->objectManager = $this->getObjectManager();
    	\Client\Entity\Client::$count = count($this->getClientRepository()->findAll());
		\Account\Entity\Account::$count = count($this->getAccountRepository()->findAll());
    }

    /**
    * @return Service Entity Manager instance
    */
    private function getObjectManager() {
        return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    }
    private function getClientRepository() {
        return $this->objectManager->getRepository('Client\Entity\Client');
    }
    private function getAccountRepository() {
        return $this->objectManager->getRepository('Account\Entity\Account');
    }
    private function getPaymentRepository() {
        return $this->objectManager->getRepository('Payment\Entity\Payment');
    }

    public function indexAction() {
    	$this->updateCounts();
    	$this->objectManager = $this->getObjectManager();
        return new ViewModel(array(
        	'payments'	=> $this->getPaymentRepository()->findAll()
        	)
        );
    }
}
