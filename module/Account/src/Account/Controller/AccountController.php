<?php

namespace Account\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Request;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;

class AccountController extends AbstractActionController {
    
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
            'allAccounts' => $this->getRepository('Account')->findAll() 
            )
        );
    }

    public function showRequestsAction() {
        $this->updateCounts();
        $this->objectManager = $this->getObjectManager();
        return new ViewModel( array(
            'allAccounts' => $this->getRepository('Account')->findAll() 
            )
        );
    }

    public function showAction() {
        $this->updateCounts();
        if ($this->params()->fromRoute('accountId', 0) != "") {
            $this->objectManager = $this->getObjectManager();
            $accountId = $this->params()->fromRoute('accountId', 0);
            $account = $this->getRepository('Account')->findOneBy(array("accountId" => $accountId));
            return new ViewModel( array(
                'account' => $account
                ) 
            );
        }
        $this->redirect()->toRoute('home'); 
    }

    public function approveAction() {
        $this->updateCounts();
        if ($this->getRequest()->isPost()) {
            $accountId = (int)$this->getRequest()->getPost('accountId');
            if ($accountId != null) {
                $this->objectManager = $this->getObjectManager();            
                $account = $this->getRepository('Account')->findOneBy(array('accountId' => $accountId));
                if ($account != null) {
                    $account->approve($this->objectManager);
                    $this->objectManager->flush();
                    return $this->redirect()->toRoute('accounts/Account', 
                        array(
                            'action'    => 'show',
                            'accountId' => $account->getAccountId()
                        )
                    ); 
                }
            }            
        }
        return $this->redirect()->toRoute('home');
    }

    public function denyAction() {
        $this->updateCounts();
        if ($this->getRequest()->isPost()) {
            $accountId = (int)$this->getRequest()->getPost('accountId');
            if ($accountId != null) {
                $this->objectManager = $this->getObjectManager();            
                $account = $this->getRepository('Account')->findOneBy(array('accountId' => $accountId));
                if ($account != null) {
                    $account->deny($this->objectManager);
                    $this->objectManager->flush();
                    return $this->redirect()->toRoute('accounts/Account', 
                        array(
                            'action'    => 'show',
                            'accountId' => $account->getAccountId()
                        )
                    ); 
                }
            }            
        }
        return $this->redirect()->toRoute('home');
    }

    public function requestAction() {
        $this->updateCounts();    
        if($this->getRequest()->isPost()) {
            $clientId = (int)$this->getRequest()->getPost('clientId');
            if ($clientId != null) {
                $this->objectManager = $this->getObjectManager();                        
                $client = $this->getRepository('Client')->findOneBy(array('clientId' => $clientId));
                if ($client != null) {
                    $account = new \Account\Entity\Account;
                    $builder = new AnnotationBuilder($this->objectManager);
                    $form = $builder->createForm(new \Account\Entity\Account);
                    $form->setHydrator(new DoctrineHydrator($this->objectManager,'Account\Entity\Account'));                       
                    $form->bind($account);
                    if ($this->getRequest()->getPost('requestAccountSubmit') != null) {
                        $form->setData($this->getRequest()->getPost());
                        $requestDate = $this->getRequest()->getPost('requestDate`');
                        $firstPayDate = $this->getRequest()->getPost('firstPayDate');
                        if ($form->isValid() 
                            && \Account\Entity\Account::areDatesValid($requestDate, $firstPayDate, $form)) {                            
                            $account->setClient($client);
                            $account->initRequest();                            
                            $this->objectManager->persist($account);
                            $client->getAccounts()->add($account);
                            $this->objectManager->flush();                                              
                            return $this->redirect()->toRoute('accounts/Account', 
                                array(
                                    'action'    => 'show',
                                    'accountId' => $account->getAccountId()
                                )
                            );                            
                        } 
                    } 
                    return new ViewModel( 
                        array(
                            'form' => $form, 
                            'clientId' => $clientId
                        )
                    );
                }                 
            }            
        }
        return $this->redirect()->toRoute('home');
    }
    
    public function payAction() {
        $this->updateCounts();
        if($this->getRequest()->isPost()) {
            $accountId = (int)$this->getRequest()->getPost('accountId');
            if ($accountId != null) {
                $this->objectManager = $this->getObjectManager();        
                $account = $this->getRepository('Account')->findOneBy(array('accountId' => $accountId));
                if ($account != null) {
                    $payment = $account->getNextDuePayment();
                    if ($payment != null) {
                        $amount = $payment->getAmount();
                        if ($this->getRequest()->getPost('paySubmit') != null) {
                            //Validate and process
                            $account->processNextDuePayment($this->objectManager, $payment);
                            $this->objectManager->flush();
                            return $this->redirect()->toRoute('accounts/Account', 
                                array(
                                    'action'    => 'show',
                                    'accountId' => $accountId,
                                    'amount'    => $amount
                                    )
                                );
                        } else {
                            //First Time 
                            return new ViewModel( array(
                                'accountId' => $accountId,
                                'amount'    => $amount
                                )
                            );
                        }
                    }                    
                } 
            }   
        } 
        return $this->redirect()->toRoute('accounts/Account', 
            array(
                'action'    => 'showAll'
            )
        );
    }
}
