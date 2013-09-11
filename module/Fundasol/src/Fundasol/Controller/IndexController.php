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

    /**
    * @return Service Entity Manager instance
    */
    private function getObjectManager() {
        return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    }
    private function getClientRepository() {
        return $this->objectManager->getRepository('Client\Entity\Client');
    }
    private function getContributionRepository() {
        return $this->objectManager->getRepository('Contribution\Entity\Contribution');
    }
    private function getAccountRepository() {
        return $this->objectManager->getRepository('Account\Entity\Account');
    }
    private function getInvestorRepository() {
        return $this->objectManager->getRepository('Investor\Entity\Investor');
    }
    private function getPaymentRepository() {
        return $this->objectManager->getRepository('Payment\Entity\Payment');
    }

    private function updateCounts() {
        $this->objectManager = $this->getObjectManager();
        \Client\Entity\Client::$count = count($this->getClientRepository()->findAll());
        \Investor\Entity\Investor::$count = count($this->getInvestorRepository()->findAll());
        \Account\Entity\Account::$count = count($this->getAccountRepository()->findAll());
        \Contribution\Entity\Contribution::$count = count($this->getContributionRepository()->findAll());
    }

    public function indexAction() {
    	$this->updateCounts();
    	$this->objectManager = $this->getObjectManager();
        return new ViewModel(array(
        	'payments'		=> $this->getPaymentRepository()->findAll(),
        	'accounts' 		=> $this->getAccountRepository()->findAll(),
        	'contributions' => $this->getContributionRepository()->findAll(),
        	'rangeStart'	=> $this->getRequest()->getPost('rangeStart'),
        	'rangeEnd'		=> $this->getRequest()->getPost('rangeEnd'),
        	)
        );
    }
}
