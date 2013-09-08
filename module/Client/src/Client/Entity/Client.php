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

    // =========================================================================

    public static $count;

	// public function __get($property) {
	// 	return (isset($this->{$property}) ? $this->{$property} : null);
	// }

    public function __construct() {
        $this->accounts = new \Doctrine\Common\Collections\ArrayCollection();
    }

    //Getters
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

    //Setters
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
}