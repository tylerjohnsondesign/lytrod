const subscriptions = () =>{


   document.querySelectorAll('.lytrodAddYears,.upgradetoplus').forEach((item)=>{
        item.addEventListener('click',(e)=>{
            collectYearsData(e)
        })
   })

  

   async function collectYearsData(e){
    
      
      try{
         let secondaryVRCut
         let dups =  document.querySelectorAll('.lytrodduplicates')
      dups.forEach((value)=>{
         if(value.innerText === e.target.parentElement.parentElement.children[1].innerText){
            secondaryVRCut =  value.getAttribute('data-id')
               
         //   window.secondaryVRCutStore = []
         //   window.secondaryVRCut.push({'vrcut':secondaryVRCut}) 
         }
      })
      console.log(secondaryVRCut)

 
         let yearsData ={
            "title":e.target.parentElement.parentElement.children[0].innerText,
            'productname':e.target.parentElement.parentElement.children[0].innerText,
            'productserial':e.target.parentElement.parentElement.children[1].innerText,
            'currexp':e.target.parentElement.parentElement.children[3].innerText,
            'currrent_post_id':e.target.parentElement.parentElement.children[0].getAttribute('data-id'),
            'current_action': e.target.innerText,
            'secondary_vrcut':secondaryVRCut
         }
         console.log(yearsData)
   
      const subscriptionData = await fetch(lytrodData.root_url + '/wp-json/subscription/v1/createSubscription',{
         method : 'POST',
         headers : {
            'Content-Type': 'application/json',
             'X-WP-Nonce' :  lytrodData.nonce // here you used the wrong name
           },
           credentials: 'same-origin',
           body:JSON.stringify(yearsData)
      })
         let finalSubscription = await subscriptionData.json()
         console.log(finalSubscription,'success')
            // // -------------------------------Intellicut------------------------------------------------------

         if(e.target.parentElement.parentElement.children[0].innerText =='Intellicut' &&  e.target.innerText == 'Upgrade To Plus' && finalSubscription !== '' ){
            window.location.href = '/product/intellicut-plus-upgrade/'
         }
         if(e.target.parentElement.parentElement.children[0].innerText =='Intellicut'&&  e.target.innerText == 'Add License Years'  && finalSubscription !== ''){
            window.location.href = '/product/intellicut-renewal/'
         }
         if(e.target.parentElement.parentElement.children[0].innerText =='Intellicut Plus'&&  e.target.innerText == 'Add License Years'  && finalSubscription !== ''){
            window.location.href = '/product/intellicut-plus-renewal/'
         }
         //---------------------------------------Intellicut Global-----------------------------------
         
         if(e.target.parentElement.parentElement.children[0].innerText =='Intellicut Global' &&  e.target.innerText == 'Upgrade To Plus' && finalSubscription !== ''){
            window.location.href = '/product/intellicut-global-plus-upgrade/'
         }
         if(e.target.parentElement.parentElement.children[0].innerText =='Intellicut Global' &&  e.target.innerText == 'Add License Years' && finalSubscription !== ''){
            window.location.href = '/product/intellicut-global-renewal/'
         }

         if(e.target.parentElement.parentElement.children[0].innerText =='Intellicut Global Plus' &&  e.target.innerText == 'Add License Years' && finalSubscription !== ''){
            window.location.href = '/product/intellicut-global-plus-renewal/'
         }

         // -----------------------VRCut----------------------------------------------------------

         if(e.target.parentElement.parentElement.children[0].innerText =='VRCut' && e.target.innerText == 'Upgrade To Plus'  && finalSubscription !== ''){
            window.location.href = '/product/vrcut-plus-upgrade/'
         }

         if(e.target.parentElement.parentElement.children[0].innerText =='VRCut' && e.target.innerText == 'Add License Years' && finalSubscription !== '' ){
            window.location.href = '/product/vrcut-renewal/'
         }

         if(e.target.parentElement.parentElement.children[0].innerText =='VRCut Plus' && e.target.innerText == 'Add License Years' && finalSubscription !== '' ){
            window.location.href = '/product/vrcut-plus-renewal/'
         }
      
     
   
       
    
        
        

      }catch(e){
         console.log(e)
      }
         

         // break;


       
      // const sendYears = await fetch()

      
   }



   

/**
 * VRCut Renewal Years
 * 
*/


 

   // async function()
}

export default subscriptions;
