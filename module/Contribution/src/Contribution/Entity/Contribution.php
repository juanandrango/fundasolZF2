<?php
namespace Contribution\Entity;
use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/** 
* Contribution Entity with Business Logic
* 
* A contribution is some money and investor decides to consign to Fundasol. A contribution earns 
* the investor some interest just like a Bank would. Once the investor retires the money corresponding 
* to the contribution, the latter is considered closed, otherwise it is open. 
* 
* @ORM\Entity 
* @Annotation\Name("Contribution")
* @Annotation\Hydrator({"type":"Zend\Stdlib\Hydrator\ClassMethods", "options": {"underscoreSeparatedKeys": false}})
*/
class Contribution {

    // ============================== DataBase Columns ==============================//

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

    // ------------------------------ Getters ------------------------------ //
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
    
    // ------------------------------ Setters ------------------------------ //
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


    // ============================== Business Logic ============================== //

    // ------------------------------ Constants ------------------------------ //

    //Status
    const OPEN = 'open';
    const CLOSE = 'close';

    // ------------------------------ Static Properties ------------------------------ //
    private static $count;

    // ------------------------------ Static Methods ------------------------------ //
    public static function getCount() {
        return Contribution::$count;
    }
    public static function updateCount($objectManager) {
        Contribution::$count = count($objectManager->getRepository('Contribution\Entity\Contribution')->findAll());
    }

    // ------------------------------ Public Methods ------------------------------ //
    /**
    * It is called in the addAction function from the controller. 
    *
    * This function sets the defaults for a new contribution. The
    * Default status is OPEN.
    *
    * @param Service $objectManager The Entity Manager
    */
    public function initAdd($objectManager) {
        $this->setTimeStamp();
        $this->setStatus(Contribution::OPEN);
    }
}