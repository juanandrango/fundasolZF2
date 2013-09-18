<?php

namespace Contribution\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Request;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;

class ContributionController extends AbstractActionController {
    
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
            'allContributions' => $this->getRepository('Contribution')->findAll() 
            )
        );
    }

    public function showAction() {
        $this->updateCounts();
        if ($this->params()->fromRoute('contributionId', 0) != "") {
            $this->objectManager = $this->getObjectManager();
            $contributionId = $this->params()->fromRoute('contributionId', 0);
            $contribution = $this->getRepository('Contribution')->findOneBy(array("contributionId" => $contributionId));    
            if ($contribution != null) {
                return new ViewModel( array(
                    'contribution' => $contribution
                    ) 
                );    
            }
        }
        $this->redirect()->toRoute('home'); 
    }

    public function addAction() {
        $this->updateCounts();    
        if($this->getRequest()->isPost()) {
            $investorId = (int)$this->getRequest()->getPost('investorId');
            if ($investorId != null) {
                $this->objectManager = $this->getObjectManager();            
                $investor = $this->getRepository('Investor')->findOneBy(array('investorId' => $investorId));
                if ($investor != null) {
                    $contribution = new \Contribution\Entity\Contribution;                    
                    $builder = new AnnotationBuilder($this->objectManager);
                    $form = $builder->createForm(new \Contribution\Entity\Contribution);
                    $form->setHydrator(new DoctrineHydrator($this->objectManager,'Contribution\Entity\Contribution'));                                       
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
                                    'action'            => 'show',
                                    'contributionId'    => $contribution->getContributionId()
                                )
                            );
                        } 
                    } 
                    return new ViewModel( 
                        array(
                            'form'          => $form, 
                            'investorId'    => $investorId
                        )
                    );
                } 
            }
        }
        return $this->redirect()->toRoute('home');
    }
}
