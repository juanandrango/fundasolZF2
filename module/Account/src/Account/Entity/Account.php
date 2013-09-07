<?php
namespace Account\Entity;
use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/** 
* Account Entity with Business Logic
*
* An Account provides a payment plan. It calculates the payment total depending on some interest
* and Late Payment Fees if applicable. It has 2 states: Open and Close. An Account is open 
* if payments are due. If all payments were done, the account is automatically "close". The account
* is always open until all payments have been submitted.
* 
* An Account will create Payment Objects and is the only interface to create and destroy objects 
* of type Payment from the database. 
* 
* The nPaid field keeps track of how many payments have been done so far. It should return the 
* number of payments with status ONTIME and/or LATE
* 
* Lastly, an account can only be destroyed if the status is closed. It will get destroyed via the Client
* interface only by the owner of the account. All Payments are destroyed with it. 
* 
* @ORM\Entity 
* @Annotation\Name("Account")
* @Annotation\Hydrator({"type":"Zend\Stdlib\Hydrator\ClassMethods", "options": {"underscoreSeparatedKeys": false}})
*/
class Account {
    /**
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    * @ORM\Column(type="integer")
    * @Annotation\Attributes({"type":"hidden"})
    * @Annotation\Options({"label":"Id:"})
    */
    protected $accountId;

    /**
    * @ORM\Column(type="date", nullable = false)
    * @Annotation\Attributes({"type":"datetime"})
    * @Annotation\Options({"label":"Payments Start:"})
    * @Annotation\Filter({"name": "StringTrim"})
    */
    protected $firstPayDate;
    
    /**
    * @ORM\Column(type="text", nullable = false)
    * @Annotation\Attributes({"type":"text"})
    * @Annotation\Options({"label":"Period:"})
    * @Annotation\Filter({"name": "StringTrim"})
    * @Annotation\Validator({"name":"NotEmpty"})
    * @Annotation\Validator({"name":"Regex", "options":{"pattern":"/^\weekly|daily$/"}})
    */
    protected $payPeriod;

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
     * @ORM\OneToMany(targetEntity="Payment\Entity\Payment", mappedBy="account")
     */
    protected $payments;
    
    /**
    * @ORM\Column(type="string", nullable = false)
    * @Annotation\Attributes({"type":"text"})
    * @Annotation\Options({"label":"number of Payments:"})
    * @Annotation\Filter({"name": "StringTrim"})
    * @Annotation\Validator({"name":"NotEmpty"})
    * @Annotation\Validator({"name":"Regex", "options":{"pattern":"/^[1-9][0-9]*$/"}})
    */
    protected $nPayments;
  
    /**
    * @ORM\ManyToOne(targetEntity="Client\Entity\Client", inversedBy="accounts")
    * @ORM\JoinColumn(name="myClientId", referencedColumnName="clientId")
     **/
    protected $client;
    
    /**
    * @ORM\Column(type="string", nullable = false)
    * @Annotation\Attributes({"type":"text"})
    * @Annotation\Options({"label":"Number Paid:"})
    * @Annotation\Filter({"name": "StringTrim"})
    * @Annotation\Validator({"name":"NotEmpty"})
    * @Annotation\Validator({"name":"Regex", "options":{"pattern":"/^\d+$/"}})
    */
    protected $nPaid;
   
    /**
    * @ORM\Column(type="string", nullable = false) 
    * @Annotation\Attributes({"type":"text"})
    * @Annotation\Options({"label":"Status:"})
    * @Annotation\Validator({"name":"Regex", "options":{"pattern":"/^\open|close$/"}})
    */
    protected $status;

    /** 
    * @ORM\Column(type="datetime") 
    * @Annotation\Attributes({"type":"hidden"})
    * @Annotation\Exclude()
    */
    protected $timeStamp;

    //Status
    const OPEN = 'open';
    const CLOSE = 'close';

    //Periods
    const WEEKLY = 'weekly';

    //BUSINESS LOGIC *************************************************************

