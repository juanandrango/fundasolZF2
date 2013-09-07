<?php

namespace Client\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Request;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;

class ClientController extends AbstractActionController {
    
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

    public function showAllAction() {
        $this->objectManager = $this->getObjectManager();
        return new ViewModel( array(
            'allClients' => $this->getClientRepository()->findAll()
            ) 
        );
    }

    public function showAction() {
        if ($this->params()->fromRoute('clientId', 0) != "") {
            $this->objectManager = $this->getObjectManager();        
            $clientId = (int)$this->params()->fromRoute('clientId', 0);
            $client = $this->getClientRepository()->findOneBy(array("clientId" => $clientId));
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
        if ($this->getRequest()->isPost()) {
            $this->objectManager = $this->getObjectManager();        
            $stateId = (int)$this->getRequest()->getPost('clientId');            
            $client = $this->getClientRepository()->findOneBy(array("clientId" => $stateId));
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
                    'form'      => $form,
                    'clientId'  => $clientId,
                    ) 
                );  
            }                      
        }
        $this->redirect()->toRoute('home');
    }

    public function addAction() {
        $this->objectManager = $this->getObjectManager();        
        $builder = new AnnotationBuilder($this->objectManager);
        $form = $builder->createForm(new \Client\Entity\Client);
        $form->setHydrator(new DoctrineHydrator($this->objectManager,'Client\Entity\Client'));
        $client = new \Client\Entity\Client;
        $form->bind($client);
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            $uniqueStateId = $this->getRequest()->getPost('stateId');
            $nullClient = $this->getClientRepository()->findOneBy(array("stateId" => $uniqueStateId));
            if ($form->isValid()) {
                if ($nullClient != null) {
                    $form->get("stateId")->setMessages(array("Repeated State Id"));
                } else {
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
        }
        return new ViewModel( array('form' => $form)); 
    }

    public function deleteAction() {
        $clientId = $this->getRequest()->getPost('clientId');
        if ($this->getRequest()->getPost('sureDelete') == 'yes') {
            $this->objectManager = $this->getObjectManager();        
            $client = $this->getClientRepository()->findOneBy(array("clientId" => $clientId));
            if ($client != null) {
                $this->objectManager->remove($client);
                $this->objectManager->flush();
                 $this->redirect()->toRoute('clients/Client', 
                    array(
                        'action' => 'showAll',
                    )
                );
            } else {
                //No Client found
                $this->redirect()->toRoute('home');
            }
        } else if($this->getRequest()->getPost('sureDelete') == 'no') {
             $this->redirect()->toRoute('clients/Client', 
                array(
                    'action' => 'show',
                    'clientId' => $clientId, 
                )
            );
        } else {
            return new ViewModel( array('clientId' => $clientId));
        }
    }
}
