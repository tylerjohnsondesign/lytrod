
<div id="AdminDashboard">
<div class="container-fluid">

<div class="d-flex justify-content-center">
  <button style="font-size: 25px;" class="btn btn-primary mt-3" @click = "globalSave($event)" >Save</button>
</div>
 
    <div class="row">
        <div class="col-6">
            <div class="group2">
                <h2>Drag and Drop Form</h2>
                <div v-for = "val in array2" class="">
                  <div class="" v-if="val.hide !== true">
                    <!-- {{val}} -->
                  </div>
                  
                </div>
            
                <draggable @change="log($event)" class="dragArea" v-model="array2" :options="{group: 'people'}">
                 
                    <!-- <div class="add-box"v-if="array2.length == 8"  >
           
                      
                    </div> -->
                  
            <div class="people" v-for="(el,index) in array2">
                  <!-- single line text -->
                <div @drop  ="dragging" v-if="el.form == 'singleLineText' "   class="mb-3 " >
                  <div class="hover-box">
                    <div class="flex">
                      <button class="btn btn-danger" @click="deleteField(index)"> <small>Delete</small></button>
                      <button class="btn btn-primary" @click="editField(index)"> <small>Edit</small></button>
                      <button class="btn btn-secondary" @click="duplicateField(index)"> <small>Duplicate</small></button>
                  </div>
                  <label for="exampleInputEmail1" class="form-label">{{el.label}}</label>
                  <input type="email" class="form-control" :value="el.default" id="exampleInputEmail1" aria-describedby="emailHelp">
                  </div>
      
                </div>
                <!-- multi select -->
                  <div   @drop ="dragging" v-if="el.form == 'multiSelect' " class="mb-3 ">
                        <div class="hover-box">
                          <div class="flex">
                            <button class="btn btn-danger" @click="deleteField(index)"> <small>Delete</small></button>
                            <button class="btn btn-primary" @click="editField(index)"> <small>Edit</small></button>
                            <button class="btn btn-secondary" @click="duplicateField(index)"> <small>Duplicate</small></button>
                        </div>
                          <label for="form-select" class="form-label">{{el.label}}</label>
                   
                          <select  multiple id="form-select" class="form-select" aria-label="Default select example">
                            
                            <option  :selected="array2[index].options[indexs].checked"v-for ="(test,indexs) in el.options" >
                              {{test.label}}
                            
                            </option>
                        
                          </select>
                        </div>                     
                  </div>
                         <!-- single checkbox -->
                  <div   @drop ="dragging" v-if="el.form == 'singleCheckbox' " class="mb-3 ">
                    <div class="hover-box">
                      <div class="flex">
                        <button class="btn btn-danger" @click="deleteField(index)"> <small>Delete</small></button>
                        <button class="btn btn-primary" @click="editField(index)"> <small>Edit</small></button>
                        <button class="btn btn-secondary" @click="duplicateField(index)"> <small>Duplicate</small></button>
                    </div>
                      <label for="form-select" class="form-label">{{el.label}}</label>
                    
                      <input  :checked="array2[index].checked"type="checkbox" id="vehicle1" name="vehicle1" >
                    </div>                     
              </div>

            <!-- radio list -->
            <div   @drop ="dragging" v-if="el.form == 'radioList' " class="mb-3 ">
              <div class="hover-box">
                <div class="flex">
                  <button class="btn btn-danger" @click="deleteField(index)"> <small>Delete</small></button>
                  <button class="btn btn-primary" @click="editField(index)"> <small>Edit</small></button>
                  <button class="btn btn-secondary" @click="duplicateField(index)"> <small>Duplicate</small></button>
              </div>
                <label for=""> {{el.label}}</label>
                <div class="form-check" v-for="option in el.options">

                  <input :class="el.class" type="radio" :name="el.name" :id="option.id">
                  <label :class="el.class" for="flexRadioDefault1">
                    {{option.label}}
                  </label>
                </div>
               
              </div>                     
           </div>
                   <!-- checkbox list -->
          <div @drop ="dragging" v-if="el.form == 'checkboxList' " class="mb-3 ">
                    <div class="hover-box">
                      <div class="flex">
                        <button class="btn btn-danger" @click="deleteField(index)"> <small>Delete</small></button>
                        <button class="btn btn-primary" @click="editField(index)"> <small>Edit</small></button>
                        <button class="btn btn-secondary" @click="duplicateField(index)"> <small>Duplicate</small></button>
                    </div>
                      <label for=""> {{el.label}}</label>
                      <div class="form-check" v-for="(option,index) in el.options">
  
                        <input :class="el.class"  :checked="option.checked" type="checkbox" :name="el.name" :id="option.id">
                        <label :class="el.class" for="flexRadioDefault1">
                          {{option.label}}
                        </label>
                      </div>
                     
                    </div>                     
              </div>
          <!-- Select -->
          <div @drop ="dragging" v-if="el.form == 'select' " class="mb-3 ">
              <div class="hover-box">
                <div class="flex">
                  <button class="btn btn-danger" @click="deleteField(index)"> <small>Delete</small></button>
                  <button class="btn btn-primary" @click="editField(index)"> <small>Edit</small></button>
                  <button class="btn btn-secondary" @click="duplicateField(index)"> <small>Duplicate</small></button>
              </div>
                <label for=""> {{el.label}}</label>
                <select id="form-select" class="form-select" aria-label="Default select example">
                              
                  <option v-for ="option in el.options" >{{option.label}}</option>
              
                </select>
              </div>                     
        </div>
                  <!-- single line text -->
            <div @drop  ="dragging" v-if="el.form == 'paragraphText' "   class="mb-3 " >
                    <div class="hover-box">
                      <div class="flex">
                        <button class="btn btn-danger" @click="deleteField(index)"> <small>Delete</small></button>
                        <button class="btn btn-primary" @click="editField(index)"> <small>Edit</small></button>
                        <button class="btn btn-secondary" @click="duplicateField(index)"> <small>Duplicate</small></button>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">{{el.label}}</label>
                    <textarea type="email" class="form-control" :value="el.default" id="exampleInputEmail1" aria-describedby="emailHelp">
                    </textarea>
                    </div>
            </div>

            <div @drop  ="dragging" v-if="el.form == 'submit' "   class="mb-3 " >
                  <div class="hover-box">
                    <div class="flex">
                   
                      <button class="btn btn-primary" @click="editField(index)"> <small>Edit</small></button>
                      
                  </div>
               
                    <button>{{el.label}}</button>
                  </div>
           </div>

           <div @drop  ="dragging" v-if="el.form == 'fileUpload' "   class="mb-3 " >
                  <div class="hover-box">
                  <div class="flex">
                        <button class="btn btn-danger" @click="deleteField(index)"> <small>Delete</small></button>
                  
                    </div>
                  <div 
                  @dragenter="enterHighLight($event)"  
                  @dragover="enterHighLight($event)"id="drop-area" 
                  :class="{highlight:showHighlight}" 
                  @dragleave = "leaveHighLight($event)" 
                    @drop = "leaveHighLight($event),handleDrop($event)" >
                      <p>Drop files here.</p>
                      <input type="file" id="fileElem" multiple  @change="handleChange($event)" >
                      <label class="button" for="fileElem">Select some files</label>
                  
                      <div id="gallery" v-for="images in finalImages">
                        
                              <div style="cursor: pointer;" @click="removeFile(images)">X</div>
                              <div v-if="images.fileType == 'image/png' ">
                                <img :src="images.src" alt="">
                              </div>
                              <div v-else-if="images.fileType == 'application/pdf' " >
                                <a :href = "images.src"  :download="images.fileName" >{{images.fileName}}</a>
                              </div>
                              <div v-else-if = "images.fileType == 'video/mp4' ">
                                <video width="320" height="240" controls>
                                  <source :src="images.src" type="video/mp4">
                                  Your browser does not support the video tag.
                                </video>   
                              </div>
                              <div v-else-if="images.fileType == 'image/jpeg' " >
                                <img :src="images.src" alt="">
                              </div>
                              <div v-else-if = "images.fileType == 'audio/mpeg' " >
                            
                                <audio controls>
                                  <source :src="images.src" type="audio/mpeg">
                                Your browser does not support the audio element.
                                </audio>
                              </div> 
                        </div>
                  </div>
                  </div>
           </div>


            </div>
                 
              
               
                 
                </draggable>
             
          
              </div>
           
        </div>
        <div class="col-6">
          <!-- <div class="d-flex justify-content-center">
            <i @click = "handleShowFields"  v-if="fields == false" class="fas fa-plus big-plus"></i>
          </div> -->
         
            <div class="group1" v-if="fields == true">
           
             <div class="" v-if="commonFieldsShow == true">
              <h2>COMMON FIELDS</h2>
              
              <draggable class="dragArea row" v-model="commonFields" :options="{group: {name:'people', pull: 'clone', put: false}}">
                <div class="col-md-4" v-for="el in commonFields">
                  <div class="drag-box">
                    {{el.label}}
                  </div>
              
                </div>
              </draggable>
             </div>


            <!-- edit single line text -->
            <div class=""v-if="editingFieldShow == true && commonFieldsShow == false && 
            editingField == 'paragraphText'
            ">
                    <div class="d-flex justify-content-center">
                      <button class="done-button" @click = "handleShowFields" >Add Field</button>
                    </div>
                        <label for="">Label</label>
                      <input type="text" ref="textLabel" v-model="array2[editingFieldID].label">
                      <label for=""> Defualt Value</label>
                      <input type="text"   v-model="array2[editingFieldID].default">
                      
            </div>
             <!-- edit single line text -->
              <div class=""v-if="editingFieldShow == true && commonFieldsShow == false && 
              editingField == 'singleLineText'
              ">
                      <div class="d-flex justify-content-center">
                        <button class="done-button" @click = "handleShowFields" >Add Field</button>
                      </div>
                          <label for="">Label</label>
                        <input type="text" ref="textLabel" v-model="array2[editingFieldID].label">
                        <label for=""> Defualt Value</label>
                        <input type="text"   ref="textDefault" v-model="array2[editingFieldID].default">
                       
              </div>
                 <!-- edit multi-select -->
               <div v-if="editingFieldShow == true && commonFieldsShow == false && editingField == 'multiSelect' ">
                
                      <div class="d-flex justify-content-center">
                        <button class="done-button" @click = "handleShowFields" >Add Field</button>
                      </div>
                      <label for="">Label</label>
                      <input type="text" ref="textLabel" :value="array2[editingFieldID].label">
                 
                      <button class="btn btn-secondary" @click = "addMoreSelect(editingFieldID)" >Add More</button>
                        <draggable class="dragArea" v-model="array2[editingFieldID].options"  :options="{group: 'people'}">
                      <div class="label-field" v-for="(el,index) in array2[editingFieldID].options">
                          <input type="text" v-model="el.label"> 
                            <button @click="deleteOption(index,editingFieldID)">Remove</button>
                            <label class="toggle">
                              <span class="toggle-label">Checked:</span>
                           
                              <input  :checked="array2[editingFieldID].options[index].checked" @click = "toggleDefaultDropDown(index,editingFieldID)" class="toggle-checkbox" type="checkbox" >
                              <div class="toggle-switch"></div>
                            </label>
                      </div>
                        </draggable>
                </div>
                <!-- single checkout -->
                <div v-if="editingFieldShow == true && commonFieldsShow == false && editingField == 'singleCheckbox' ">
                  <div class="d-flex justify-content-center">
                    <button class="done-button" @click = "handleShowFields" >Add Field</button>
                  </div>
                
                    <label for="">Label</label>
                    <input type="text"  v-model="array2[editingFieldID].label">
                      <label class="toggle">
                        <span class="toggle-label">Checked by default</span>
                     
                        <input  :checked="array2[editingFieldID].checked"  @click = "toggleDefault(editingFieldID)" class="toggle-checkbox" type="checkbox" >
                        <div class="toggle-switch"></div>
                      </label>
                    
                </div>
                <!-- edit radio list -->
                <div v-if="editingFieldShow == true && commonFieldsShow == false && editingField == 'radioList' ">
                  <button @click = "addMoreSelect(editingFieldID)" >Add More</button>
                  <div class="d-flex justify-content-center">
                    <button class="done-button" @click = "handleShowFields" >Add Field</button>
                  </div>
                  <label for="">Label</label>
                  <input type="text" ref="textLabel" v-model="array2[editingFieldID].label">
             
                    <draggable class="dragArea" v-model="array2[editingFieldID].options"  :options="{group: 'people'}">
                      <div class="label-field" v-for="(el,index) in array2[editingFieldID].options">
                      <input type="text" v-model="el.label"> 
                        <button @click="deleteOption(index,editingFieldID)">Remove</button>
                      </div>
                    </draggable>
                
                    
                </div>

                <!-- edit checkbox list -->
                <div v-if="editingFieldShow == true && commonFieldsShow == false && editingField == 'checkboxList' ">
                     
                      <div class="d-flex justify-content-center">
                        <button class="done-button" @click = "handleShowFields" >Add Field</button>
                      </div>
                      <button class="btn btn-primary" @click = "addMoreSelect(editingFieldID)" >Add More</button>
                      <label for="">Label</label>
                      <input type="text" ref="textLabel" v-model="array2[editingFieldID].label">
                 
                        <draggable class="dragArea" v-model="array2[editingFieldID].options"  :options="{group: 'people'}">
                          <div class="label-field" v-for="(el,index) in array2[editingFieldID].options">
                          <input type="text" v-model="el.label"> 
                            <button @click="deleteOption(index,editingFieldID)">Remove</button>
                            <label class="toggle">
                              <span class="toggle-label">Checked:</span>
                           
                              <input  :checked="array2[editingFieldID].options[index].checked" @click = "toggleDefaultDropDown(index,editingFieldID)" class="toggle-checkbox" type="checkbox" >
                              <div class="toggle-switch"></div>
                            </label>
                          </div>
                        </draggable>
                </div>
                <!-- edit select -->
                <div v-if="editingFieldShow == true && commonFieldsShow == false && editingField == 'select' ">
                               
                                <div class="d-flex justify-content-center">
                                  <button class="done-button btn btn-primary mb-4" @click = "handleShowFields" >Add Field

                                  </button>
                                </div>
                                <button class="btn btn-secondary" @click = "addMoreSelect(editingFieldID)" >Add More</button>
                                <label for="">Label</label>
                                <input type="text" ref="textLabel" v-model="array2[editingFieldID].label">
                          
                                  <draggable class="dragArea" v-model="array2[editingFieldID].options"  :options="{group: 'people'}">
                                    <div class="label-field" v-for="(el,index) in array2[editingFieldID].options">
                                    <input type="text" v-model="el.label"> 
                                      <button @click="deleteOption(index,editingFieldID)">Remove</button>
                                    </div>
                                  </draggable>
                  </div>
                        <!-- edit select -->
                <div v-if="editingFieldShow == true && commonFieldsShow == false && editingField == 'submit' ">
                  <label for="">Button Text</label>
                  <input type="text" ref="textLabel" v-model="array2[editingFieldID].label">
                  </div>
                        
                         
            </div>
        </div>
    </div>
  
</div>

</div>