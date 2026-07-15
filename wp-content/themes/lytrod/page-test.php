<?php
/**
 * My Subscriptions section on the My Account page
 *
 * @author   Prospress
 * @category WooCommerce Subscriptions/Templates
 * @version  2.6.4
 */
/*


// You can only upgrade VRCUt Impose to Plus

// there is no VRCut Controller plus
// */

// if ( ! defined( 'ABSPATH' ) ) {
// 	exit; // Exit if accessed directly
// }
// ?>
// <?php 
//     $args = array(
//         'post_type'  => 'shop_subscription',
//         'post_status' => array('wc-active'),
//         'posts_per_page' => -1
//         // 'author'=>get_current_user_id()
//     );

//     $query = new WP_Query($args);
//     $original = array(
//         'product'=>array(),
//         'serial'=>array(),
//         'status'=>array(),
//         'order_id'=>array(),
//         'expiration'=>array()
//     );
//     $final = array(
//         'product'=>array()
//     );
//     foreach ($query->posts as $value) {
//         $order =  wc_get_order($value);
//         if($order->customer_id == get_current_user_id()){
          
//         for($i=0;$i<count($order->meta_data);$i++){       
//             if($order->meta_data[$i]->key == 'vrcut_serial_number'||$order->meta_data[$i]->key == 'vrcut_impose_serial_number'){
//                     array_push($original['product'],($order->meta_data[$i]->key == 'vrcut_impose_serial_number') ? 'VRCut Serial Number':'VRCut Serial Number');
//                     array_push($original['serial'],$order->meta_data[$i]->value);
//                     array_push($original['order_id'], $order->id);
//                     array_push($original['status'], $order->get_status());
//                     array_push($original['expiration'], $order->get_date_to_display( 'next_payment' ));   
                    
//             }
            
//         }
//         for($i=0;$i<count($order->meta_data);$i++){       
//             if($order->meta_data[$i]->key == 'intellicut_serial_number_global'){
//                     array_push($original['product'],($order->meta_data[$i]->key == 'intellicut_serial_number_global') ? 'Intellicut Global':'Intellicut Global');
//                     array_push($original['serial'],$order->meta_data[$i]->value);
//                     array_push($original['order_id'], $order->id);
//                     array_push($original['status'], $order->get_status());
//                     array_push($original['expiration'], $order->get_date_to_display( 'next_payment' ));   
                    
//             }
            
//         }
//         for($i=0;$i<count($order->meta_data);$i++){       
//             if($order->meta_data[$i]->key == 'intellicut_serial_number'){
//                     array_push($original['product'],($order->meta_data[$i]->key == 'intellicut_serial_number') ? 'Intellicut':'Intellicut');
//                     array_push($original['serial'],$order->meta_data[$i]->value);
//                     array_push($original['order_id'], $order->id);
//                     array_push($original['status'], $order->get_status());
//                     array_push($original['expiration'], $order->get_date_to_display( 'next_payment' ));   
                    
//             }
            
//         }
      

//         // $final['product'] = array_merge($final['product'],$original['product']);
//         }
//     }
//         echo '<pre>';
//             print_r($original);
//         echo'</pre>';
//         die();

// // $original

//         // function unique_multidim_array($array, $key1,$key2,$key3) {
            
//         //     $key_array1 = array();
//         //     $key_array1 = $array[$key1];
//         //     $unique = array();
//         //     $confirmation = array();
//         //     $confirmation = $array[$key2];
//         //     foreach( $key_array1 as $key => $h ){
//         //         if( ! in_array($h, $unique ) ){
//         //             $unique[$key] = $h;
//         //         }
//         //         else
//         //         $unique[$key] = 0; 
//         //     }
//         //     $temp = array();
//         //     $temp1 = array();
//         //     $temp2 = array();
//         //     $newarry1 = array();
//         //     $newarry2 = array();
//         //     $newarry3 = array();
//         //     foreach($array as $key11 => $h1){
//         //         foreach($h1 as $key22 => $h2){
//         //             $val = $unique[$key22];
//         //             $val2 = $confirmation[$key22];
                   
                    
//         //             if($val>0 && $val2 == 'yes'){
//         //                 $temp1[$key22] = $h2;
                        
//         //             }elseif($val>0 && $val2 == 'no'){
//         //                 $temp2[$key22] = $h2;
//         //             }
//         //             elseif($val==0){
//         //                 $temp[$key22] = $h2;
//         //             } 
//         //         }
//         //         $newarry1[$key11] = $temp;
//         //         $newarry2[$key11] = $temp1; 
//         //         $newarry3[$key11] = $temp2; 
//         //     }
//         //         echo '<pre>';
//         //         print_r($newarry3);
//         //         print_r($newarry2);
//         //         print_r($newarry1);
//         //         echo'</pre>';   
//         // }
//         // unique_multidim_array($original,'serial','confirmation','yes');
       
    
     
   ?>

                        
                     
                                       
                        
                          
                        
                           
                           
     



