
document.addEventListener('DOMContentLoaded',()=>{
	new Vue({
		el:'#AdminDashboard',
		data:{
			formSuccess:false,
			defaultRoles:[],
			//file upload
			showHighlight:false,
			finalImages:[],
			index:0,
			email: '',
			google_font:'',
			addFieldForm:false,
			fields:true,
			commonFieldsShow:true,
			editingField:'',
			editingFieldID:null,
			editingFieldShow:false,
			commonFields: [
						{
							form: 'singleCheckbox',
							label: 'Single Checkbox',
							checked:false,
							required:false,
						},
						//
						{
							form: 'checkboxList',
							label: 'Check Box List',
							required:false,
							name:'name',
							class:'classname',
							options:[
								{ 
									label:'One',
									checked:false,  
									id:'idname',
								},
								{
									label:'Two',
									checked:false,
									id:'idname2',
								}
							]
						},
						{
							form: 'multiSelect',
							label:'Multi Select',
							required:false,
							options:[{label:'One',checked:false},{label:'Two',checked:false}]
						},
						{
							form:'radioList',
							label:'Radio List',
							required:false,
							name:'name',
							class:'classname',
							options:[
								{ 
									label:'One',
									checked:false,  
									id:'idname',
								},
								{
									label:'Two',
									checked:false,
									id:'idname2',
								}
							]
						},
						{
							form:'select',
							label:'Select ',
							required:false,
							options:[{label:'One',checked:false}]
						},
						{
							form:'paragraphText',
							label:'Paragraph Text',
							required:false,
							default:' Defualt paragraph text Default',
						},
						{
							form:'singleLineText',
							label:'Single Line Text',
							required:false,
							default:'Single Line Text Default',
						},
						{
							form:'fileUpload',
							label:'File Upload',
							required:false,
						},

				],
			array2: [
				
			],
		  
		},
		mounted(){
			fetch(woocommerceTickets.root_url + '/wp-json/namespace/v1/route')
				.then(response => response.json())
				.then(data => {
				 
				  let datatest = JSON.parse(data)
				  console.log(datatest)
						this.array2 = datatest
				   
				});
		},
		methods:{
			handleAdminChanges(e){
				e.preventDefault()
				
				const selected = document.querySelectorAll('#defaultRoles option:checked');
				const values = Array.from(selected).map(el => el.value);


				let formData = {
					email:this.$refs.email.value,
					google_font:this.$refs.google_font.value,
					defaultRoles:values,
					onlineStatus:this.$refs.onlineStatus.value
				}

			


				fetch( woocommerceTickets.root_url + '/wp-json/adminForm/v1/add_settings_route', {
                    method: 'POST', // or 'PUT'
                    headers: {
                      'Content-Type': 'application/json',
                      'X-WP-Nonce' :  woocommerceTickets.nonce 
                    },
                    body: JSON.stringify(formData),
                  })
                  .then(response => response.json())
                  .then(data => {
                    console.log('Success:', data);
					this.formSuccess = true
                  })
                  .catch((error) => {
                    console.error('Error:', error);
                  });


			}, 
			handleShowFields(){
				// this.fields = !this.fields
				this.commonFieldsShow = true
			},
			log(e){
				this.addFieldForm = true
			},
			deleteField(index){
	
				this.array2.splice(index,1)
		
			},
			editField(index){
				console.log(this.array2[index])
			  
				this.commonFieldsShow = false
				this.editingFieldShow = true
				this.editingFieldID = index
				let target = this.array2[index]
				console.log(target.form)
				this.editingField =  target.form
			},
			save(form){
				// console.log(this.$refs.textLabel.value)
		   
				this.array2.splice(form,1,{
					form:'singleLineText',
					label:this.$refs.textLabel.value,
					required:false,
					default:this.$refs.textDefault.value,
					actualVal:true
				})
	
				console.log(this.array2,form)
			},
			globalSave(e){
				// const data = { username: 'example' };
				e.preventDefault();
				let form = []
				this.array2.forEach(element => {
					if(element.hide !==true){
					   
						form.push(element)
					}
				});
	
				const data = { json_data: form};
				
			
				// if(form.length >=1){
					fetch(woocommerceTickets.root_url + '/wp-json/formBuilder/v1/add_form_route', {
						method: 'POST', // or 'PUT'
						headers: {
							'Content-Type': 'application/json',
							'X-WP-Nonce' :woocommerceTickets.nonce
						},
						body: JSON.stringify(data),
						})
						.then(response => response.json())
						.then(data => {
							e.preventDefault();
						console.log('Success:', data);
						})
						.catch((error) => {
						console.error('Error:', error);
						});
				// }else{
				// 	alert('You cant have an empty form')
				// }
		   
			},
			addMoreSelect(item){
			 
				// console.log(this.array2[item].options)
				this.array2[item].options.push({
					checked: false,
					label: "New"
				})
			},
			dragging(){
				console.log('dragging')
				this.editingFieldShow = false
				this.commonFieldsShow = true
			},
			duplicateField(index){
				console.log(this.array2[index])
	
				if(this.array2[index].form == "singleLineText" || this.array2[index].form == "paragraphText"||this.array2[index].form == "singleCheckbox"){
					this.array2.splice(index,0,{
						default:this.array2[index].default,
						form: this.array2[index].form,
						label: this.array2[index].label,
						required: this.array2[index].required,
					})
				}
	
				if(this.array2[index].form == "multiSelect" || this.array2[index].form == "radioList" || this.array2[index].form == "checkboxList" || this.array2[index].form == "select"){
					optionArray = []
					this.array2[index].options.forEach((val)=>{
					   
						optionArray.push({
							label:val.label,
							checked:val.checked,
						})
					})
			  
					this.array2.splice(index,0,{
						label:this.array2[index].label,
						default:this.array2[index].default,
						form: this.array2[index].form,
						options: optionArray,
						required: this.array2[index].required,
					})
				}
	
	
			   
				console.log(this.array2)
			},
			deleteOption(indexOption,indexValue){
			   this.array2[indexValue].options.splice(indexOption,1)
			},
			toggleDefault(checked){
		   
			  this.array2[checked].checked = !this.array2[checked].checked
	
		  
		
			},
			toggleDefaultDropDown(indexOption,indexValue){
				this.array2[indexValue].options[indexOption].checked = ! this.array2[indexValue].options[indexOption].checked   
				console.log(this.array2)
			},
			//file upload
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
		
		   
	

		}
	  })
	  

})