    public function __construct() {
        $this->payments = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
    * It gets called in the addAction function from the controller. 
    *
    * This function sets the defaults for a new account. nPaid is handled internally. The
    * Default status is OPEN and payments are generated here.
    *
    * @param Service $objectManager The Entity Manager
    */
    public function initAdd($objectManager) {
        $this->nPaid = "0";
        $this->setTimeStamp();
        $this->setStatus(Account::OPEN);
        $this->generatePayments($objectManager);
    }

    /**
    *
    * This function removes a close account from its client's list and all its respective
    * payment objects
    *
    * @param Service $objectManager The Entity Manager
    * @return bool True if all payments were paid with Status ONTIME or LATE and account is CLOSE, else false
    */
    public function initDelete($objectManager) {
        if ($this->removePayments($objectManager) && $this->getStatus == Client::CLOSE) {
            $this->getClient()->getAccounts()->remove($this);
            return true;
        }
        return false;
    }

    /**
    * Creates payments objects.
    * 
    * It creates default payments for the newly created account. Should only be called once. 
    * Checks if objects already exists and adds new ones if none are found. Defaults for payments
    * is Status due. 
    * 
    * @param Service $objectManager The Entity Manager
    */
    private function generatePayments($objectManager) {
        for ($i = 0 ; $i < $this->nPayments ; $i++) {
            $payment = new \Payment\Entity\Payment;
            $payment->setAmount(number_format($this->amount/$this->nPayments, 2, '.', '') + "");
            $payment->setPaymentNumber($i + 1);
            $payment->setAccount($this);
            $payment->setStatus(\Payment\Entity\Payment::DUE);
            $payment->setTimeStamp();
            $payment->setDueDate($this->firstPayDate, Account::WEEKLY);
            $objectManager->persist($payment);
            $this->getPayments()->add($payment);
        }
    }

    /**
    * Removes payments objects.
    * 
    * It removes payments objects from account. Double checks all payments were paid
    * 
    * @param Service $objectManager The Entity Manager
    * @return bool True if all payments are ONTIME or LATE, False otherwise
    */    
    private function removePayments($objectManager) {
        foreach($this->getPayments as $payment) {
            if ($payment->getStatus() != \Payment\Entity\Payment::ONTIME
                && $payment->getStatus() != \Payment\Entity\Payment::LATE) {
                return false;
            }
        }

        foreach($this->getPayments as $payment) {
            $objectManager->remove($payment);    
        }

        return true;
    }

    /**
    * @return Payment|null It returns a Payment object with a due status, depending on its 
    * nPayment number. If no more due payments are found, return null
    */
    public function getNextDuePayment() {
        for ($i = 0 ; $i < $this->nPayments ; $i++) {
            $payment = $this->payments[$i];
            if ($payment->getStatus() == \Payment\Entity\Payment::DUE) {
                return $payment;
            }
        }   
        return null;
    }

    /**
    * Performs a Legal payment against the account. Marks the account as close if 
    * no more payments are due. If account is close, it doesn't do anything
    *
    * @param Service $objectManager The entity Manager
    * @param Payment $payment The next due payment from Account
    */
    public function processNextDuePayment($objectManager, $payment) {
        if ($this->getStatus() != Account::CLOSE) {
            $this->setNPaid($payment->getPaymentNumber());
            if ($this->getNPaid() == $this->getNPayments()) {
                $this->setStatus(Account::CLOSE);
            }
            $payment->processPay();
            $objectManager->persist($payment);
            $objectManager->persist($this);    
        }
    }

    public function getFirstPayDateStr() {
        return $this->firstPayDate->format('Y-m-d');
    }

    /**
    * @return string An HTML formatted string to show reporting details about the account
    */
    public function getHTMLReport() {
        $HTMLReport = "<dl class='dl-horizontal' >" . PHP_EOL;
        $HTMLReport .= "<dt> Completed </dt> <dd>" . (int)(($this->getNPaid()/$this->getNPayments()) * 100) . "% </dd>" . PHP_EOL;
        $latePayments = 0;
        foreach($this->getPayments() as $payment) {
            if ($payment->getStatus() == \Payment\Entity\Payment::LATE) {
                $latePayments ++;
            }
        }
        $HTMLReport .= "<dt> Late Payments </dt> <dd>" . $latePayments . "/" . $this->getNPaid() . "</dd>" . PHP_EOL;
        $HTMLReport .= "</dl>";
        return $HTMLReport;
    }
    
    //END BUSINESS LOGIC ************************************************************

    //Getters
    public function getAccountId() {
        return $this->accountId;
    }
    public function getFirstPayDate() {
        if ($this->firstPayDate == null) {
            return $this->firstPayDate;
        }
        return $this->firstPayDate->format('Y-m-d');
    }
    public function getPayPeriod() {
        return $this->payPeriod;
    }
    public function getAmount() {
        return $this->amount;
    }
    public function getPayments() {
        return $this->payments;
    }
    public function getNPayments() {
        return $this->nPayments;
    }
    public function getClient() {
        return $this->client;
    }
    public function getNPaid() {
        return $this->nPaid;
    }
    public function getStatus() {
        return $this->status;
    }
    public function getTimeStamp() {
        return $this->timeStamp;
    }
    //Setters
    public function setFirstPayDate($fpd) {
        $this->firstPayDate = $fpd;
    }
    public function setPayPeriod($pp) {
        $this->payPeriod = $pp;
    }
    public function setAmount($newAmount) {
        $this->amount = $newAmount;
    }
    public function setPayments($p) {
        $this->payments = $p;
    }
    public function setNPayments($np) {
        $this->nPayments = $np;
    }
    public function setClient($c) {
        $this->client = $c;
    }
    public function setNPaid($np) {
        $this->nPaid = $np;
    }
   	public function setStatus($s) {
   		$this->status = $s;
   	}
   	public function setTimeStamp() {
        $this->timeStamp = new \DateTime("now");
   	}
}