document.addEventListener('DOMContentLoaded',()=>{

  Vue.use( CKEditor );
  new Vue({
    el:"#app",
    data:{
      show: false,
      // openOrClose:'Open',
      showHighlight:false,
      finalImages:[],
      index:0,
      editor: ClassicEditor,
      editorData: '',
      editorConfig: {
          // The configuration of the editor.
      },
      activeTab: 0,
      categories: [
        "Create New Ticket", 
        "Ticket History"
      ],
      name:'',
      finalSize:0,
      id:[],
      registerProduct:'',
    
    },
    mounted(){
        console.log('hello')
    },
    methods:{

      handleProductInfo(e){
          console.log(e)
      },  
      activate(index) {
        this.activeTab = index;
      },

      enterHighLight(e){
        e.preventDefault()
        this.showHighlight = true
    },
    leaveHighLight(e){
      e.preventDefault()
      this.showHighlight = false
    },
    handleChange(e){

      files = e.target.files;
      this.handleFiles(files)

    },
    handleDrop(e){

        files = e.dataTransfer.files;
        this.handleFiles(files)

    },
    handleFiles(files){
   
        this.file = [...files]

        this.file.forEach(file=>{
          this.finalSize += Math.round((file.size)/1024)
        })

        if(this.finalSize>5000){
          alert('Sorry, file size is to large.');
          return
        }else{
          this.file.forEach(this.previewFile);
        }
       
       
    },
    removeFile(file){
    
      this.file.forEach(file=>{
        this.finalSize -= Math.round((file.size)/1024)
      })
     index = this.finalImages.indexOf(file)

     this.finalImages.splice(index,1)
    },
    previewFile(file){
      console.log(file)
      let reader = new FileReader()
      reader.readAsDataURL(file)
      let imageArray = this.finalImages
      reader.onloadend = function() {
        console.log(reader)
        imageArray.push({
          'src':reader.result,
          'fileType':file.type,
          'fileName':file.name
        })
      }
    },
  
      enter(el, done) {
      
        console.log(el )
      let tl = gsap.timeline()
        // tl.fromTo(el,0.5,{height:'0px'},{height:'500px'})
          // .to(el,0.5,{height:'unset'})
          .to(this.$refs.chevron,{rotate:'180deg'})
          .fromTo(el.children[0],0.5,{opacity:'0'},{opacity:'1',onComplete: done})
      
     
          
      },
      leave(el, done) {
        console.log(this.$refs.chevron)
       let tl = gsap.timeline()
        tl.fromTo(el.children[0],0.5,{opacity:'1'},{opacity:'0'})
          .to(this.$refs.chevron,{rotate:'0deg'})
          .fromTo(el,0.5,{height:el.offsetHeight + "px"},{height: 0 ,delay:0.5})
      
            
      },
     async submitTicket(e){


        e.preventDefault()
        document.querySelector('.spinner-loader').style.display = 'block';
        if(document.querySelector("#lytrod-name").value ==''){
          document.querySelector("#lytrod-name").style.border = '3px solid red'
            alert("Name must be filled out");
            document.querySelector('.spinner-loader').style.display = 'none';
            return false;
        }else if(document.querySelector("#lytrod-email").value ==''){
          document.querySelector("#lytrod-email").style.border = '3px solid red'
            alert("Email must be filled out");
            document.querySelector('.spinner-loader').style.display = 'none';
            return false;
        }else if(this.editorData ==''){
          document.querySelector("#lytrod-question").style.border = '3px solid red'
            alert("Your question must be filled out");

            document.querySelector('.spinner-loader').style.display = 'none';
            return false;
        }else{

           

            
                let formData ={
                    "name":document.querySelector("#lytrod-name").value,
                    "email":document.querySelector("#lytrod-email").value,
                    "question":this.editorData,
                    "question_file":this.finalImages,
                    "reason":document.querySelector("#register-product").options[document.querySelector("#register-product").selectedIndex].text,
                    "ticket_serial_number":document.querySelector(".serial-value").value
                 }

                 
                let connection = ''
                 fetch(woocommerceTickets.root_url+'/wp-json/form/v1/submitQuestion', {
                  method: 'POST', // or 'PUT'
                  headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce' :woocommerceTickets.nonce
                  },
                  body: JSON.stringify(formData),
                  })
                  .then(response => response.json())
                  .then(data => {
         
                    connection = data

                  })
                  .then(data => {

                    console.log(connection)
                   
                    let formDataEmail ={
                      "name":document.querySelector("#lytrod-name").value,
                      "email":document.querySelector("#lytrod-email").value,
                      "question":this.editorData.replace(/<[^>]*>?/gm, ''),
                      "question_file":this.finalImages,
                      "reason":document.querySelector("#register-product").options[document.querySelector("#register-product").selectedIndex].text,
                      "ticket_serial_number":document.querySelector(".serial-value").value,
                      "ticket_id": connection[0]
                   }

                   

                    fetch(woocommerceTickets.root_url+'/wp-json/form/v1/submitQuestionEmail', {
                      method: 'POST', // or 'PUT'
                      headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce' :woocommerceTickets.nonce
                      },
                      body: JSON.stringify(formDataEmail),
                      })
                      .then(response => response.json())
                      .then(data => {
             
                       
                        location.href = woocommerceTickets.root_url +'/singleticket/'+connection[0]
    
                      })
                      .catch((error) => {
                      console.error('Error:', error);
                      });
                    
                  })
                  .catch((error) => {
                  console.error('Error:', error);
                  });
                 
          }      
        }
    
      }


  })


    })