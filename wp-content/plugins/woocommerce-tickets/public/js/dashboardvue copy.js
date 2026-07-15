document.addEventListener('DOMContentLoaded',()=>{

  Vue.use( CKEditor );
  new Vue({
    el:"#accordian1",
    data:{
      show: false,
      openOrClose:'Open',
      showHighlight:false,
      finalImages:[],
      index:0,
      editor: ClassicEditor,
      editorData: '',
      editorConfig: {
          // The configuration of the editor.
      }
    },
    mounted(){
			  setTimeout(() => {
				document.querySelector('#accordian2').style.display = 'block'
				document.querySelector('#accordian1').style.display = 'block'
				document.querySelector('.spinner-loader').style.display = 'none'
			  }, 2000);
    },
    methods:{

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
   
        file = [...files]
        file.forEach(this.previewFile);
       
    },
    removeFile(file){
     index = this.finalImages.indexOf(file)
     console.log(index)
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
      
          if(this.show == true){
            this.openOrClose = 'Close'
        }
          
      },
      leave(el, done) {
        console.log(this.$refs.chevron)
       let tl = gsap.timeline()
        tl.fromTo(el.children[0],0.5,{opacity:'1'},{opacity:'0'})
          .to(this.$refs.chevron,{rotate:'0deg'})
          .fromTo(el,0.5,{height:el.offsetHeight + "px"},{height: 0 ,delay:0.5})
      
          if(this.show == false){
            this.openOrClose = 'Open'
        }
            
      },
     async submitTicket(e){
        e.preventDefault()
        // document.querySelector('.spinner-loader').style.display = 'block';
        // if(document.querySelector("#lytrod-name").value ==''){
        //   document.querySelector("#lytrod-name").style.border = '3px solid red'
        //     alert("Name must be filled out");
        //     document.querySelector('.spinner-loader').style.display = 'none';
        //     return false;
        // }else if(document.querySelector("#lytrod-email").value ==''){
        //   document.querySelector("#lytrod-email").style.border = '3px solid red'
        //     alert("Email must be filled out");
        //     document.querySelector('.spinner-loader').style.display = 'none';
        //     return false;
        // }else if(document.querySelector("#lytrod-reason-option").value ==''){
        //    document.querySelector("#lytrod-reason-option").style.border = '3px solid red'
        //     alert("Reason for message must be filled out");
        //     document.querySelector('.spinner-loader').style.display = 'none';
        //     return false;
        // }else if(this.editorData ==''){
        //   document.querySelector("#lytrod-question").style.border = '3px solid red'
        //     alert("Your question must be filled out");

        //     document.querySelector('.spinner-loader').style.display = 'none';
        //     return false;
        // }else{

            let fd = new FormData();
            fd.append('action', 'cvf_upload_files'); 
            fd.append('name', document.querySelector("#lytrod-name").value); 
            fd.append('email', document.querySelector("#lytrod-email").value); 
            fd.append('reason', document.querySelector("#lytrod-reason-option").value); 
            fd.append('question', this.editorData); 
            
            for (let index = 0; index < 2; index++) {
              fd.append(`files[${index}]`, document.querySelector('#fileElem').files[index]);
            }

            axios({
              method: "post",
              url: 'http://localhost/lytrod/wp-admin/admin-ajax.php',
              data: fd,
              headers: { 
                "Content-Type": "multipart/form-data" 
              },
              })
              .then(function (response) {
                //handle success
                // location.href = woocommerceTickets.root_url +'/singleticket/'+response.data
              })
              .catch(function (response) {
                //handle error
                console.log(response);
              });
            }
          
                // let finalFiles = []
                // for (let i = 0; i < this.finalImages.length; i++) {
                //     finalFiles.push(this.finalImages[i].src)
                  
                // }

                // let formData ={
                //     "name":document.querySelector("#lytrod-name").value,
                //     "email":document.querySelector("#lytrod-email").value,
                //     "reason": document.querySelector("#lytrod-reason-option").value,
                //     "question":document.querySelector("#lytrod-question").value,
                //     "file":finalFiles
                //  }
                 
                // const emailMessage = await fetch(woocommerceTickets.root_url + '/wp-json/form/v1/submitQuestion',{
                //     method : 'POST',
                //     headers : {
                //        'Content-Type': 'application/json',
                //         'X-WP-Nonce' :  woocommerceTickets.nonce // here you used the wrong name
                //       },
                //       credentials: 'same-origin',
                //       body:JSON.stringify(formData)
                //  })
                //     let finalEmailMessage = await emailMessage.json()
                //     console.log(finalEmailMessage[1])

                //     if(finalEmailMessage !== undefined){
                //         console.log(formData)
                //         // document.querySelector('#congrats').style.display = 'block';
                        
                //         location.href = woocommerceTickets.root_url +'/singleticket/'+finalEmailMessage[1]
                //     }       
        }
    



  })


  new Vue({
    el:"#accordian2",
    data:{
      show: false,
      openOrClose:'Open',
      showHighlight:false,
      finalImages:[],
      index:0
    },
  	mounted(){
			  
			  setTimeout(() => {
				document.querySelector('#accordian2').style.display = 'block'
				document.querySelector('#accordian1').style.display = 'block'
				document.querySelector('.spinner-loader').style.display = 'none'
			  }, 2000);
		},
		methods: {
	  
		  enter(el, done) {
		  
			console.log(el )
		  let tl = gsap.timeline()
			tl.fromTo(el,0.5,{height:'0px'},{height:el.offsetHeight + 'px'})
			.to(this.$refs.chevron,{rotate:'180deg'})
				.fromTo(el.children[0],0.5,{opacity:'0'},{opacity:'1',onComplete: done})
	
				setTimeout(() => {
				  if(this.show == true){
					this.openOrClose = 'Close'
				}
				}, 500);
			
			  
		  },
		  leave(el, done) {
		
		   let tl = gsap.timeline()
			tl.fromTo(el.children[0],0.5,{opacity:'1'},{opacity:'0'})
			.to(this.$refs.chevron,{rotate:'0deg'})
			  .fromTo(el,0.5,{height:el.offsetHeight + "px"},{height: 0 ,delay:0.5,onComplete: done})
			
			  setTimeout(() => {
				if(this.show == false){
				  this.openOrClose = 'Open'
			  }
			  }, 1000);
			
				
		  }
		}
  })

    })