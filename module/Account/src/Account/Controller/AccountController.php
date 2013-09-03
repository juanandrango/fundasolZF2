<?php
/**
 *
 */

namespace Account\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Request;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;

class AccountController extends AbstractActionController {
    
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
    private function getAccountRepository() {
        return $this->objectManager->getRepository('Account\Entity\Account');
    }

    public function showAllAction() {
        $this->objectManager = $this->getObjectManager();
        return new ViewModel( array(
            'allAccounts' => $this->getAccountRepository()->findAll() 
            )
        );
    }

    public function showAction() {
        if ($this->params()->fromRoute('accountId', 0) != "") {
            $this->objectManager = $this->getObjectManager();
            $accountId = $this->params()->fromRoute('accountId', 0);
            $account = $this->getAccountRepository()->findOneBy(array("accountId" => $accountId));    
            $builder = new AnnotationBuilder($this->objectManager);
            $form = $builder->createForm($account);
            $form->setHydrator(new DoctrineHydrator($this->objectManager,'Account\Entity\Account'));
            $form->setBindOnValidate(false);
            $form->bind($account);
            return new ViewModel( array(
                'account' => $account, 
                'form' => $form
                ) 
            );
        }
        $this->redirect()->toRoute('home'); 
    }

    // public function editAction() {
    //     if ($this->getRequest()->isPost()) {
    //         $this->objectManager = $this->getObjectManager();            
    //         $accountId = (int)$this->getRequest()->getPost('accountId');            
    //         if ($accountId != null) {
    //             $account = $this->getAccountRepository()->findOneBy(array("accountId" => $accountId));
    //             $builder = new AnnotationBuilder($this->objectManager);
    //             $form = $builder->createForm($account);
    //             $form->setHydrator(new DoctrineHydrator($this->objectManager,'Account\Entity\Account'));
    //             $form->bind($account);
    //             $form->setData($this->getRequest()->getPost());
    //             if ($form->isValid()) {
    //                 $this->objectManager->flush();
    //             }
    //             return new ViewModel( array(
    //                 'account'   => $account, 
    //                 'form'      => $form,
    //                 'accountId' => $accountId,
    //                 ) 
    //             );    
    //         }                  
    //     }
    //     $this->redirect()->toRoute('home');
    // }

    public function addAction() {
        if($this->getRequest()->isPost()) {
            $this->objectManager = $this->getObjectManager();            
            $builder = new AnnotationBuilder($this->objectManager);
            $form = $builder->createForm(new \Account\Entity\Account);
            $form->setHydrator(new DoctrineHydrator($this->objectManager,'Account\Entity\Account'));           
            $clientId = (int)$this->getRequest()->getPost('clientId');
            if ($clientId != null) {
                $client = $this->getClientRepository()->findOneBy(array('clientId' => $clientId));
                if ($client != null) {
                    $account = new \Account\Entity\Account;
                    $form->bind($account);
                    if ($this->getRequest()->getPost('addAccountSubmit') != null) {
                        $form->setData($this->getRequest()->getPost());                                                
                        if ($form->isValid()) {
                            if (\strtotime($account->getFirstPayDateStr()) < \strtotime(\date("Y-m-d"))) {
                                $form->get("firstPayDate")->setMessages(array("Invalid Date"));
                            } else {
                                $account->setClient($client);
                                $account->initAdd($this->objectManager);                            
                                $this->objectManager->persist($account);
                                $client->getAccounts()->add($account);
                                $this->objectManager->flush();                                              
                                return $this->redirect()->toRoute('accounts/Account', 
                                    array(
                                        'action'    => 'show',
                                        'accountId'   => $account->getAccountId()
                                    )
                                );
                            }
                        } 
                    } 
                    //First Time 
                    return new ViewModel( array('form' => $form, 'clientId' => $clientId));
                } 
                //No client Found with client ID
            }
            //No client ID given
        }
        //No Post at all
        return $this->redirect()->toRoute('home');
    }
    
    public function deleteAction() {
        if ($this->getRequest()->isPost()) {
            $accountId = $this->getRequest()->getPost('accountId');    
            if ($this->getRequest()->getPost('sureDelete') == 'yes') {
                $this->objectManager = $this->getObjectManager();
                $account = $this->getAccountRepository()->findOneBy(array("accountId" => $accountId));
                if ($account != null) {
                    if ($account->initDelete($this->objectManager)) {
                        $this->objectManager->remove($account);
                        $this->objectManager->flush();
                        $this->redirect()->toRoute('accounts/Account', 
                            array(
                                'action' => 'showAll',
                            )
                        );    
                    } else {
                        //Something went wrong!. Account is not close or payments were not all Paid
                        //TODO Handle this situation 
                        return $this->redirect()->toRoute('home');
                    }                    
                } 
                //No Account found
            } else if ($this->getRequest()->getPost('sureDelete') == 'no') {
                 return $this->redirect()->toRoute('accounts/Account', 
                    array(
                        'action' => 'show',
                        'accountId' => $accountId, 
                    )
                );
            } 
        }
        return $this->redirect()->toRoute('home');
    }
    public function payAction() {
        $this->objectManager = $this->getObjectManager();
        if($this->getRequest()->isPost()) {
            $accountId = (int)$this->getRequest()->getPost('accountId');
            if ($accountId != null) {
                $account = $this->getAccountRepository()->findOneBy(array('accountId' => $accountId));
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
                                    'action'        => 'show',
                                    'accountId'     => $accountId,
                                    'amount'        => $amount
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
