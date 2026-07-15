<?php
/*
Get all recurrings from database
*/
global $WC_Gateway_EBiz_Rec;
$ebiz = new WC_ebizcharge();
$tran = $ebiz->_initTransaction();

if ((isset($_GET['recurrings'])) && ($_GET['recurrings'] == 'list')) {
    // get data and store in a json array
    $query = $WC_Gateway_EBiz_Rec->getCustomerAllRecurringsFromDb();

    foreach ($query as $recurring) {
        $status = $WC_Gateway_EBiz_Rec->get_subscription_status($recurring->rec_status);
        $payment_method = $WC_Gateway_EBiz_Rec->get_payment_method_array($recurring->rec_pmethod_name, 20, 20);

        $subscriptions[] = array(
            'id' => $recurring->id,
            'start' => date("Y-m-d", strtotime($recurring->rec_start_date)),
            'end' => date("Y-m-d", strtotime($recurring->rec_end_date)),
            'amount' => number_format($recurring->rec_amount, 2),//'amount' => $WC_Gateway_EBiz_Rec->get_store_currency().$recurring->rec_amount,
            'product' => $recurring->item_name,
            'frequency' => $recurring->rec_frequency,
            'pmethod' => $recurring->rec_pmethod_name,
            'status_class' => $status['class'],
            'status' => $status['title'],
            'four_digits' => $payment_method['four_digits'],
            'payment_method_type' => $payment_method['payment_method_type'],
            'pmethod_image_url' => $payment_method['pmethod_image_url']
        );
    }
    echo json_encode($subscriptions);
}
?>