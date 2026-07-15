fetch(window.location.pathname + '../wp-json/subscription/v1/getSubscription')
  .then(response => response.json())
  .then(data => {
    const $ = (val)=> document.querySelector(val)
    


     currentUserData =  data.product.filter((currentuser)=>{
            return currentuser.authorName == $('#currentuser').value
      })
      
      console.log(currentUserData)




    try{
      /*VRCut Controller*/
            let filterDataVRCutImpose =  currentUserData.filter((item)=>{
            return item.product_name == "VRCut Impose" && item.current_action == "Add License Years"
            })
            console.log(filterDataVRCutImpose)
            $('#lytrod_renewal_current_product_name_vrcutimpose').value = filterDataVRCutImpose[0].product_name
            $('#lytrod_renewal_current_product_id_vrcutimpose').value = filterDataVRCutImpose[0].current_product
            $('#lytrod_renewal_serial_number_vrcutimpose').value = filterDataVRCutImpose[0].product_serial
            $('#confirmedpending').value = 'No'
            document.querySelector('#lytrod_renwal_year_number_vrcutimpose').value = document.querySelector("#order_review > input[type=text]").value.match(/\d+/)[0];

      
      }catch(e){
      console.log(e)
      }

      try{
            /*VRCut Controller*/
            let filterDataVRCutImpose =  currentUserData.filter((item)=>{
                  return item.product_name == "VRCut Impose" && item.current_action == "Upgrade To Plus"
            })
            console.log(filterDataVRCutImpose)
            $('#lytrod_renewal_current_product_name_vrcut_impose_plus_upgrade').value = filterDataVRCutImpose[0].product_name
            $('#lytrod_renewal_current_product_id_vrcut_impose_plus_upgrade').value = filterDataVRCutImpose[0].current_product
            $('#lytrod_renewal_serial_number_vrcut_impose_plus_upgrade').value = filterDataVRCutImpose[0].product_serial
            $('#confirmedpending').value = 'No'
            document.querySelector('#lytrod_renwal_year_number_vrcutimpose').value = document.querySelector("#order_review > input[type=text]").value.match(/\d+/)[0]; 
      }catch(e){
            console.log(e)
      }

     /*VRCut Controller Renewal*/
   try{   
            let filterData =  currentUserData.filter((item)=>{
                  return item.product_name == "VRCut Controller"&& item.current_action == "Add License Years"
            })
            $('#lytrod_renewal_current_product_name_vrcutcontroller').value = filterData[0].product_name
            $('#lytrod_renewal_current_product_id_vrcutcontroller').value = filterData[0].current_product
            $('#lytrod_renewal_serial_number_vrcutcontroller').value = filterData[0].product_serial
            $('#confirmedpending').value = 'No'
            document.querySelector('#lytrod_renwal_year_number_vrcutcontroller').value = document.querySelector("#order_review > input[type=text]").value.match(/\d+/)[0];
         console.log(filterData)
   }
   catch(e){
      console.log(e)
   }


   /*intellcut Renewal */
   try{   
         let filterDataIntellicut =  currentUserData.filter((item)=>{
               return item.product_name == "Intellicut"  && item.current_action == "Add License Years"
         })
         $('#lytrod_renewal_current_product_name_intellicut').value = filterDataIntellicut[0].product_name
         $('#lytrod_renewal_current_product_id_intellicut').value = filterDataIntellicut[0].current_product
         $('#lytrod_renewal_serial_number_intellicut').value = filterDataIntellicut[0].product_serial
         $('#confirmedpending').value = 'No'
         document.querySelector('#lytrod_renwal_year_number_intellicut').value = document.querySelector("#order_review > input[type=text]").value.match(/\d+/)[0];
      console.log(filterDataIntellicut)
   }
   catch(e){
         console.log(e)
   }
   
   /*intellcut  Plus Renewal*/

   try{   
   
      let filterDataIntellicutUpgrade =  currentUserData.filter((item)=>{
            return item.product_name == "Intellicut Plus" && item.current_action == "Add License Years"
      })
            $('#lytrod_renewal_current_product_name_intellicut_plus_renewal').value = filterDataIntellicutUpgrade[0].product_name
            $('#lytrod_renewal_current_product_id_intellicut_plus_renewal').value = filterDataIntellicutUpgrade[0].current_product
            $('#lytrod_renewal_serial_number_intellicut_plus_renewal').value = filterDataIntellicutUpgrade[0].product_serial
            $('#lytrod_renewal_years_plus_upgrade').value = document.querySelector("#order_review > input[type=text]").value.match(/\d+/)[0];
            $('#confirmedpending').value = 'No'
            console.log('helo', filterDataIntellicutUpgrade)
      }
      catch(e){
            console.log(e)
      }

        /*intellcut Upgrade*/
   try{   
      let filterDataIntellicutUpgrade =  currentUserData.filter((item)=>{
            return item.product_name == "Intellicut" && item.current_action == "Upgrade To Plus"
      })
      
      $('#lytrod_renewal_current_product_name_intellicut_plus_upgrade').value = filterDataIntellicutUpgrade[0].product_name
      $('#lytrod_renewal_current_product_id_intellicut_plus_upgrade').value = filterDataIntellicutUpgrade[0].current_product
      $('#lytrod_renewal_serial_number_intellicut_plus_upgrade').value = filterDataIntellicutUpgrade[0].product_serial
      $('#confirmedpending').value = 'No'
   console.log(filterDataIntellicutUpgrade)
      }
      catch(e){
            console.log(e)
      }

      /*intellcut Global Renewal*/
   try{   
      let filterData =  currentUserData.filter((item)=>{
            return item.product_name == "Intellicut Global"  && item.current_action == "Add License Years"
      })
      $('#lytrod_renewal_current_product_name_intellicutglobal').value = filterData[0].product_name
      $('#lytrod_renewal_current_product_id_intellicutglobal').value = filterData[0].current_product
      $('#lytrod_renewal_serial_number_intellicutglobal').value = filterData[0].product_serial
      $('#confirmedpending').value = 'No'
      document.querySelector('#lytrod_renwal_year_number_intellicutglobal').value = document.querySelector("#order_review > input[type=text]").value.match(/\d+/)[0];
   console.log(filterData)
      }
      catch(e){
            console.log(e)
      }
      /*intellcut Global Upgrade*/
      try{   
            // console.log( currentUserData)
         let filterData =  currentUserData.filter((item)=>{
            
               return item.product_name == "Intellicut Global"   && item.current_action == "Upgrade To Plus"
         })
         $('#lytrod_renewal_current_product_name_intellicutglobalplus').value = filterData[0].product_name
         $('#lytrod_renewal_current_product_id_intellicutglobalplus').value = filterData[0].current_product
         $('#lytrod_renewal_serial_number_intellicutglobalplus').value = filterData[0].product_serial
         $('#confirmedpending').value = 'No'
         document.querySelector('#lytrod_renwal_year_number_intellicutglobal').value = document.querySelector("#order_review > input[type=text]").value.match(/\d+/)[0];
      console.log(filterData)
         }
         catch(e){
               console.log(e)
         }

      try{   
            let filterData =  currentUserData.filter((item)=>{
                  return item.product_name == "Intellicut Global Plus" && item.current_action == "Add License Years"
            })
            $('#lytrod_renewal_current_product_name_intellicut_global_plus_renewal').value = filterData[0].product_name
            $('#lytrod_renewal_current_product_id_intellicut_global_plus_renewal').value = filterData[0].current_product
            $('#lytrod_renewal_serial_number_intellicut_global_plus_renewal').value = filterData[0].product_serial
            $('#confirmedpending').value = 'No'
            $('#lytrod_renewal_years_intellicut_global_plus_renewal_years').value = document.querySelector("#order_review > input[type=text]").value.match(/\d+/)[0];

            console.log(filterData)
      }
      catch(e){
            console.log(e)
      }

   
      //VRCut Add License Years
      
      try{   
            let filterData =  currentUserData.filter((item)=>{
                  return item.product_name =="VRCut" && item.current_action == "Add License Years"
            })
            $('#lytrod_renewal_current_product_name_vrcut').value = filterData[0].product_name
            $('#lytrod_renewal_current_product_id_vrcut').value = filterData[0].current_product
            $('#lytrod_renewal_serial_number_vrcut').value = filterData[0].product_serial
            $('#confirmedpending').value = 'No'
            $('#lytrod_renwal_year_number_vrcut').value = document.querySelector("#order_review > input[type=text]").value.match(/\d+/)[0];
            $('#lytrod_renwal_second_vrcut').value = filterData[0].secondary_vrcut
            $('#lytrod_vrcut_current_action').value = filterData[0].current_action

            console.log(filterData)
      }
      catch(e){
            console.log(e)
      }

      //VRCut Plus Add License Years
      try{   
            let filterData =  currentUserData.filter((item)=>{
                  return item.product_name =="VRCut Plus" && item.current_action == "Add License Years"
            })
            $('#lytrod_vrcut_plus_renewal_current_product_name').value = filterData[0].product_name
            $('#lytrod_vrcut_plus_renewal_current_product_id').value = filterData[0].current_product
            $('#lytrod_vrcut_plus_renewal_serial_number').value = filterData[0].product_serial
            $('#confirmedpending').value = 'No'
            $('#lytrod_vrcut_plus_renewal_years').value = document.querySelector("#order_review > input[type=text]").value.match(/\d+/)[0];
          

            console.log(filterData)
      }
      catch(e){
            console.log(e)
      }

      //VRCut Upgrade To Plus
      try{   
            let filterData =  currentUserData.filter((item)=>{
                  return item.product_name =="VRCut" && item.current_action == "Upgrade To Plus"
            })
            $('#lytrod_renewal_current_product_name_vrcut_plus').value = filterData[0].product_name
            $('#lytrod_renewal_current_product_id_vrcut_plus').value = filterData[0].current_product
            $('#lytrod_renewal_serial_number_vrcut_plus').value = filterData[0].product_serial
            $('#confirmedpending').value = 'No'
            // $('#lytrod_renwal_year_number_vrcut').value = document.querySelector("#order_review > input[type=text]").value.match(/\d+/)[0];
            $('#lytrod_renwal_second_vrcut_plus').value = filterData[0].secondary_vrcut
         
            console.log(filterData[0].secondary_vrcut)
      }
      catch(e){
            console.log(e)
      }

  });
