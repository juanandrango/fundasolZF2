<?php
namespace Payment\Entity;
use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/** 
* @ORM\Entity 
* @Annotation\Name("Payment")
* @Annotation\Hydrator({"type":"Zend\Stdlib\Hydrator\ClassMethods", "options": {"underscoreSeparatedKeys": false}})
*/
class Payment {
    /**
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    * @ORM\Column(type="integer")
    * @Annotation\Attributes({"type":"hidden"})
    * @Annotation\Options({"label":"Id:"})
    */
    protected $paymentId;

    // *
    // * @ORM\Column(type="integer", nullable = false)
    // * @Annotation\Attributes({"type":"hidden"})
    // * @Annotation\Options({"label":"For Account:"})
    
    // protected $myAccountId;

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
    * @Annotation\Validator({"name":"Regex", "options":{"pattern":"/^\d+$/"}})
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

    //Getters
    public function getPaymentId() {
        return $this->paymentId;
    }
    // public function getMyAccountId() {
    //     return $this->myAccountId;
    // }
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
    //Setters
    // public function setMyAccountId($mAID) {
    //     $this->myAccountId = $mAID;
    // }
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
   	public function setTimeStamp() {
        $this->timeStamp = new \DateTime("now");
   	}
}