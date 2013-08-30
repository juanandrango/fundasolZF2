<?php
namespace Account\Entity;
use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/** 
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
    * @ORM\Column(type="integer", nullable = false)
    * @Annotation\Attributes({"type":"text"})
    * @Annotation\Options({"label":"Start Week:"})
    */
    protected $startWeek;
    
    /**
    * @ORM\Column(type="integer", nullable = false)
    * @Annotation\Attributes({"type":"text"})
    * @Annotation\Options({"label":"Amount:"})
    */
    protected $amount;
    
    /**
    * @ORM\Column(type="integer", nullable = false)
    * @Annotation\Attributes({"type":"text"})
    * @Annotation\Options({"label":"number of Payments:"})
    */
    protected $nPayments;
  
     /**
     * @ORM\ManyToOne(targetEntity="Client\Entity\Client", inversedBy="accounts")
     * @ORM\JoinColumn(name="myClientId", referencedColumnName="clientId")
     **/
    protected $client;
    
    /**
    * @ORM\Column(type="integer", nullable = false)
    * @Annotation\Attributes({"type":"text"})
    * @Annotation\Options({"label":"Number Paid:"})
    */
    protected $nPaid;
   
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
    public function getAccountId() {
        return $this->accountId;
    }
   public function getStartWeek() {
        return $this->startWeek;
    }
    public function getAmount() {
        return $this->amount;
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
    public function setStartWeek($sw) {
        $this->startWeek = $sw;
    }
    public function setAmount($newAmount) {
        $this->amount = $newAmount;
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