<?php
namespace Fundasol\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController {
    
   // ------------------------------ Properties ------------------------------ //
    /**
    * It is the Entity manager provided by Doctrine
    * @var Service
    */
    protected $objectManager;

    // ------------------------------ Methods ------------------------------ //
    /**
    * @return Service Entity Manager instance
    */
    private function getObjectManager() {
        return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    }   

    /**
    * @param string $repoStr The name of the Class 
    * @return Repository Should return the corresponding repository
    */
    private function getRepository($repoStr) {
        if ($this->objectManager == null) {
            $this->objectManager = $this->getObjectManager();
        }
        return $this->objectManager->getRepository($repoStr . "\\Entity\\" . $repoStr);
    }


    private function updateCounts() {
        if ($this->objectManager == null) {
            $this->objectManager = $this->getObjectManager();        
        }
        \Client\Entity\Client::updateCount($this->objectManager);
        \Investor\Entity\Investor::updateCount($this->objectManager);
        \Account\Entity\Account::updateCount($this->objectManager);
        \Contribution\Entity\Contribution::updateCount($this->objectManager);
    }

    public function indexAction() {
    	$this->updateCounts();
    	$this->objectManager = $this->getObjectManager();
        return new ViewModel(array(
        	'payments'		=> $this->getRepository('Payment')->findAll(),
        	'accounts' 		=> $this->getRepository('Account')->findAll(),
        	'contributions' => $this->getRepository('Contribution')->findAll(),
        	'rangeStart'	=> $this->getRequest()->getPost('rangeStart'),
        	'rangeEnd'		=> $this->getRequest()->getPost('rangeEnd'),
        	)
        );
    }
}
