<?php

namespace Client\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Request;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;

class ClientController extends AbstractActionController {
    
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
            'allClients' => $this->getRepository('Client')->findAll()
            ) 
        );
    }

    public function showAction() {
        $this->updateCounts();
        if ($this->params()->fromRoute('clientId', 0) != "") {
            $clientId = (int)$this->params()->fromRoute('clientId', 0);
            $this->objectManager = $this->getObjectManager();        
            $client = $this->getRepository('Client')->findOneBy(array("clientId" => $clientId));
            if ($client != null) {
                $builder = new AnnotationBuilder($this->objectManager);
                $form = $builder->createForm($client);
                $form->setHydrator(new DoctrineHydrator($this->objectManager,'Client\Entity\Client'));
                $form->setBindOnValidate(false);
                $form->bind($client);
                return new ViewModel( array(
                    'client' => $client, 
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
            $clientId = (int)$this->getRequest()->getPost('clientId');            
            $this->objectManager = $this->getObjectManager();                    
            $client = $this->getRepository('Client')->findOneBy(array("clientId" => $clientId));
            if ($client != null) {
                $builder = new AnnotationBuilder($this->objectManager);
                $form = $builder->createForm($client);
                $form->setHydrator(new DoctrineHydrator($this->objectManager,'Client\Entity\Client'));
                $form->bind($client);
                $form->setData($this->getRequest()->getPost());
                if ($form->isValid()) {
                    $this->objectManager->flush();
                    $clientId = $client->getClientId();
                }
                return new ViewModel( array(
                    'client'    => $client, 
                    'form'      => $form
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
        $form = $builder->createForm(new \Client\Entity\Client);
        $form->setHydrator(new DoctrineHydrator($this->objectManager,'Client\Entity\Client'));
        $client = new \Client\Entity\Client;
        $form->bind($client);
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            $stateId = $this->getRequest()->getPost('stateId');            
            if ($form->isValid() && \Client\Entity\Client::isUniqueStateId($this->objectManager, $stateId, $form)) {
                $client->setTimeStamp();
                $this->objectManager->persist($client);          
                $this->objectManager->flush();
                $clientId = $client->getClientId();                             
                $this->redirect()->toRoute('clients/Client', 
                    array(
                        'action'    => 'show',
                        'clientId'  => $clientId, 
                    )
                ); 
            }
        }
        return new ViewModel( array('form' => $form)); 
    }
}
