<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Client\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Request;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;

class ClientController extends AbstractActionController
{
    public function showAllAction()
    {
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $allClients = $objectManager->getRepository('Client\Entity\Client')->findAll();
        return new ViewModel( array('allClients' => $allClients) );
    }

    public function showAction() 
    {
        if ($this->params()->fromRoute('clientId', 0) != "") {
            $clientId = (int)$this->params()->fromRoute('clientId', 0);
            $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
            $client = $objectManager->getRepository('Client\Entity\Client')->findOneBy(array("clientId" => $clientId));
            //Create form 
            //TODO This should be a form under form/ directory
            $builder = new AnnotationBuilder($objectManager);
            $form = $builder->createForm($client);
            $form->setHydrator(new DoctrineHydrator($objectManager,'Client\Entity\Client'));
            $form->setBindOnValidate(false);
            $form->bind($client);
            return new ViewModel( array(
                'client' => $client, 
                'form' => $form
                ) 
            );
        } else {
            die(var_dump($this->params()->fromRoute()));
            //return;
        }
    }
    public function editAction() {
        if ($this->getRequest()->isPost()) {            
            $stateId = (int)$this->getRequest()->getPost('clientId');            
            $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
            $client = $objectManager->getRepository('Client\Entity\Client')->findOneBy(array("clientId" => $stateId));
            //Create form 
            //TODO This should be a form under form/ directory
            $builder = new AnnotationBuilder($objectManager);
            $form = $builder->createForm($client);
            $form->setHydrator(new DoctrineHydrator($objectManager,'Client\Entity\Client'));
            $form->bind($client);
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $objectManager->flush();
                $clientId = $client->getClientId();
            }
            return new ViewModel( array(
                'client' => $client, 
                'form' => $form,
                'clientId' => $clientId,
                ) 
            );                        
        }
    }
    public function addAction() {
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $builder = new AnnotationBuilder($objectManager);
        $form = $builder->createForm(new \Client\Entity\Client);
        $form->setHydrator(new DoctrineHydrator($objectManager,'Client\Entity\Client'));
        $client = new \Client\Entity\Client;
        $form->bind($client);
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            $uniqueStateId = $this->getRequest()->getPost('stateId');
            $nullClient = $objectManager->getRepository('Client\Entity\Client')->findOneBy(array("stateId" => $uniqueStateId));
            if ($form->isValid()) {
                if ($nullClient != null) {
                    $form->get("stateId")->setMessages(array("Repeated State Id"));
                } else {
                    $client->setTimeStamp();
                    $objectManager->persist($client);          
                    $objectManager->flush();
                    $clientId = $client->getClientId();                             
                    $this->redirect()->toRoute('clients/Client', 
                        array(
                            'action' => 'show',
                            'clientId' => $clientId, 
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
            $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
            $client = $objectManager->getRepository('Client\Entity\Client')->findOneBy(array("clientId" => $clientId));
            if ($client != null) {
                $objectManager->remove($client);
                $objectManager->flush();
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
