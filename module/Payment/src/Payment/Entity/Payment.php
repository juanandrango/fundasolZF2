<?php

namespace Payment\Entity;
use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/** 
* Payment Entity with business logic
* 
* This file represents the entity Payment for the Payment module. 
* An Entity Payment is generated only when an Account is created. The account defines how many payment
* objects will be created, and generates the Payment entries with an Amount, paymentNumber, dueDate and 
* status with Default DUE, and timestamp. ID is automatically generated for each. Each payment will 
* record the ID of the account that created it. 
*
* CONSTRAINS:
* No payment can be created with a timestamp prior to the current DateTime.  
* A payment changes to status ONTIME if payment was done up to date on dueDate, else, it is classified as late.
* paymentNumber goes from 1 to Account->nPayments.
*
* @ORM\Entity 
* @Annotation\Name("Payment")
* @Annotation\Hydrator({"type":"Zend\Stdlib\Hydrator\ClassMethods", "options": {"underscoreSeparatedKeys": false}})
*/
class Payment {
    // ============================== Database Columns ============================== //
    /**
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    * @ORM\Column(type="integer")
    * @Annotation\Attributes({"type":"hidden"})
    * @Annotation\Options({"label":"Id:"})
    */
    protected $paymentId;

    /**
    * @ORM\ManyToOne(targetEntity="Account\Entity\Account", inversedBy="payments")
    * @ORM\JoinColumn(name="myAccountId", referencedColumnName="accountId")
    */
    protected $account;

    /**
    * @ORM\Column(type="string", nullable = false)
    * @Annotation\Attributes({"type":"text"})
    * @Annotation\Options({"label":"Payment Number:"})
    * @Annotation\Filter({"name": "StringTrim"})
    * @Annotation\Validator({"name":"NotEmpty"})
    * @Annotation\Validator({"name":"Regex", "options":{"pattern":"/^[1-9][0-9]*$/"}})
    */
    protected $paymentNumber;
    
    /**
    * @ORM\Column(type="string", nullable = false)
    * @Annotation\Attributes({"type":"text"})
    * @Annotation\Options({"label":"Amount:"})
    * @Annotation\Filter({"name": "StringTrim"})
    * @Annotation\Validator({"name":"NotEmpty"})
    * @Annotation\Validator({"name":"Regex", "options":{"pattern":"/^\d+$/"}})
    */
    protected $amount;
   
    /**
    * @ORM\Column(type="string", nullable = true) 
    * @Annotation\Attributes({"type":"text"})
    * @Annotation\Options({"label":"Status:"})
    */
    protected $status;

    /** 
    * @ORM\Column(type="datetime") 
    * @Annotation\Attributes({"type":"hidden"})
    * @Annotation\Exclude()
    */
    protected $timeStamp;

    /** 
    * @ORM\Column(type="date") 
    * @Annotation\Attributes({"type":"hidden"})
    * @Annotation\Exclude()
    */
    protected $dueDate;

    /** 
    * @ORM\Column(type="datetime", nullable = true) 
    * @Annotation\Attributes({"type":"hidden"})
    * @Annotation\Exclude()
    */
    protected $paidTimeStamp;

    // ------------------------------ Getters ------------------------------ //
    public function getPaymentId() {
        return $this->paymentId;
    }
    public function getAccount() {
        return $this->account;
    }
    public function getPaymentNumber() {
        return $this->paymentNumber;
    }
    public function getAmount() {
        return $this->amount;
    }
    public function getStatus() {
        return $this->status;
    }
    public function getTimeStamp() {
        return $this->timeStamp;
    }
    public function getDueDate() {
        return $this->dueDate;
    }
    public function getPaidTimeStamp() {
        return $this->paidTimeStamp;
    }
    
    //------------------------------ Setters ------------------------------ //
    public function setAccount($a) {
        $this->account = $a;
    }
    public function setPaymentNumber($pn) {
        $this->paymentNumber = $pn;
    }
    public function setAmount($a) {
        $this->amount = $a;
    }
    public function setStatus($s) {
        $this->status = $s;
    }
    /**
    * Sets the creation timestamp of the Entity to current date/time.
    */
    public function setTimeStamp() {
        $this->timeStamp = new \DateTime("now");
    }
    /**
    * Sets the paidTimeStamp to the current date/time. 
    */
    public function setPaidTimeStamp() {
        $this->paidTimeStamp = new \DateTime("now");
    }

    // ============================== Business Logic ============================== //

    // ------------------------------ Constants ------------------------------ //
    //Payment Status
    const DUE = "due";
    const ONTIME = 'On Time';
    const LATE = 'Late';
    const PARTIAL = 'Partial';

    // ------------------------------ Methods ------------------------------ //
    
    /**
    * processPay records a payment. It sets the status to ONTIME if payment is made on the due
    * date of before, status is LATE otherwise
    */
    public function processPay() {
        $this->setPaidTimeStamp();
        if (\strtotime($this->getDueDateStr()) >= \strtotime(\date('Y-m-d'))) {
            $this->setStatus(Payment::ONTIME);    
        } else {
            $this->setStatus(Payment::LATE);
        }
    }

    /**
    * Sets the dueDate property taking a date as the start date and offsetting it
    * by a period of time times the nPayment attribute
    * @param string @date The start date for the offset
    * @param string @aPeriod The period is defined in Account. So far only WEEKLY
    */
    public function setDueDate($date, $aPeriod) {
        $aDate = new \DateTime($date);
        if ($aPeriod == \Account\Entity\Account::WEEKLY) {
            $dateInterval = 7 * ((int)$this->getPaymentNumber() - 1) . ' days';
            date_add($aDate, date_interval_create_from_date_string($dateInterval));    
            $this->dueDate = $aDate;
        }
    }

    /**
    * @param string|null $format If format is provided use it, otherwise, use default
    * @return string Empty string if paidTimeStamp is null, else, a string formatted datetime
    */
    public function getPaidTimeStampStr($format) {
        if ($this->paidTimeStamp == null) {
            return "";
        } else {
            if ($format == null) {
                return $this->paidTimeStamp->format('Y-m-d h:i:s');    
            } else {
                return $this->paidTimeStamp->format($format);
            }            
        }
    }

    /**
    * @param string|null $format If format is provided use it, otherwise, use default
    * @return string dueDate formatted to Y-m-d or given format
    */
    public function getDueDateStr($format) {
        if ($format == null) {            
            return $this->dueDate->format('Y-m-d');
        }
        return $this->dueDate->format($format);
    }
}