<?php
namespace Contribution\Entity;
use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/** 
* Contribution Entity with Business Logic
*
* An Contribution provides a payment plan. It calculates the payment total depending on some interest
* and Late Payment Fees if applicable. It has 2 states: Open and Close. An Contribution is open 
* if payments are due. If all payments were done, the contribution is automatically "close". The contribution
* is always open until all payments have been submitted.
* 
* An Contribution will create Payment Objects and is the only interface to create and destroy objects 
* of type Payment from the database. 
* 
* The nPaid field keeps track of how many payments have been done so far. It should return the 
* number of payments with status ONTIME and/or LATE
* 
* Lastly, an contribution can only be destroyed if the status is closed. It will get destroyed via the Investor
* interface only by the owner of the contribution. All Payments are destroyed with it. 
* 
* @ORM\Entity 
* @Annotation\Name("Contribution")
* @Annotation\Hydrator({"type":"Zend\Stdlib\Hydrator\ClassMethods", "options": {"underscoreSeparatedKeys": false}})
*/
class Contribution {
    /**
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    * @ORM\Column(type="integer")
    * @Annotation\Attributes({"type":"hidden"})
    * @Annotation\Options({"label":"Id:"})
    */
    protected $contributionId;

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
    * @ORM\ManyToOne(targetEntity="Investor\Entity\Investor", inversedBy="contributions")
    * @ORM\JoinColumn(name="myInvestorId", referencedColumnName="investorId")
     **/
    protected $investor;
   
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

    //BUSINESS LOGIC *************************************************************

    //Status
    const OPEN = 'open';
    const CLOSE = 'close';

    //Statics
    public static $count;

    /**
    * It gets called in the addAction function from the controller. 
    *
    * This function sets the defaults for a new contribution. The
    * Default status is OPEN and payments are generated here.
    *
    * @param Service $objectManager The Entity Manager
    */
    public function initAdd($objectManager) {
        $this->setTimeStamp();
        $this->setStatus(Contribution::OPEN);
    }

    /**
    * @return string An HTML formatted string to show reporting details about the contribution
    */
    public function getHTMLReport() {
        return "";
    }
    
    //END BUSINESS LOGIC ************************************************************

    //Getters
    public function getContributionId() {
        return $this->contributionId;
    }
    public function getAmount() {
        return $this->amount;
    }
    public function getInvestor() {
        return $this->investor;
    }
    public function getStatus() {
        return $this->status;
    }
    public function getTimeStamp() {
        return $this->timeStamp;
    }
    //Setters
    public function setAmount($newAmount) {
        $this->amount = $newAmount;
    }
    public function setInvestor($c) {
        $this->investor = $c;
    }
    public function setStatus($s) {
   		$this->status = $s;
   	}
   	public function setTimeStamp() {
        $this->timeStamp = new \DateTime("now");
   	}
}