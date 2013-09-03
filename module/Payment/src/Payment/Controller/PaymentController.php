<?php
/**
 *
 */

namespace Payment\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Request;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;

class PaymentController extends AbstractActionController {

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
    private function getPaymentRepository() {
        return $this->objectManager->getRepository('Payment\Entity\Payment');
    }
    private function getAccountRepository() {
        return $this->objectManager->getRepository('Account\Entity\Account');
    }

    // public function payAction() {
    //     $this->objectManager = $this->getObjectManager();
    //     if($this->getRequest()->isPost()) {
    //         $accountId = (int)$this->getRequest()->getPost('accountId');
    //         if ($accountId != null) {
    //             $account = $this->getAccountRepository()->findOneBy(array('accountId' => $accountId));
    //             if ($account != null) {
    //                 $payment = $account->getNextDuePayment();
    //                 if ($payment != null) {
    //                     $amount = $payment->getAmount();
    //                     if ($this->getRequest()->getPost('paySubmit') != null) {
    //                         //Validate and process
    //                         $payment->processPay();
    //                         $this->objectManager->persist($payment);
    //                         $this->objectManager->flush();
    //                         return $this->redirect()->toRoute('accounts/Account', 
    //                             array(
    //                                 'action'        => 'show',
    //                                 'accountId'     => $accountId,
    //                                 'amount'        => $amount
    //                                 )
    //                             );
    //                     } else {
    //                         //First Time 
    //                         return new ViewModel( array(
    //                             'form'      => $form, 
    //                             'accountId' => $accountId,
    //                             'amount'    => $amount
    //                             )
    //                         );
    //                     }
    //                 } 
    //             } 
    //         }   
    //     } 
    //     return $this->redirect()->toRoute('accounts/Account', 
    //         array(
    //             'action'    => 'showAll'
    //         )
    //     );
    // }
}

    // public function showAllAction() {
    //     $this->objectManager = $this->getObjectManager();
    //     return new ViewModel( array(
    //         'allPayments' => $this->getPaymentRepository()->findAll()
    //         ) 
    //     );
    // }

    // public function showAction() {
    //     $this->objectManager = $this->getObjectManager();
    //     if ($this->params()->fromRoute('paymentId', 0) != "") {
    //         $paymentId = $this->params()->fromRoute('paymentId', 0);
    //         $payment = $this->getPaymentRepository()->findOneBy(array("paymentId" => $paymentId));
    //         $builder = new AnnotationBuilder($this->objectManager);
    //         $form = $builder->createForm($payment);
    //         $form->setHydrator(new DoctrineHydrator($this->objectManager,'Payment\Entity\Payment'));
    //         $form->setBindOnValidate(false);
    //         $form->bind($payment);
    //         return new ViewModel( array(
    //             'payment' => $payment, 
    //             'form' => $form
    //             ) 
    //         );
    //     }
    //     return $this->redirect()->toRoute('home');
    // }

    // public function editAction() {
    //     $this->objectManager = $this->getObjectManager();
    //     if ($this->getRequest()->isPost()) {            
    //         $paymentId = (int)$this->getRequest()->getPost('paymentId');            
    //         $payment = $this->getPaymentRepository()->findOneBy(array("paymentId" => $paymentId));
    //         $builder = new AnnotationBuilder($this->objectManager);
    //         $form = $builder->createForm($payment);
    //         $form->setHydrator(new DoctrineHydrator($this->objectManager,'Payment\Entity\Payment'));
    //         $form->bind($payment);
    //         $form->setData($this->getRequest()->getPost());
    //         if ($form->isValid()) {
    //             $this->objectManager->flush();
    //             $paymentId = $payment->getPaymentId();
    //         }
    //         return new ViewModel( array(
    //             'payment'   => $payment, 
    //             'form'      => $form,
    //             'paymentId' => $paymentId,
    //             ) 
    //         );                        
    //     }
    //     return $this->redirect()->toRoute('home');
    // }
    // public function deleteAction() {
    //     $this->objectManager = $this->getObjectManager();
    //     if ($this->getRequest()->isPost()) {
    //         $paymentId = $this->getRequest()->getPost('paymentId');
    //         if ($this->getRequest()->getPost('sureDelete') == 'yes') {
    //             $payment = $this->getPaymentRepository()->findOneBy(array("paymentId" => $paymentId));
    //             if ($payment != null) {
    //                 $account = $payment->getAccount();
    //                 $account->getPayments()->remove($payment);
    //                 $this->objectManager->remove($payment);
    //                 $this->objectManager->flush();
    //                 $this->redirect()->toRoute('payments/Payment', 
    //                     array(
    //                         'action' => 'showAll',
    //                         )
    //                     );
    //             } 
    //         } else if($this->getRequest()->getPost('sureDelete') == 'no') {
    //             return $this->redirect()->toRoute('payments/Payment', 
    //                 array(
    //                     'action' => 'show',
    //                     'paymentId' => $paymentId, 
    //                     )
    //                 );
    //         } 
    //         return new ViewModel( array('paymentId' => $paymentId));
    //     }
    //     return $this->redirect()->toRoute('home');
    // }