<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Account\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Request;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;

class AccountController extends AbstractActionController
{
    public function showAllAction()
    {
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $allAccounts = $objectManager->getRepository('Account\Entity\Account')->findAll();
        return new ViewModel( array('allAccounts' => $allAccounts) );
    }

    public function showAction() 
    {
        if ($this->params()->fromRoute('accountId', 0) != "") {
            $accountId = $this->params()->fromRoute('accountId', 0);
            $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
            $account = $objectManager->getRepository('Account\Entity\Account')->findOneBy(array("accountId" => $accountId));
            //Create form 
            //TODO This should be a form under form/ directory
            $builder = new AnnotationBuilder($objectManager);
            $form = $builder->createForm($account);
            $form->setHydrator(new DoctrineHydrator($objectManager,'Account\Entity\Account'));
            $form->setBindOnValidate(false);
            $form->bind($account);
            return new ViewModel( array(
                'account' => $account, 
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
            $accountId = (int)$this->getRequest()->getPost('accountId');            
            $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
            $account = $objectManager->getRepository('Account\Entity\Account')->findOneBy(array("accountId" => $accountId));
            //Create form 
            //TODO This should be a form under form/ directory
            $builder = new AnnotationBuilder($objectManager);
            $form = $builder->createForm($account);
            $form->setHydrator(new DoctrineHydrator($objectManager,'Account\Entity\Account'));
            $form->bind($account);
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $objectManager->flush();
                $accountId = $account->getAccountId();
            }
            return new ViewModel( array(
                'account' => $account, 
                'form' => $form,
                'accountId' => $accountId,
                ) 
            );                        
        }
    }
    public function addAction() {
        if($this->getRequest()->isPost()) {
             $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
            $builder = new AnnotationBuilder($objectManager);
            $form = $builder->createForm(new \Account\Entity\Account);
            $form->setHydrator(new DoctrineHydrator($objectManager,'Account\Entity\Account'));           
            $clientId = (int)$this->getRequest()->getPost('clientId');
            if ($clientId != null) {
                $client = $objectManager->getRepository('Client\Entity\Client')->findOneBy(array('clientId' => $clientId));
                if ($client != null) {
                    $account = new \Account\Entity\Account;
                    $form->bind($account);
                    if ($this->getRequest()->getPost('addAccountSubmit') != null) {
                        //Validate and process
                        $form->setData($this->getRequest()->getPost());
                        if ($form->isValid()) {
                            $account->setTimeStamp();
                            $account->setClient($client);
                            $objectManager->persist($account);
                            $client->getAccounts()->add($account);
                            //$objectManager->persist($client);          
                            $objectManager->flush();
                            $accountId = $account->getAccountId();                                                         
                            return $this->redirect()->toRoute('accounts/Account', 
                                array(
                                    'action'    => 'show',
                                    'accountId'   => $accountId
                                )
                            );
                        } 
                    } 
                    //First Time 
                    return new ViewModel( array('form' => $form, 'clientId' => $clientId));
                } else {
                    //No client Found with client ID
                    return $this->redirect()->toRoute('home');
                }
            } else {
                //No client ID given
                return $this->redirect()->toRoute('home');
            }   
        } else {
            //No Post at all
            return $this->redirect()->toRoute('home');
        }
        return $this->redirect()->toRoute('home');
    }
    public function deleteAction() {
        $accountId = $this->getRequest()->getPost('accountId');
        if ($this->getRequest()->getPost('sureDelete') == 'yes') {
            $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
            $account = $objectManager->getRepository('Account\Entity\Account')->findOneBy(array("accountId" => $accountId));
            if ($account != null) {
                $client = $account->getClient();
                $client->getAccounts()->remove($account);
                $objectManager->remove($account);
                $objectManager->flush();
                $this->redirect()->toRoute('accounts/Account', 
                    array(
                        'action' => 'showAll',
                    )
                );
            } else {
                //No Account found
                return $this->redirect()->toRoute('home');
            }
        } else if($this->getRequest()->getPost('sureDelete') == 'no') {
             return $this->redirect()->toRoute('accounts/Account', 
                array(
                    'action' => 'show',
                    'accountId' => $accountId, 
                )
            );
        } else {
            return new ViewModel( array('accountId' => $accountId));
        }
    }
}
