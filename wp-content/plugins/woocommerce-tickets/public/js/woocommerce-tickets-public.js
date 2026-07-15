document.addEventListener('DOMContentLoaded',()=>{
  Vue.use( CKEditor );
  new Vue({
    el: '#app_conversation',
    data:{
          datas:[],
          editable:false,
          heightPxOdd:[],
          heightPxEven:[],
          reachLimit:false,
          questions:[],
          firstMount:false,
          playQuestionAudio:false,
          showHighlight:false,
          finalImages:[],
          index:0,
          editor: ClassicEditor,
          editorData: '',
          editorConfig: {
              // The configuration of the editor.
          },
          finalSize:0,
          onlineStatus:false,
 
  },
  watch:{
  
      questions:{
        immediate: false,
          handler(newVal, oldVal) {
         
            // questions
            let mapquestionnew = newVal.map((data)=>{
                return data.question
            })
            
    
            let mapquestionold = oldVal.map((data)=>{
              return data.question
            })
            

            if(mapquestionold.length == 0){
                // console.log('welcome')
            }else{
              if( mapquestionold.length< mapquestionnew.length){
                console.log('user')
                try {

                  // document.querySelector('#myAudioQuestion').play()
                } catch (error) {
                  
                }
           
              }
            }

          // answers
              let mapanswernnew = newVal.map((data)=>{
                return data.answer
            })
            // console.log(mapanswernnew)
    
            let mapanswernold = oldVal.map((data)=>{
              return data.answer
            })
            

            if(mapanswernold.length == 0){
                // console.log('welcome')
            }else{
              if( mapanswernold.length< mapanswernnew.length && document.querySelector('#current-user-id').innerText == 1){

                console.log('admin')
                try {
          
                  // document.querySelector('#myAudioAnswer').play() 
                } catch (error) {
                  
                }
                  console.log( mapanswernold.length, mapanswernnew.length)
              }
            }
           
          },
      }
       
       
    

    },
  mounted(){
      
     this.refresh =  setInterval(() => {this.renderData()}, 1000);
    this.renderData()
    // setTimeout(() => {
    //   document.querySelector('.conversation_box').scrollTo(0,document.querySelector('.conversation_box').scrollHeight)
    //   document.querySelector('.questionbox').click
    // }, 4000);
  
    this.firstMount = true
    //  this.renderData()

  //     if(this.editable == true){
  //         alert()
  //     }
setInterval(()=>{ this.setOnlineStatus()},2000)
  
  },
  filters:{
    reverse(val){
      return val.split('').reverse.join('')
    }
  },
  created(){
     
      setTimeout(() => {
          document.querySelector('#app_conversation').style.opacity = '1'
          document.querySelector('.spinner-loader').style.display = 'none'
      }, 3000);

   
      
  },
  methods:{

    setOnlineStatus(){
      fetch(woocommerceTickets.root_url + '/wp-json/onlineStatus/v1/change_status')
        .then(response => response.json())
        .then(data => {

            if(data.online_status == 'no'){
                this.onlineStatus = false
            }else{
              this.onlineStatus = true
            } 
        })

    },
    playSound (sound) {
      if(sound) {
        var audio = new Audio(sound);
        audio.play();
      }
    },
      findNearestParentLi(el){
          let thisNote = el
          while (thisNote.tagName != "LI") {
            thisNote = thisNote.parentElement
          }
          return thisNote
        },
      renderData(){
        fetch(woocommerceTickets.root_url + '/wp-json/form/v1/submitQuestion?term=' +document.querySelector('#post_ticket_id').innerText)
        .then(response => response.json())
        .then(data => {
          // console.log(data)
        
          const convertData = (data) => {
              let newData = [];
              data.forEach((element, index) => {
                // console.log(element)
                  let title = element.title;
                  let id = element.id;
              
            
                  element.question.map((eachQuestion, questionIndex) => {
                    // console.log(questionIndex)
                      newData.push({
                          id,
                          title,
                          question_file:JSON.parse(JSON.parse(element.question_file)[questionIndex]),
                          question: eachQuestion,
                          time: element.question_time[questionIndex],
                      });
                  
                  });
                  element.answer.map((eachAnswer,answerIndex)=>{
                    if(element.answer[answerIndex]){
                      newData.push({
                          id,
                          title,
                          answer_file: JSON.parse(JSON.parse(element.answer_file)[answerIndex]),
                          answer: eachAnswer,
                          time: element.answer_time[answerIndex]
                      });
                  };
                  })
              
              });
              return newData;
            }
            
              // 
              const convertedData = convertData(data.ticket);
              // console.log(convertedData)
           
          
              const sortedTickets = convertedData.sort(function(time1,time2){
        
                  if(new Date(parseInt(time1.time) *1000 ) >new Date(parseInt(time2.time) *1000 ) ){
                       return 1
                     }else{
                       return -1
                     }
                })
               const filterUser =  sortedTickets.filter((id)=>{
                    
                    return id.id == document.querySelector('#post_ticket_id').innerText
                })

               
       
              
                this.questions = filterUser
          
                 this.datas = filterUser
          
          
          }).then(data=>{
          
           
        });
      },
      editTicket(e){
         
          let thisNote = this.findNearestParentLi(e.target)
      

          
          if (thisNote.getAttribute("data-state") == "editable") {
              thisNote.setAttribute("data-state", "cancel")
              thisNote.querySelector(".questionTicket").setAttribute("readonly", "true")
              thisNote.querySelector('.edit').innerHTML = 'Edit'
              thisNote.querySelector('.questionTicket').classList.remove('lytrod-active-field')
              // thisNote.querySelector('.input.questionTicket').classList.remove('lytrod-active-field ')
            
             
         } else {
              thisNote.setAttribute("data-state", "editable")
              thisNote.querySelector('.questionTicket').removeAttribute('readonly')
              thisNote.querySelector('.edit').innerHTML = 'Cancel'
              thisNote.querySelector('.questionTicket').classList.add('lytrod-active-field')
              // if(this.refresh ==2){
              //     clearInterval(this.refresh)
              // }
              
         }
      },
      saveTicket(e){

          // if(this.refresh !== 2){
          //     setInterval(() => {this.renderData()}, 1000);
          // }

          let thisNote = this.findNearestParentLi(e.target)

          //old value 
          let oldValTicket = thisNote.querySelector('.questionTicketOldVal').value;
          //new value
          let newValTicket = thisNote.querySelector('.questionTicket').value
          //post id
          let postId= document.querySelector("#post_ticket_id").innerText;
          let keyVal
          if(thisNote.getAttribute("data-from") =='answer'){
              keyVal = 'answer'
          }
          if(thisNote.getAttribute("data-from") =='question'){
              keyVal = 'question'
          }

          let saveTicketData ={
              'oldValue' : oldValTicket,
              'newValue': newValTicket,
              'postId' :postId,
              'keyVal':keyVal
          }
       

          fetch( woocommerceTickets.root_url + '/wp-json/form/v1/updateQuestion', {
              method: 'POST', // or 'PUT'
              headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce' :  woocommerceTickets.nonce 
              },
              body: JSON.stringify(saveTicketData),
            })
            .then(response => response.json())
            .then(data => {
              console.log('Success:', data);
           
            })
            .catch((error) => {
              console.error('Error:', error);
            });

      },
      deleteTicket(e){
        
          let thisNote = this.findNearestParentLi(e.target)
          let deleteTicketVal = thisNote.querySelector('.questionTicketOldVal').value;
      
          let postId= document.querySelector("#post_ticket_id").innerText;
          let keyVal
          if(thisNote.getAttribute("data-from") =='answer'){
              keyVal = 'answer'
          }
          if(thisNote.getAttribute("data-from") =='question'){
              keyVal = 'question'
          }
          let deleteTicketData ={
              'metaVal': deleteTicketVal,
              'postId' :postId,
              'metaKey':keyVal 
          }
          fetch( woocommerceTickets.root_url + '/wp-json/form/v1/deleteQuestion', {
              method: 'POST', // or 'PUT'
              headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce' :  woocommerceTickets.nonce 
              },
              body: JSON.stringify(deleteTicketData),
            })
            .then(response => response.json())
            .then(data => {
              console.log('Success:', data);
            })
            .catch((error) => {
              console.error('Error:', error);
            });
      },
      closeTicket(e){
        let postId= document.querySelector("#post_ticket_id").innerText;
            let closeTicketData = {
              'postId' :postId,
          }

          fetch( woocommerceTickets.root_url + '/wp-json/form/v1/closeQuestion', {
              method: 'POST', // or 'PUT'
              headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce' :  woocommerceTickets.nonce 
              },
              body: JSON.stringify(closeTicketData),
            })
            .then(response => response.json())
            .then(data => {
              console.log('Success:', data);
            })
            .catch((error) => {
              console.error('Error:', error);
            })
        
        
      },
      createTicket(e){
          // if(this.refresh !== 2){
          //     setInterval(() => {this.renderData()}, 1000);
          // }
              document.querySelector('.message-box-spinner').style.display = 'block'
        

              let postId= document.querySelector("#post_ticket_id").innerText;
              let messageBox = e.target.parentElement.children[1].value
              console.log(postId,messageBox)
                 
                if(this.editorData.length <= 5){
                  alert('Sorry, your message is to short. Must be a minimum of 5 charcters.')
                  document.querySelector('.message-box-spinner').style.display = 'none'
                }else if(/^\s+$/.test(this.editorData)){
                  alert("Your message cant just have white space")
                  document.querySelector('.message-box-spinner').style.display = 'none'
                }else{
                 
              let metaKeyQuestionAnswer 
              if(e.target.parentElement.getAttribute("data-from") =='answer'){
                metaKeyQuestionAnswer = 'answer'
              }
              if(e.target.parentElement.getAttribute("data-from") =='question'){
                metaKeyQuestionAnswer = 'question'
              }


              let timeStampKeyQuestionAnswer
              if(e.target.parentElement.getAttribute("data-time") =='questiontime'){
                timeStampKeyQuestionAnswer = 'question_time'
             }
              if(e.target.parentElement.getAttribute("data-time") =='answertime'){
                timeStampKeyQuestionAnswer = 'answer_time'
              }

              let fileQuestionAnswer

              if(e.target.parentElement.getAttribute("data-file") =='filequestion'){
                fileQuestionAnswer = 'question_file'
             }
              if(e.target.parentElement.getAttribute("data-file") =='fileanswer'){
                fileQuestionAnswer = 'answer_file'
              }


              //file
              // console.log(metaKey)



          
              let createTicketData = {
                  'postId' :postId,
                  'metaValQuestionAnswer':this.editorData,
                  'metaKeyQuestionAnswer':metaKeyQuestionAnswer,
                  'timeStampKeyQuestionAnswer':timeStampKeyQuestionAnswer,
                  'fileValQuestionAnswer':this.finalImages,
                  'fileKeyQuestionAnswer':fileQuestionAnswer
              }

              let connection = ''

              
          // let render = this.renderData;
          
              fetch( woocommerceTickets.root_url + '/wp-json/form/v1/addQuestion', {
                  method: 'POST', // or 'PUT'
                  headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce' :  woocommerceTickets.nonce 
                  },
                  body: JSON.stringify(createTicketData),
                })
                .then(response => response.json())
                .then(data => {
                  console.log('Success:', data);
                  this.finalImages = []
                  document.querySelector('.message-box-spinner').style.display = 'none'
                })
                .then(data => {
                  // console.log('Success Second:', connection);
                 
                })
                .catch((error) => {
                  console.error('Error:', error);
                  document.querySelector('.message-box-spinner').style.display = 'none'
                })


              }

                

        

      },
      stopEmailTicket(e){

        document.querySelector('.message-box-spinner').style.display = "block"
        let postId= document.querySelector("#post_ticket_id").innerText;
        let stopEmailData = {
          'postId' :postId,
      }
     
      const checkbox = e.target;  
      const checked = checkbox.checked; 
 
      if(checked == true){
        fetch( woocommerceTickets.root_url + '/wp-json/form/v1/stopEmail', {
            method: 'POST', // or 'PUT'
            headers: {
              'Content-Type': 'application/json',
              'X-WP-Nonce' :  woocommerceTickets.nonce 
            },
            body: JSON.stringify(stopEmailData),
          })
          .then(response => response.json())
          .then(data => {
            console.log(data)
            document.querySelector('.message-box-spinner').style.display = "none"
          })
          .catch((error) => {
            console.error('Error:', error);
            document.querySelector('.message-box-spinner').style.display = "none"
          })
      }else{
          fetch( woocommerceTickets.root_url + '/wp-json/form/v1/restartEmail', {
            method: 'POST', // or 'PUT'
            headers: {
              'Content-Type': 'application/json',
              'X-WP-Nonce' :  woocommerceTickets.nonce 
            },
            body: JSON.stringify(stopEmailData),
          })
          .then(response => response.json())
          .then(data => {
            console.log(data)
            document.querySelector('.message-box-spinner').style.display = "none"
          })
          .catch((error) => {
            console.error('Error:', error);
            document.querySelector('.message-box-spinner').style.display = "none"
          })
      }
         
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


  }
  });




   

	
})

   
