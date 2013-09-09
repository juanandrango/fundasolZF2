<?php

namespace Contribution\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Request;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;

class ContributionController extends AbstractActionController {
    
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
            'allContributions' => $this->getContributionRepository()->findAll() 
            )
        );
    }

    public function showAction() {
        $this->updateCounts();
        if ($this->params()->fromRoute('contributionId', 0) != "") {
            $this->objectManager = $this->getObjectManager();
            $contributionId = $this->params()->fromRoute('contributionId', 0);
            $contribution = $this->getContributionRepository()->findOneBy(array("contributionId" => $contributionId));    
            return new ViewModel( array(
                'contribution' => $contribution
                ) 
            );
        }
        $this->redirect()->toRoute('home'); 
    }

    public function addAction() {
        $this->updateCounts();    
        if($this->getRequest()->isPost()) {
            $this->objectManager = $this->getObjectManager();            
            $builder = new AnnotationBuilder($this->objectManager);
            $form = $builder->createForm(new \Contribution\Entity\Contribution);
            $form->setHydrator(new DoctrineHydrator($this->objectManager,'Contribution\Entity\Contribution'));           
            $investorId = (int)$this->getRequest()->getPost('investorId');
            if ($investorId != null) {
                $investor = $this->getInvestorRepository()->findOneBy(array('investorId' => $investorId));
                if ($investor != null) {
                    $contribution = new \Contribution\Entity\Contribution;
                    $form->bind($contribution);
                    if ($this->getRequest()->getPost('addContributionSubmit') != null) {
                        $form->setData($this->getRequest()->getPost());                                                
                        if ($form->isValid()) {                         
                            $contribution->setInvestor($investor);
                            $contribution->initAdd($this->objectManager);                            
                            $this->objectManager->persist($contribution);
                            $investor->getContributions()->add($contribution);
                            $this->objectManager->flush();                                              
                            return $this->redirect()->toRoute('contributions/Contribution', 
                                array(
                                    'action'    => 'show',
                                    'contributionId'   => $contribution->getContributionId()
                                )
                            );
                        } 
                    } 
                    //First Time 
                    return new ViewModel( array('form' => $form, 'investorId' => $investorId));
                } 
                //No investor Found with investor ID
            }
            //No investor ID given
        }
        //No Post at all
        return $this->redirect()->toRoute('home');
    }
}
