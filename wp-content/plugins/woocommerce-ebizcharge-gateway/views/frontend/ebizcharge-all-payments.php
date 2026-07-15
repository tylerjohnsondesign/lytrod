<?php
/*
Get all recurrings from database
*/
global $WC_Gateway_EBiz_Rec;
$searchTransactions = $WC_Gateway_EBiz_Rec->getCustomerAllTransactionsFromGateway();

$counter = 1;
foreach ( $searchTransactions as $transactions ) {
	$amount = number_format( $transactions->Details->Amount, 2 );
	$p_date = date( "Y-m-d", strtotime( $transactions->DateTime ) );
	$auth_code = ( !empty( $transactions->Response->AuthCode ) ? $transactions->Response->AuthCode : 'N/A' );
	$ref_num = ( !empty( $transactions->Response->RefNum ) ? $transactions->Response->RefNum : 'N/A' );
	$status = $WC_Gateway_EBiz_Rec->get_payment_status( $transactions->Response->ResultCode );

	if ( isset( $transactions->CreditCardData->CardType ) ) {
		$ccType = $transactions->CreditCardData->CardType;
		$ccNumber = $transactions->CreditCardData->CardNumber;
		$payment_method = $WC_Gateway_EBiz_Rec->get_ebiz_rec_payment_array( $ccNumber, $ccType, 20, 20 );
	} else if ( isset( $transactions->CheckData->AccountType ) ) {
		$accountType = $transactions->CheckData->AccountType;
		$accountNumber = $transactions->CheckData->Account;
		$payment_method = $WC_Gateway_EBiz_Rec->get_ebiz_rec_payment_array( $accountNumber, $accountType, 20, 20 );
	}

	$transactionsArray[] = array(
		'counter' => $counter,
		'amount' => $amount,
		'p_date' => $p_date,
		'auth_code' => $auth_code,
		'ref_num' => $ref_num,
		'four_digits' => $payment_method[ 'four_digits' ],
		'payment_method_type' => $payment_method[ 'payment_method_type' ],
		'pmethod_image_url' => $payment_method[ 'pmethod_image_url' ],
		'status_class' => $status[ 'class' ],
		'status' => $status[ 'title' ]
	);

	$counter++;
}

echo json_encode( $transactionsArray );
?>