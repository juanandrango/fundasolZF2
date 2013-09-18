<?php
namespace Client\Entity;
use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/** 
* Client Entity holds client's information, using an ID for internal purposes only and their
* state ID as a unique value. 
*
* A Client object creates accounts of type Account. Then he makes payment against that account. He
* is also able to delete account info (which will remove payment information with them) only once the 
* account has been paid off.  
*
*
* @ORM\Entity 
* @Annotation\Name("Client")
* @Annotation\Hydrator({"type":"Zend\Stdlib\Hydrator\ClassMethods", "options": {"underscoreSeparatedKeys": false}})
*/
class Client {
    // ============================== Database Columns ============================== //
    /**
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    * @ORM\Column(type="integer")
    * @Annotation\Attributes({"type":"hidden"})
    * @Annotation\Options({"label":"Id:"})
    */
    protected $clientId;

    /**
     * @ORM\OneToMany(targetEntity="Account\Entity\Account", mappedBy="client")
     */
    protected $accounts;

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
    public function getClientId() {
        return $this->clientId;
    }
    public function getAccounts() {
        return $this->accounts;
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
    public function setAccounts($a) {
        $this->accounts = $a;
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

    // ------------------------------ Static Properties ------------------------------ //
    private static $count;

    // ------------------------------ Static Methods ------------------------------ //
    public static function getCount() {
        return Client::$count;
    }
    public static function updateCount($objectManager) {
        Client::$count = count($objectManager->getRepository('Client\Entity\Client')->findAll());
    }

	// ------------------------------ Methods ------------------------------ //
    public function __construct() {
        $this->accounts = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
    * @return int[string] It returns the payment count from all client's accounts sorted by status
    */
    public function getPaymentCounts() {
        $counts = array(
            \Payment\Entity\Payment::DUE => 0,
            \Payment\Entity\Payment::LATE => 0,
            \Payment\Entity\Payment::ONTIME => 0,
            );
        foreach($this->getAccounts() as $account) {
            $tempCounts = $account->getPaymentCounts();
            $counts[\Payment\Entity\Payment::DUE] += $tempCounts[\Payment\Entity\Payment::DUE];
            $counts[\Payment\Entity\Payment::LATE] += $tempCounts[\Payment\Entity\Payment::LATE];
            $counts[\Payment\Entity\Payment::ONTIME] += $tempCounts[\Payment\Entity\Payment::ONTIME];
        }
        return $counts;
    }

    /**
    * @param Service $objectManager The Entity Manager
    * @param string $stateId The state ID to look up against
    * @param form $form A reference to the form we will be using to display error messages on
    * @return bool Returns true if stateID is indeed unique, else it returns false and renders error message on form
    */
    public static function isUniqueStateId($objectManager, $stateId, &$form) {
        $repo = $objectManager->getRepository("Client\Entity\Client");
        $nullClient = $repo->findOneBy(array("stateId" => $stateId));            
        if ($nullClient != null) {
            $form->get("stateId")->setMessages(array("Repeated State Id"));
            return false;
        }       
        return true;
    }
}