<?php
/*
Edit single recurring to Db and Gateway
*/
global $wpdb, $WC_Gateway_EBiz_Rec, $Susbscriptions_Econnect;
$sign = $WC_Gateway_EBiz_Rec->getPermalinkStructure() ? '?' : '&';
$recurrings_edit_url = $WC_Gateway_EBiz_Rec->makePermalinkUrl('subscriptions-edit?recID=');
$history_list_url = $WC_Gateway_EBiz_Rec->makePermalinkUrl('history');

/* For edit recurring page start */
if (isset($_GET['recurringID'])) {
    if ((isset($_POST['unsub'])) && ($_POST['unsub'] == 'yes')) {
        $unsub = $GLOBALS['Susbscriptions_Econnect']->suspendDeleteBulkSubscription(array($_POST['ScheduledPaymentInternalId']), 1);

        if ($unsub) {
            // After successful unsub:
            wp_redirect($recurrings_edit_url . $_GET['recurringID'] . '&action=unsubscribed');
            exit;
        } else {
            // After unsuccessful unsub:
            wp_redirect($recurrings_edit_url . $_GET['recurringID'] . '&action=error');
            exit;
        }
    }

    if ((isset($_POST['delete'])) && ($_POST['delete'] == 'yes')) {
        $delete = $GLOBALS['Susbscriptions_Econnect']->suspendDeleteBulkSubscription(array($_POST['ScheduledPaymentInternalId']), 3);

        if ($delete) {
            // After successful delete:
            wp_redirect($recurrings_edit_url . $_GET['recurringID'] . '&action=deleted');
            exit;
        } else {
            // After unsuccessful delete:
            wp_redirect($recurrings_edit_url . $_GET['recurringID'] . '&action=error');
            exit;
        }
    }

    if ((isset($_POST['resub'])) && ($_POST['resub'] == 'yes')) {
        $resub = $GLOBALS['Susbscriptions_Econnect']->suspendDeleteBulkSubscription(array($_POST['ScheduledPaymentInternalId']), 0);

        if ($resub) {
            // After successful resubscribe:
            wp_redirect($recurrings_edit_url . $_GET['recurringID'] . '&action=resubscribed');
            exit;
        } else {
            // After unsuccessful resubscribe:
            wp_redirect($recurrings_edit_url . $_GET['recurringID'] . '&action=error');
            exit;
        }
    }

    if ((isset($_POST['update'])) && ($_POST['update'] == 'yes')) {
        $modifyRecurring = $WC_Gateway_EBiz_Rec->modifyScheduleRecurringPayment($_POST, $_GET['recurringID']);
        if ($modifyRecurring) {
            // After successful resubscribe:
            wp_redirect($recurrings_edit_url . $_GET['recurringID'] . '&action=updated');
            exit;
        } else {
            // After unsuccessful resubscribe:
            wp_redirect($recurrings_edit_url . $_GET['recurringID'] . '&action=error');
            exit;
        }
    }
}
/* For edit recurring page end */

/* For history page start */
if (isset($_GET['refNumber'])) {
    if ((isset($_GET['print'])) && ($_GET['print'] == 'yes')) {
        $print = $WC_Gateway_EBiz_Rec->printReceiptData($_GET['refNumber']);
        $receipt = base64_decode($print);
        if (!empty($print)) {
            // After successful print:
            $js_url = plugins_url('assets/js/jquery-3.6.0.min.js', dirname(__DIR__));
            ?>
            <head>
                <script src="<?php echo $js_url; ?>"></script>
            </head>
            <body>
            Redirecting...
            <div id="divToPrint" style="display:none;">
                <div style="width:600px;height:500px;background-color:teal;">
                    <?php echo $receipt; ?>
                </div>
            </div>
            <script type="text/javascript">
                $(document).ready(function () {
                    function PrintDiv() {
                        var divToPrint = document.getElementById('divToPrint');
                        var popupWin = window.open('', '_blank', 'width=600,height=500');
                        popupWin.document.open();
                        popupWin.document.write('<html><body onload="window.print()">' + divToPrint.innerHTML + '</html>');
                        popupWin.document.close();
                    }

                    PrintDiv();

                    setTimeout(function () {
                        window.location.href = "<?php echo $history_list_url . $sign; ?>print=ok";
                    }, 1000);
                });
            </script>
            </body>
            <?php
        } else {
            // After unsuccessful print:
            ?>
            <body>
            Redirecting...
            <script type="text/javascript">
                $(document).ready(function () {
                    setTimeout(function () {
                        window.location.href = "<?php echo $history_list_url . $sign; ?>print=error";
                    }, 1000);
                });
            </script>
            </body>
            <?php
        }
    }

    if ((isset($_GET['email'])) && ($_GET['email'] == 'yes')) {
        $email = $WC_Gateway_EBiz_Rec->emailReceipt($_GET['refNumber']);

        if ($email) {
            // After successful email:
            wp_redirect($history_list_url . $sign . 'email=sent');
            exit;
        } else {
            // After unsuccessful email:
            wp_redirect($history_list_url . $sign . 'email=error');
            exit;
        }
    }
}
/* For history page end */
