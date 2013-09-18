<?php
namespace Investor\Entity;
use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/** 
* Investor Entity with Business Logic
*
* An investor is a person that commits contributions to Fundasol. An investor can have 1 or more contributions.
* Investors earn interest on their contributions
*
* @ORM\Entity 
* @Annotation\Name("Investor")
* @Annotation\Hydrator({"type":"Zend\Stdlib\Hydrator\ClassMethods", "options": {"underscoreSeparatedKeys": false}})
*/
class Investor {
    // ============================== Database Columns ============================== //
    /**
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    * @ORM\Column(type="integer")
    * @Annotation\Attributes({"type":"hidden"})
    * @Annotation\Options({"label":"Id:"})
    */
    protected $investorId;

    /**
     * @ORM\OneToMany(targetEntity="Contribution\Entity\Contribution", mappedBy="investor")
     */
    protected $contributions;

    /** 
    * @ORM\Column(type="string")
    * @Annotation\Attributes({"type":"text"})
    * @Annotation\Options({"label":"First Name:"})
	*/
    protected $firstName;

    /** 
    * @ORM\Column(type="string") 
    * @Annotation\Attributes({"type":"text"})
    * @Annotation\Options({"label":"Last Name:"})
    */
    protected $lastName;

    /** 
    * @ORM\Column(type="string", unique=true, length=10) 
    * @Annotation\Attributes({"type":"text"})
    * @Annotation\Options({"label":"State Id:"})
    * @Annotation\Validator({"name":"Regex", "options":{"pattern":"/^[0-9]{10}$/"}})
    */
    protected $stateId;

    /** 
    * @ORM\Column(type="string", nullable=true) 
    * @Annotation\Attributes({"type":"text"})
    * @Annotation\Options({"label":"Status:"})
    */
    protected $status;

    /** 
    * @ORM\Column(type="string", nullable=true, length=10) 
    * @Annotation\Attributes({"type":"text"})
    * @Annotation\Options({"label":"House Phone #:"})
    * @Annotation\Validator({"name":"Regex", "options":{"pattern":"/^[0-9]{10}$/"}})
    */
    protected $phoneHome;

    /** 
    * @ORM\Column(type="string", nullable=true, length=10) 
    * @Annotation\Attributes({"type":"text"})
    * @Annotation\Options({"label":"Work Phone #:"})
    * @Annotation\Validator({"name":"Regex", "options":{"pattern":"/^[0-9]{10}$/"}})
    */
    protected $phoneWork;

    /** 
    * @ORM\Column(type="string", nullable=true, length=10) 
    * @Annotation\Attributes({"type":"text"})
    * @Annotation\Options({"label":"Reference Phone #:"})
    * @Annotation\Validator({"name":"Regex", "options":{"pattern":"/^[0-9]{10}$/"}})
    */
    protected $phoneReference;

    /** 
    * @ORM\Column(type="string", nullable=true, length=10) 
    * @Annotation\Attributes({"type":"text"})
    * @Annotation\Options({"label":"Cell Phone #:"})
    * @Annotation\Validator({"name":"Regex", "options":{"pattern":"/^[0-9]{10}$/"}})    
    */
    protected $phoneCell;

    /** 
    * @ORM\Column(type="string", nullable=true) 
    * @Annotation\Attributes({"type":"text"})
    * @Annotation\Options({"label":"Home Address:"})
    */
    protected $addressHome;

    /** 
    * @ORM\Column(type="string", nullable=true) 
    * @Annotation\Attributes({"type":"text"})
    * @Annotation\Options({"label":"Work Address:"})
    */
    protected $addressWork;

    /** 
    * @ORM\Column(type="datetime") 
    * @Annotation\Attributes({"type":"hidden"})
    * @Annotation\Exclude()
    */
    protected $timeStamp;
    
    // ------------------------------ Getters ------------------------------ //
    public function getInvestorId() {
        return $this->investorId;
    }
    public function getContributions() {
        return $this->contributions;
    }
    public function getFirstName() {
        return $this->firstName;
    }
    public function getLastName() {
        return $this->lastName;
    }
    public function getStateId() {
        return $this->stateId;
    }
    public function getStatus() {
        return $this->status;
    }
    public function getPhoneHome() {
        return $this->phoneHome;
    }
    public function getPhoneWork() {
        return $this->phoneWork;
    }
    public function getPhoneReference() {
        return $this->phoneReference;
    }
    public function getPhoneCell() {
        return $this->phoneCell;
    }
    public function getAddressHome() {
        return $this->addressHome;
    }
    public function getAddressWork() {
        return $this->addressWork;
    }
    public function getTimeStamp() {
        return $this->timeStamp;
    }

    // ------------------------------ Setters ------------------------------ //
    public function setContributions($a) {
        $this->contributions = $a;
    }
    public function setFirstName($fn) {
        $this->firstName = $fn;
    }
    public function setLastName($ln) {
        $this->lastName = $ln;
    }
    public function setStateId($sId) {
        $this->stateId = $sId;
    }
    public function setStatus($s) {
        $this->status = $s;
    }
    public function setPhoneHome($ph) {
        $this->phoneHome = $ph;
    }
    public function setPhoneReference($pr) {
        $this->phoneReference = $pr;
    }
    public function setPhoneCell($pc) {
        $this->phoneCell = $pc;
    }
    public function setAddressHome($ah) {
        $this->addressHome = $ah;
    }
    public function setAddressWork($aw) {
        $this->addressWork = $aw;
    }
    public function setTimeStamp() {
        $this->timeStamp = new \DateTime("now");
    }
    
    // ============================== Business Logic ============================== //

    // ------------------------------ Static Properties ------------------------------//
    private static $count;

    // ------------------------------ Static Methods ------------------------------//
    public static function getCount() {
        return Investor::$count;
    }
    public static function updateCount($objectManager) {
        Investor::$count = count($objectManager->getRepository('Investor\Entity\Investor')->findAll());
    }

    /**
    * @param Service $objectManager The Entity Manager
    * @param string $stateId The state ID to look up against
    * @param form $form A reference to the form we will be using to display error messages on
    * @return bool Returns true if stateID is indeed unique, else it returns false and renders error message on form
    */
    public static function isUniqueStateId($objectManager, $stateId, &$form) {
        $repo = $objectManager->getRepository("Investor\Entity\Investor");
        $nullInvestor = $repo->findOneBy(array("stateId" => $stateId));            
        if ($nullInvestor != null) {
            $form->get("stateId")->setMessages(array("Repeated State Id"));
            return false;
        }       
        return true;
    }

    // ------------------------------ Methods ------------------------------ //
    public function __construct() {
        $this->contributions = new \Doctrine\Common\Collections\ArrayCollection();
    }
}