<?php
namespace Fundasol\Model;

class Fundasol {

	// ------------------------------ Static Methods ------------------------------ //


	/**
	* @param ArrayCollection $payments It is a doctrine collection holding Payment Objects
	* @param date $dateStart The start of the range
	* @param date $dateEnd The end of the range
	* @return float[string] A list of dates within the range (inclusive) with their respective expected total amounts
	*/
	public static function getExpectedArray($payments, $dateStart, $dateEnd) {
		$dataArray = array();
		for ($date = $dateStart ; 
			\strtotime($date) <= \strtotime($dateEnd) ; ) {
			if (date('N', strtotime($date)) < 6) {
				$dataArray[$date] = 0;
			}
			$dateObj = new \DateTime($date);
			date_add($dateObj, date_interval_create_from_date_string('1 days'));
			$date = $dateObj->format('Y-m-d');
		}
		foreach ($payments as $payment) {
			if (\strtotime($payment->getDueDateStr()) <= \strtotime($dateEnd)
				&& \strtotime($payment->getDueDateStr()) >= \strtotime($dateStart)) {				
				$currentDayAmount = $dataArray[$payment->getDueDateStr()];			
				$dataArray[$payment->getDueDateStr()] = $currentDayAmount + $payment->getAmount();	
			}
		}
		return $dataArray;
	}

	/**
	* @param ArrayCollection $payments It is a doctrine collection holding Payment Objects
	* @param date $dateStart The start of the range
	* @param date $dateEnd The end of the range
	* @return float[string] A list of dates within the range (inclusive) with their respective real total amounts
	*/
	public static function getRealityArray($payments, $dateStart, $dateEnd) {
		$dataArray = array();
		for ($date = $dateStart ; 
			\strtotime($date) <= \strtotime($dateEnd) ; ) {
			if (date('N', strtotime($date)) < 6) {
				$dataArray[$date] = 0;
			}
			$dateObj = new \DateTime($date);
			date_add($dateObj, date_interval_create_from_date_string('1 days'));
			$date = $dateObj->format('Y-m-d');
		}
		foreach ($payments as $payment) {
			if ($payment->getStatus() == \Payment\Entity\Payment::ONTIME 
				&& \strtotime($payment->getDueDateStr()) <= \strtotime($dateEnd)
				&& \strtotime($payment->getDueDateStr()) >= \strtotime($dateStart)) {
				$currentDayAmount = $dataArray[$payment->getDueDateStr()];			
				$dataArray[$payment->getDueDateStr()] = $currentDayAmount + $payment->getAmount();					
			}
		}
		return $dataArray;
	}

	/**
	* @param ArrayCollection $payments It is a doctrine collection holding Payment Objects
	* @param date $dateStart The start of the range
	* @param date $dateEnd The end of the range
	* @return float[string] A list of dates within the range (inclusive) with their respective collected total amounts
	*/
	public static function getCollectedArray($payments, $dateStart, $dateEnd) {
		$dataArray = array();
		for ($date = $dateStart ; 
			\strtotime($date) <= \strtotime($dateEnd) ; ) {
			if (date('N', strtotime($date)) < 6) {
				$dataArray[$date] = 0;				
			}
			$dateObj = new \DateTime($date);
			date_add($dateObj, date_interval_create_from_date_string('1 days'));			
			$date = $dateObj->format('Y-m-d');
		}		
		foreach ($payments as $payment) {
			if (($payment->getStatus() == \Payment\Entity\Payment::ONTIME 
				|| $payment->getStatus() == \Payment\Entity\Payment::LATE)
				&& \strtotime($payment->getPaidTimeStamp()->format('Y-m-d')) <= \strtotime($dateEnd)
				&& \strtotime($payment->getPaidTimeStamp()->format('Y-m-d')) >= \strtotime($dateStart)) {
				$currentDayAmount = $dataArray[$payment->getPaidTimeStamp()->format('Y-m-d')];			
				$dataArray[$payment->getPaidTimeStamp()->format('Y-m-d')] = $currentDayAmount + $payment->getAmount();			
			}
		}
		return $dataArray;
	}

	/**
	* @param float[int] $valuesArray An Array of values to be added up together
	* @return float The sumation of all values
	*/
	public static function getTotalAmount($valuesArray) {
		$total = 0;
		foreach ($valuesArray as $value) {
			$total += $value;
		}
		return $total;
	}

	/**
	* @param ArrayCollection $payments It is a doctrine collection holding Payment Objects
	* @return float The total amount in Client's possession
	*/
	public static function amountOnTransit($payments) {
		$amount = 0;
		foreach ($payments as $payment) {
			if ($payment->getStatus() != \Payment\Entity\Payment::ONTIME 
				&& $payment->getStatus() != \Payment\Entity\Payment::LATE) {
				$amount += $payment->getAmount();
			} 
		}
		return $amount;	
	}

	/**
	* @param ArrayCollection $accounts It is a doctrine collection holding Account Objects
	* @return float The total requested amount
	*/
	public static function amountRequested($accounts) {
		$amount = 0;
		foreach ($accounts as $account) {
			if ($account->getStatus() == \Account\Entity\Account::PENDING) {
				$amount += $account->getAmount();
			}
		}
		return $amount;
	}

	/**
	* @param ArrayCollection $contributions It is a doctrine collection holding Contribution Objects
	* @param ArrayCollection $payments It is a doctrine collection holding Payment Objects
	* @return float The total amount available
	*/
	public static function amountAvailable($contributions, $payments) {
		return Fundasol::amountTotal($contributions) - Fundasol::amountOnTransit($payments);
	}

	/**
	* @param ArrayCollection $contributions It is a doctrine collection holding Contribution Objects
	* @return float The total amount from contributions
	*/
	public static function amountTotal($contributions) {
		$amount = 0;
		foreach ($contributions as $contribution) {
			if ($contribution->getStatus() == \Contribution\Entity\Contribution::OPEN) {
				$amount += $contribution->getAmount();
			}
		}
		return $amount;
	}
}
