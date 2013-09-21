<?php

namespace Investor\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Request;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;

class InvestorController extends AbstractActionController {
    
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


    // ============================== Action Methods ============================== //
    public function showAllAction() {
        $this->updateCounts();
        $this->objectManager = $this->getObjectManager();
        return new ViewModel( array(
            'allInvestors' => $this->getRepository('Investor')->findAll()
            ) 
        );
    }

    public function showAction() {
        $this->updateCounts();
        if ($this->params()->fromRoute('investorId', 0) != "") {
            $investorId = (int)$this->params()->fromRoute('investorId', 0);                
            $this->objectManager = $this->getObjectManager();        
            $investor = $this->getRepository('Investor')->findOneBy(array("investorId" => $investorId));
            if ($investor != null) {
                $builder = new AnnotationBuilder($this->objectManager);
                $form = $builder->createForm($investor);
                $form->setHydrator(new DoctrineHydrator($this->objectManager,'Investor\Entity\Investor'));
                $form->setBindOnValidate(false);
                $form->bind($investor);
                return new ViewModel( array(
                    'investor' => $investor, 
                    'form' => $form
                    ) 
                );    
            }
        } 
        $this->redirect()->toRoute('home');
    }
    
    public function editAction() {
        $this->updateCounts();
        if ($this->getRequest()->isPost()) {
            $this->objectManager = $this->getObjectManager();        
            $investorId = (int)$this->getRequest()->getPost('investorId');
            $investor = $this->getRepository('Investor')->findOneBy(array("investorId" => $investorId));
            if ($investor != null) {
                $builder = new AnnotationBuilder($this->objectManager);
                $form = $builder->createForm($investor);
                $form->setHydrator(new DoctrineHydrator($this->objectManager,'Investor\Entity\Investor'));
                $form->bind($investor);
                $form->setData($this->getRequest()->getPost());
                if ($form->isValid()) {
                    $this->objectManager->flush();
                    $investorId = $investor->getInvestorId();
                }
                return new ViewModel( array(
                    'investor'      => $investor, 
                    'form'          => $form
                    ) 
                );  
            }                      
        }
        $this->redirect()->toRoute('home');
    }

    public function addAction() {        
        $this->updateCounts();
        $this->objectManager = $this->getObjectManager();        
        $builder = new AnnotationBuilder($this->objectManager);
        $form = $builder->createForm(new \Investor\Entity\Investor);
        $form->setHydrator(new DoctrineHydrator($this->objectManager,'Investor\Entity\Investor'));
        $investor = new \Investor\Entity\Investor;
        $form->bind($investor);
        $form->setData($this->getRequest()->getPost());
        if ($this->getRequest()->isPost()) {           
            $stateId = $this->getRequest()->getPost('stateId');
            if ($form->isValid() && \Investor\Entity\Investor::isUniqueStateId($this->objectManager, $stateId, $form)) {
                $investor->setTimeStamp();
                $this->objectManager->persist($investor);          
                $this->objectManager->flush();
                $investorId = $investor->getInvestorId();                             
                $this->redirect()->toRoute('investors/Investor', 
                    array(
                        'action'        => 'show',
                        'investorId'    => $investorId, 
                    )
                );                 
            }            
        }
        return new ViewModel( array('form' => $form)); 
    }
}
