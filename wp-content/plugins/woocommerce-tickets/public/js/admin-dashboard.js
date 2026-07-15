barba.init({

  views:[
    {
      namespace:'home',
      beforeEnter({next}){


        setTimeout(() => {
          new Vue({
            el:'#admin-tickets-dash',
            data:{
                data:[],
                id:'',
                name:'',
                status:'',
                reason:'',
                registerProduct:'',
                serial:'',
                nameDups:[],
                reasonDups:[],
                placeID:'Search by Id',
                placeName:'Search by Name',
                placeserial:'Search by Serial',
                placeProduct:'Search by Product',
                placeStatus:'Search by Status',
         
            },
            mounted(){
                fetch(woocommerceTickets.root_url +'/wp-json/form/v1/submitQuestion')
                   .then(res=>res.json())
                   .then(data=>{
                             this.data = data
                             console.log(this.data)
                             this.data.ticket.forEach(element => {
                        
                              this.nameDups.push(element.name)
                           
                            });
                            this.nameDups= [...new Set(this.nameDups)];
        
                            //reason dups
        
                            this.data.ticket.forEach(element => {
                        
                              this.reasonDups.push(element.reason)
                           
                            });
                            this.reasonDups= [...new Set(this.reasonDups)];
        
        
        
        
        
             
                            console.log(this.reasonDups);
                           
                        })
                   
                   
            },
            methods:{
                handleFilter(){



                  var p1 = new Promise((resolve, reject) => {
                    resolve('Success!');
                    // or
                    // reject(new Error("Error!"));
                  });
                  
                  p1.then(value => {
                      
                    let values = {
                      'id':this.id,
                      'name':this.name,
                      'status':this.status,
                      'reason':this.reason,
                      'serial':this.serial
                    }
      
     
        for (const key in values) {
            if (values[key] === '') {
              delete values[key];
            }

          }

          let size = Object.keys(values).length
          let str = ''
      
          if(size == 0){
            document.querySelector('#ticket_val').setAttribute('href',woocommerceTickets.root_url + '/tickets-dashboard/')
           
          }
        for (const key in values) {  

            
              
              if(size == 1){
               
                str+=  key + '-' + values[key] + '/'
                document.querySelector('#ticket_val').setAttribute('href',woocommerceTickets.root_url + '/tickets-dashboard/' +str)
                
              }
              if(size == 2){
                str+=  key + '-' + values[key] + '/'
                document.querySelector('#ticket_val').setAttribute('href',woocommerceTickets.root_url + '/tickets-dashboard/' +str)
                // document.querySelector('#ticket_val').click()  
                console.log('url added')
              }  
              if(size == 3){
                str+=  key + '-' + values[key] + '/'
                document.querySelector('#ticket_val').setAttribute('href',woocommerceTickets.root_url + '/tickets-dashboard/' +str)
                
              }  
              if(size == 4){
                console.log('yes')
                str+=  key + '-' + values[key] + '/'
                document.querySelector('#ticket_val').setAttribute('href',woocommerceTickets.root_url + '/tickets-dashboard/' +str)
             
              }  
              if(size == 5){
                str+=  key + '-' + values[key] + '/'
                document.querySelector('#ticket_val').setAttribute('href',woocommerceTickets.root_url + '/tickets-dashboard/' +str)
             
              }  
                   
                 
        }
                  })
                  .then((value)=>{
                       document.querySelector('#ticket_val').click()  
                  });





            
        
                   
                    
               
                },
                clearField(val){
              
        
                    if(val == 'id'){
                      this.id = ''
                    }
        
                    if(val == 'name'){
                      this.name = ''
                    }
        
                    
                    if(val == 'status'){
                      this.status = ''
                    }
        
                    
                    if(val == 'reason'){
                      this.reason = ''
                    }
        
                    
                    if(val == 'serial'){
                      this.serial = ''
                    }
                  
        
        
                }
            }
        })



        }, 1);
      }
      
    },
  ]
});


document.addEventListener("DOMContentLoaded", function(){
  document.querySelectorAll("#wpadminbar a").forEach((item)=>{
    item.setAttribute('data-barba-prevent','self')
  });


  document.querySelectorAll('.menu-item a').forEach((item)=>{
    item.setAttribute('data-barba-prevent','self')
  });
});