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

	public static function amountAvailable($contributions, $payments) {
		return Fundasol::amountTotal($contributions) - Fundasol::amountOnTransit($payments);
	}

	public static function amountTotal($contributions) {
		$amount = 0;
		foreach ($contributions as $contribution) {
			if ($contribution->getStatus() == \contribution\Entity\contribution::OPEN) {
				$amount += $contribution->getAmount();
			}
		}
		return $amount;
	}
}
