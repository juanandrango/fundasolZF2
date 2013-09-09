<?php

namespace Investor\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Request;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;

class InvestorController extends AbstractActionController {
    
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

    private function updateCounts() {
        $this->objectManager = $this->getObjectManager();
        \Client\Entity\Client::$count = count($this->getClientRepository()->findAll());
        \Investor\Entity\Investor::$count = count($this->getInvestorRepository()->findAll());
        \Account\Entity\Account::$count = count($this->getAccountRepository()->findAll());
        \Contribution\Entity\Contribution::$count = count($this->getContributionRepository()->findAll());
    }

    public function showAllAction() {
        $this->updateCounts();
        $this->objectManager = $this->getObjectManager();
        return new ViewModel( array(
            'allInvestors' => $this->getInvestorRepository()->findAll()
            ) 
        );
    }

    public function showAction() {
        $this->updateCounts();
        if ($this->params()->fromRoute('investorId', 0) != "") {
            $this->objectManager = $this->getObjectManager();        
            $investorId = (int)$this->params()->fromRoute('investorId', 0);
            $investor = $this->getInvestorRepository()->findOneBy(array("investorId" => $investorId));
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
            $stateId = (int)$this->getRequest()->getPost('investorId');            
            $investor = $this->getInvestorRepository()->findOneBy(array("investorId" => $stateId));
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
                    'investor'    => $investor, 
                    'form'      => $form,
                    'investorId'  => $investorId,
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
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            $uniqueStateId = $this->getRequest()->getPost('stateId');
            $nullInvestor = $this->getInvestorRepository()->findOneBy(array("stateId" => $uniqueStateId));
            if ($form->isValid()) {
                if ($nullInvestor != null) {
                    $form->get("stateId")->setMessages(array("Repeated State Id"));
                } else {
                    $investor->setTimeStamp();
                    $this->objectManager->persist($investor);          
                    $this->objectManager->flush();
                    $investorId = $investor->getInvestorId();                             
                    $this->redirect()->toRoute('investors/Investor', 
                        array(
                            'action'    => 'show',
                            'investorId'  => $investorId, 
                        )
                    );
                } 
            }
        }
        return new ViewModel( array('form' => $form)); 
    }

    public function deleteAction() {
        $this->updateCounts();
        $investorId = $this->getRequest()->getPost('investorId');
        if ($this->getRequest()->getPost('sureDelete') == 'yes') {
            $this->objectManager = $this->getObjectManager();        
            $investor = $this->getInvestorRepository()->findOneBy(array("investorId" => $investorId));
            if ($investor != null) {
                $this->objectManager->remove($investor);
                $this->objectManager->flush();
                 $this->redirect()->toRoute('investors/Investor', 
                    array(
                        'action' => 'showAll',
                    )
                );
            } else {
                //No Investor found
                $this->redirect()->toRoute('home');
            }
        } else if($this->getRequest()->getPost('sureDelete') == 'no') {
             $this->redirect()->toRoute('investors/Investor', 
                array(
                    'action' => 'show',
                    'investorId' => $investorId, 
                )
            );
        } else {
            return new ViewModel( array('investorId' => $investorId));
        }
    }
}
