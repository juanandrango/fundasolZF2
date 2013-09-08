<?php
namespace Fundasol\Model;

class Fundasol {

	public static function amountExpected($payments, $dateStart, $dateEnd) {
		$amount = 0;
		foreach ($payments as $payment) {
			if (\strtotime($payment->getDueDateStr()) <= \strtotime($dateEnd)
				&& \strtotime($payment->getDueDateStr()) >= \strtotime($dateStart)) {
				$amount += $payment->getAmount();
			}
		}
		return $amount;
	}

	public static function amountReality($payments, $dateStart, $dateEnd) {
		$amount = 0;
		foreach ($payments as $payment) {
			if ($payment->getStatus() == \Payment\Entity\Payment::ONTIME 
				&& \strtotime($payment->getDueDateStr()) <= \strtotime($dateEnd)
				&& \strtotime($payment->getDueDateStr()) >= \strtotime($dateStart)) {
				$amount += $payment->getAmount();
			}
		}
		return $amount;
	}

	public static function amountCollected($payments, $dateStart, $dateEnd) {
		$amount = 0;
		foreach ($payments as $payment) {
			if (($payment->getStatus() == \Payment\Entity\Payment::ONTIME 
				|| $payment->getStatus() == \Payment\Entity\Payment::LATE)
				&& \strtotime($payment->getPaidTimeStamp()->format('Y-m-d')) <= \strtotime($dateEnd)
				&& \strtotime($payment->getPaidTimeStamp()->format('Y-m-d')) >= \strtotime($dateStart)) {
				$amount += $payment->getAmount();
			}
		}
		return $amount;
	}

	public static function amountOnTransit($payments, $dateStart, $dateEnd) {
		$amount = 0;
		foreach ($payments as $payment) {
			if ($payment->getStatus() != \Payment\Entity\Payment::ONTIME 
				&& $payment->getStatus() != \Payment\Entity\Payment::LATE
				//&& \strtotime($payment->getPaidTimeStamp()->format('Y-m-d')) <= \strtotime($dateEnd)
				//&& \strtotime($payment->getPaidTimeStamp()->format('Y-m-d')) >= \strtotime($dateStart)
				) {
				$amount += $payment->getAmount();
			}
		}
		return $amount;	
	}

	public static function amountAvailable($payments, $dateStart, $dateEnd) {
		return "";
	}

	public static function amountTotal($accounts) {
		return "";
	}

}
