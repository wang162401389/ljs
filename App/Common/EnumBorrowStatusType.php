<?php
// 全局设置
class BorrowStatusType 
{
	const __default = self::Release;

	const Release             = 0;
	const FirstVerifyFailed   = 1;
	const FirstVerfiyPassed   = 2;
	const WaitForSecondVerify = 4;
	const SecondVerifyFailed  = 5;
	const WaitForRepayment    = 6;
	const RepaymentComplete   = 7;
	const WaitToRelease       = 8;

}
?>