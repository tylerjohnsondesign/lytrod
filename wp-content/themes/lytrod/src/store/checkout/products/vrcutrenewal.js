fetch(window.location.pathname + '../wp-json/subscription/v1/getSubscription')
  .then(response => response.json())
  .then(data => {
    const $ = (val)=>{
        return document.querySelector(val);
    }

 

     
     /*VRCut Controller Renewal*/

     let vrcutControllerRenewal =  data.product.filter((item)=>{
        return item.product_name == "VRCut Controller Renewal"
     })
     $('#vrcut_controller_renwal_current_product_name').value = vrcutControllerRenewal[0].product_name
     $('#vrcut_controller_renwal_current_product_id').value = vrcutControllerRenewal[0].current_product
     $('#vrcut_controller_renwal_serial_number').value = vrcutControllerRenewal[0].product_serial
     $('#confirmedpending').value = 'No'
     document.querySelector('#vrcut_controller_renwal_year_number').value = document.querySelector("#order_review > input[type=text]").value.match(/\d+/)[0];

     console.log(vrcutControllerRenewal)
   




  });
