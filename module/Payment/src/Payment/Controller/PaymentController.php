<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Payment\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Request;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;

class PaymentController extends AbstractActionController
{
    public function showAllAction()
    {
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $allPayments = $objectManager->getRepository('Payment\Entity\Payment')->findAll();
        return new ViewModel( array('allPayments' => $allPayments) );
    }

    public function showAction() 
    {
        if ($this->params()->fromRoute('paymentId', 0) != "") {
            $paymentId = $this->params()->fromRoute('paymentId', 0);
            $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
            $payment = $objectManager->getRepository('Payment\Entity\Payment')->findOneBy(array("paymentId" => $paymentId));
            //Create form 
            //TODO This should be a form under form/ directory
            $builder = new AnnotationBuilder($objectManager);
            $form = $builder->createForm($payment);
            $form->setHydrator(new DoctrineHydrator($objectManager,'Payment\Entity\Payment'));
            $form->setBindOnValidate(false);
            $form->bind($payment);
            return new ViewModel( array(
                'payment' => $payment, 
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
            $paymentId = (int)$this->getRequest()->getPost('paymentId');            
            $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
            $payment = $objectManager->getRepository('Payment\Entity\Payment')->findOneBy(array("paymentId" => $paymentId));
            //Create form 
            //TODO This should be a form under form/ directory
            $builder = new AnnotationBuilder($objectManager);
            $form = $builder->createForm($payment);
            $form->setHydrator(new DoctrineHydrator($objectManager,'Payment\Entity\Payment'));
            $form->bind($payment);
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $objectManager->flush();
                $paymentId = $payment->getPaymentId();
            }
            return new ViewModel( array(
                'payment' => $payment, 
                'form' => $form,
                'paymentId' => $paymentId,
                ) 
            );                        
        }
    }
    public function addAction() {
        if($this->getRequest()->isPost()) {
             $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
            $builder = new AnnotationBuilder($objectManager);
            $form = $builder->createForm(new \Payment\Entity\Payment);
            $form->setHydrator(new DoctrineHydrator($objectManager,'Payment\Entity\Payment'));           
            $accountId = (int)$this->getRequest()->getPost('accountId');
            if ($accountId != null) {
                $account = $objectManager->getRepository('Account\Entity\Account')->findOneBy(array('accountId' => $accountId));
                if ($account != null) {
                    $payment = new \Payment\Entity\Payment;
                    $form->bind($payment);
                    if ($this->getRequest()->getPost('addPaymentSubmit') != null) {
                        //Validate and process
                        $form->setData($this->getRequest()->getPost());
                        if ($form->isValid()) {
                            $payment->setTimeStamp();
                            $payment->setAccount($account);
                            //$payment->setMyAccountId($accountId);
                            $objectManager->persist($payment);
                            $account->getPayments()->add($payment);
                            //$objectManager->persist($account);          
                            $objectManager->flush();
                            $paymentId = $payment->getPaymentId();                                                         
                            return $this->redirect()->toRoute('payments/Payment', 
                                array(
                                    'action'    => 'show',
                                    'paymentId'   => $paymentId
                                )
                            );
                        } 
                    } 
                    //First Time 
                    return new ViewModel( array('form' => $form, 'accountId' => $accountId));
                } else {
                    //No account Found with account ID
                    return $this->redirect()->toRoute('home');
                }
            } else {
                //No account ID given
                return $this->redirect()->toRoute('home');
            }   
        } else {
            //No Post at all
            return $this->redirect()->toRoute('home');
        }
        return $this->redirect()->toRoute('home');
    }
    public function deleteAction() {
        $paymentId = $this->getRequest()->getPost('paymentId');
        if ($this->getRequest()->getPost('sureDelete') == 'yes') {
            $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
            $payment = $objectManager->getRepository('Payment\Entity\Payment')->findOneBy(array("paymentId" => $paymentId));
            if ($payment != null) {
                $account = $payment->getAccount();
                $account->getPayments()->remove($payment);
                $objectManager->remove($payment);
                $objectManager->flush();
                $this->redirect()->toRoute('payments/Payment', 
                    array(
                        'action' => 'showAll',
                    )
                );
            } else {
                //No Payment found
                return $this->redirect()->toRoute('home');
            }
        } else if($this->getRequest()->getPost('sureDelete') == 'no') {
             return $this->redirect()->toRoute('payments/Payment', 
                array(
                    'action' => 'show',
                    'paymentId' => $paymentId, 
                )
            );
        } else {
            return new ViewModel( array('paymentId' => $paymentId));
        }
    }
}
