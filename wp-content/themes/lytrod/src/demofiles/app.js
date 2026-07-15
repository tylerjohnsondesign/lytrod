function showPage(pageId){
  $(".main-containers").hide();
  $(pageId).show();
}
console.log('hello')
$(window).on("hashchange",function(){
    
    showPage(location.hash);
    window.scrollTo(0,0);
})
$(window).on('load',function(){
 showPage(location.hash);
 window.scrollTo(0,0);
})
if(window.location.href === 'https://lytrod.com/demonstration-files/'){
    window.location.href = 'https://lytrod.com/demonstration-files/#home'
}


//this starts IAP_11x17 
// $("[data-toggle=popover]").popover();
// $('[data-toggle="tooltip"]').tooltip()
// ====================================================================================================================================================//
// ----------------------------------------------INTELLICUT PRIME 11x17--------------------------------------------------------------------------------
// ====================================================================================================================================================//
$.ajax({
  method:'GET',
  url: 'https://api.apispreadsheets.com/data/1008/?accessKey=4d7a2411cd96d7fbb53e2cc243311aa0&secretKey=4696868e1061aee437e875c16549abc8&dataFormat=column&blank=False',
 dataType: 'json'
}).done(function(data){
  console.log(data) 

  for(let i = 0;i<data.IAP_11x17_preset_text.length;i++){
    // ------------------------Prime------------------
  $('#outputPreset_IP_11X17').append(`
  <tr>
      <td>
        <a class = "hoverPrimePreset11x17Link${i}" href="${data.IAP_11x17_preset_pdf_download[i]}" target="_blank"><i class="fa fa-file-pdf"></i>
        ${data.IAP_11x17_preset_text[i]}</a>
        <br>
        <img  class ="hoverPrimePreset11x17Image${i} ${data.IAP_11x17_preset_thumbnail_size[i]} hide" src="${data.IAP_11x17_preset_thumbnail[i]}" >
        <i class=" archive-icon fas fa-file-archive"></i>
            <a class = "hoverPrimePresetArchive11x17Link${i}" href = "${data.IAP_11x17_preset_archive_link[i]}">
            ${data.IAP_11x17_preset_archive_text[i]}
            </a>
            <img src = "/showcase/Intellicut_Archive_PopUp.png"class = "hoverPrimePresetArchive11x17Image${i} hide size">
            
      </td>
  </tr>
    `)
  
//product images
for(let i = 0;i<10;i++){
      $('.hoverPrimePreset11x17Link'+i).hover(function(){
        $('.hoverPrimePreset11x17Image'+i).css('display','block')
      },function(){
        $('.hoverPrimePreset11x17Image'+i).css('display','none')
      })
    }
  
//Archive Images
for(let i = 0;i<10;i++){
  $('.hoverPrimePresetArchive11x17Link'+i).hover(function(){
    $('.hoverPrimePresetArchive11x17Image'+i).css('display','block')
  },function(){
    $('.hoverPrimePresetArchive11x17Image'+i).css('display','none')
  })
}

  }

//------------------------Manuset------------------
for(i = 0;i<data.IAP_11x17_manuset_text.length;i++){
$('#outputManuset_IP_11X17').append(`
  <tr>
      <td>
        <a class = "hoverPrimeManuset11x17Link${i}" href="${data.IAP_11x17_manuset_pdf_download[i]}" target="_blank"><i class="fa fa-file-pdf"></i>
        ${data.IAP_11x17_manuset_text[i]}</a>
        <img  class ="hoverPrimeManuset11x17Image${i} ${data.IAP_11x17_manuset_thumbnail_size[i]} hide" src="${data.IAP_11x17_manuset_thumbnail[i]}" >
     
        <br>
        <i class=" archive-icon fas fa-file-archive"></i>
            <a class = "hoverPrimeManusetArchiveLink${i}" href = "${data.IAP_11x17_manuset_archive_link[i]}">
            ${data.IAP_11x17_manuset_archive_text[i]}
            </a>
          <img src = "/showcase/Intellicut_Archive_PopUp.png"class = "hoverPrimeManusetArchiveImage${i} hide size">
      </td>
  </tr>
    `)

// ---------Manuset Image popovers
  for(let i = 0;i<10;i++){
          $('.hoverPrimeManuset11x17Link'+i).hover(function(){
            $('.hoverPrimeManuset11x17Image'+i).css('display','block')
          },function(){
            $('.hoverPrimeManuset11x17Image'+i).css('display','none')
          })
        }
      
//-------- Manuset Archive popovers-------
    for(let i = 0;i<10;i++){
      $('.hoverPrimeManusetArchiveLink'+i).hover(function(){
        $('.hoverPrimeManusetArchiveImage'+i).css('display','block')
      },function(){
        $('.hoverPrimeManusetArchiveImage'+i).css('display','none')
      })
    }
  }

// ---End of ajax
});
//---End of ajax

// ====================================================================================================================================================//
// ----------------------------------------------INTELLICUT VELOCITY 11x17--------------------------------------------------------------------------------
// ====================================================================================================================================================//
$.ajax({
method:'GET',
url: 'https://api.apispreadsheets.com/data/15447/?accessKey=2b17ba71ffff9c62d0faa9f519525b73&secretKey=0da0d58f7ad09258b3ea68ad4bfa87bd&dataFormat=column&blank=False',
// dataType: 'json'
}).done(function(data){

     // ------------------------Preset------------------
     for(let i = 0;i<data.IAV_11x17_preset_text.length;i++){
      // ------------------------Prime------------------
    $('#outputPreset_IV_11X17').append(`
    <tr>
        <td>
          <a class = "hoverVelocityPreset11x17Link${i}" href="${data.IAV_11x17_preset_pdf_download[i]}" target="_blank"><i class="fa fa-file-pdf"></i>
          ${data.IAV_11x17_preset_text[i]}</a>
          <br>
          <img  class ="hoverVelocityPreset11x17Image${i} ${data.IAV_11x17_preset_thumbnail_size[i]} hide" src="${data.IAV_11x17_preset_thumbnail[i]}" >
          <i class=" archive-icon fas fa-file-archive"></i>
              <a class = "hoverVelocityPresetArchive11x17Link${i}" href = "${data.IAV_11x17_preset_archive_link[i]}">
              ${data.IAV_11x17_preset_archive_text[i]}
              </a>
              <img src = "/showcase/Intellicut_Archive_PopUp.png"class = "hoverVelocityPresetArchive11x17Image${i} hide size">
              
        </td>
    </tr>
      `)
    
  //product images
  for(let i = 0;i<10;i++){
        $('.hoverVelocityPreset11x17Link'+i).hover(function(){
          $('.hoverVelocityPreset11x17Image'+i).css('display','block')
        },function(){
          $('.hoverVelocityPreset11x17Image'+i).css('display','none')
        })
      }
    
  //Archive Images
  for(let i = 0;i<10;i++){
    $('.hoverVelocityPresetArchive11x17Link'+i).hover(function(){
      $('.hoverVelocityPresetArchive11x17Image'+i).css('display','block')
    },function(){
      $('.hoverVelocityPresetArchive11x17Image'+i).css('display','none')
    })
  }
  
    }

// ------------------------Manuset------------------
for(let i = 0;i<data.IAV_11x17_manuset_text.length;i++){

  $('#outputManuset_IVM').append(`
    <tr>
        <td>
          <a class = "hoverVelocityManuset11x17Link${i}" href="${data.IAV_11x17_manuset_pdf_download[i]}" target="_blank"><i class="fa fa-file-pdf"></i>
          ${data.IAV_11x17_manuset_text[i]}</a>
          <br>
          <img  class ="hoverVelocityManuset11x17Image${i} ${data.IAV_11x17_manuset_thumbnail_size[i]} hide" src="${data.IAV_11x17_manuset_thumbnail[i]}" >
          <i class=" archive-icon fas fa-file-archive"></i>
              <a class = "hoverVelocityManusetArchiveLink${i}" href = "${data.IAV_11x17_manuset_archive_link[i]}">
              ${data.IAV_11x17_manuset_archive_text[i]}
              </a>
              <img src = "/showcase/Intellicut_Archive_PopUp.png"class = "hoverVelocityManusetArchiveImage${i} hide size">
              
        </td>
    </tr>
      `)
for(let i = 0;i<10;i++){
    $('.hoverVelocityManuset11x17Link'+i).hover(function(){
      $('.hoverVelocityManuset11x17Image'+i).css('display','block')
    },function(){
      $('.hoverVelocityManuset11x17Image'+i).css('display','none')
    })
  }

//archive
for(let i = 0;i<10;i++){
  $('.hoverVelocityManusetArchiveLink'+i).hover(function(){
    $('.hoverVelocityManusetArchiveImage'+i).css('display','block')
  },function(){
    $('.hoverVelocityManusetArchiveImage'+i).css('display','none')
  })
}
}
//----------this is the end sheet IAV_11x17
})
// ------------this is the end sheet IAV_11x17
// ====================================================================================================================================================//
// ----------------------------------------------INTELLICUT PRIME 12x18--------------------------------------------------------------------------------
// ====================================================================================================================================================//
$.ajax({
method:'GET',
url: 'https://api.apispreadsheets.com/data/1011/?accessKey=0dfc60e502a45de8683314fa1f569924&secretKey=2bb13c545ac61f6ccd2a47ef4a0549c2&dataFormat=column&blank=False',
dataType: 'json'
}).done(function(data){

for(let i = 0;i<data.IAP_12x18_preset_text.length;i++){

  // ------------------------Preset------------------
$('#outputPreset_IP_12X18').append(`
<tr>
    <td>
      <a class = "hoverPrimePreset12x18Link${i}" href="${data.IAP_12x18_preset_pdf_download[i]}" target="_blank"><i class="fa fa-file-pdf"></i>
      ${data.IAP_12x18_preset_text[i]}</a>
      <br>
      <img  class ="hoverPrimePreset12x18Image${i} ${data.IAP_12x18_preset_thumbnail_size[i]} hide" src="${data.IAP_12x18_preset_thumbnail[i]}" >
      <i class=" archive-icon fas fa-file-archive"></i>
          <a class = "hoverPrimePresetArchive12x18Link${i}" href = "${data.IAP_12x18_preset_archive_link[i]}">
          ${data.IAP_12x18_preset_archive_text[i]}
          </a>
          <img src = "/showcase/Intellicut_Archive_PopUp.png"class = "hoverPrimePresetArchive12x18Image${i} hide size">
          
    </td>
</tr>
  `)

//product images
for(let i = 0;i<data.IAP_12x18_preset_text.length;i++){
    $('.hoverPrimePreset12x18Link'+i).hover(function(){
      $('.hoverPrimePreset12x18Image'+i).css('display','block')
    },function(){
      $('.hoverPrimePreset12x18Image'+i).css('display','none')
    })
  }

//Archive Images
for(let i = 0;i<data.IAP_12x18_preset_text.length;i++){
$('.hoverPrimePresetArchive12x18Link'+i).hover(function(){
  $('.hoverPrimePresetArchive12x18Image'+i).css('display','block')
},function(){
  $('.hoverPrimePresetArchive12x18Image'+i).css('display','none')
})
}

}



for(let i = 0;i<data.IAP_12x18_manuset_text.length;i++){
  // ------------------------Manuset------------------
$('#outputManuset_IP_12x18').append(`
<tr>
    <td>
      <a class = "hoverPrimeManuset12x18Link${i}" href="${data.IAP_12x18_manuset_pdf_download[i]}" target="_blank"><i class="fa fa-file-pdf"></i>
      ${data.IAP_12x18_manuset_text[i]}</a>
      <br>
      <img  class ="hoverPrimeManuset12x18Image${i} ${data.IAP_12x18_manuset_thumbnail_size[i]} hide" src="${data.IAP_12x18_manuset_thumbnail[i]}" >
      <i class=" archive-icon fas fa-file-archive"></i>
          <a class = "hoverPrimeManusetArchive12x18Link${i}" href = "${data.IAP_12x18_manuset_archive_link[i]}">
          ${data.IAP_12x18_manuset_archive_text[i]}
          </a>
          <img src = "/showcase/Intellicut_Archive_PopUp.png"class = "hoverPrimeManusetArchive12x18Image${i} hide size">
          
    </td>
</tr>
  `)

//product images
for(let i = 0;i<data.IAP_12x18_manuset_text.length;i++){
    $('.hoverPrimeManuset12x18Link'+i).hover(function(){
      $('.hoverPrimeManuset12x18Image'+i).css('display','block')
    },function(){
      $('.hoverPrimeManuset12x18Image'+i).css('display','none')
    })
  }

//Archive Images
for(let i = 0;i<data.IAP_12x18_manuset_text.length;i++){
$('.hoverPrimeManusetArchive12x18Link'+i).hover(function(){
  $('.hoverPrimeManusetArchive12x18Image'+i).css('display','block')
},function(){
  $('.hoverPrimeManusetArchive12x18Image'+i).css('display','none')
})
}
}
// ------------------------------------------
for(let i = 0;i<data.IAP_12x18_flexmode_text.length;i++){
  // ---------------------FLEXMODE--------------
  $('#outputFlexMode_IP_12x18').append(`
  <tr>
      <td>
        <a class = "hoverPrimeFlexMode12x18Link${i}" href="${data.IAP_12x18_flexmode_pdf_download[i]}" target="_blank"><i class="fa fa-file-pdf"></i>
        ${data.IAP_12x18_flexmode_text[i]}</a>
        <br>
        <img  class ="hoverPrimeFlexMode12x18Image${i} ${data.IAP_12x18_flexmode_thumbnail_size[i]} hide" src="${data.IAP_12x18_flexmode_thumbnail[i]}" >
        <i class=" archive-icon fas fa-file-archive"></i>
            <a class = "hoverPrimeFlexModeArchive12x18Link${i}" href = "${data.IAP_12x18_flexmode_archive_link[i]}">
            ${data.IAP_12x18_flexmode_archive_text[i]}
            </a>
            <img src = "/showcase/Intellicut_Archive_PopUp.png"class = "hoverPrimeFlexModeArchive12x18Image${i} hide size">
            
      </td>
  </tr>
    `)

    //product imagesW
    for(let i = 0;i<data.IAP_12x18_flexmode_text.length;i++){
      $('.hoverPrimeFlexMode12x18Link'+i).hover(function(){
        $('.hoverPrimeFlexMode12x18Image'+i).css('display','block')
      },function(){
        $('.hoverPrimeFlexMode12x18Image'+i).css('display','none')
      })
    }

      
    //archive images
    for(let i = 0;i<data.IAP_12x18_flexmode_text.length;i++){
      $('.hoverPrimeFlexModeArchive12x18Link'+i).hover(function(){
        $('.hoverPrimeFlexModeArchive12x18Image'+i).css('display','block')
      },function(){
        $('.hoverPrimeFlexModeArchive12x18Image'+i).css('display','none')
      })
    }
 }
})

// ------------this is the end sheet IAV_11x17

// ------------------------------------------------------------------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------INTELLICUT PRIME 13X19-------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------------------------------------------------
$.ajax({
method:'GET',
url: 'https://api.apispreadsheets.com/data/15436/?accessKey=884c2b4f399a92e5d976b01b18868395&secretKey=9a7b9dbec16717796dcc00e5b3546b45&dataFormat=column&blank=False',
dataType: 'json'
}).done(function(data){
//preset

for(let i = 0;i<data.IAP_13x19_preset_text.length;i++){
  // ------------------------Prime------------------
    $('#outputPreset_IP_13X19').append(`
    <tr>
        <td>
          <a class = "hoverPrimePreset13x19Link${i}" href="${data.IAP_13x19_preset_pdf_download[i]}" target="_blank"><i class="fa fa-file-pdf"></i>
          ${data.IAP_13x19_preset_text[i]}</a>
          <br>
          <img  class ="hoverPrimePreset13x19Image${i} ${data.IAP_13x19_preset_thumbnail_size[i]} hide" src="${data.IAP_13x19_preset_thumbnail[i]}" >
          <i class=" archive-icon fas fa-file-archive"></i>
              <a class = "hoverPrimePresetArchive11x17Link${i}" href = "${data.IAP_13x19_preset_archive_link[i]}">
              ${data.IAP_13x19_preset_archive_text[i]}
              </a>
              <img src = "/showcase/Intellicut_Archive_PopUp.png"class = "hoverPrimePresetArchive11x17Image${i} hide size">
              
        </td>
    </tr>
      `)
    
  //product images
  for(let i = 0;i<data.IAP_13x19_preset_text.length;i++){
        $('.hoverPrimePreset13x19Link'+i).hover(function(){
          $('.hoverPrimePreset13x19Image'+i).css('display','block')
        },function(){
          $('.hoverPrimePreset13x19Image'+i).css('display','none')
        })
      }
    
  //Archive Images
  for(let i = 0;i<data.IAP_13x19_preset_text.length;i++){
    $('.hoverPrimePresetArchive11x17Link'+i).hover(function(){
      $('.hoverPrimePresetArchive11x17Image'+i).css('display','block')
    },function(){
      $('.hoverPrimePresetArchive11x17Image'+i).css('display','none')
    })
  }

}


for(let i = 0;i<data.IAP_13x19_manuset_text.length;i++){
// ------------------------Manuset------------------
$('#outputManuset_IP_13X19').append(`
<tr>
    <td>
      <a class = "hoverPrimeManuset13x19Link${i}" href="${data.IAP_13x19_manuset_pdf_download[i]}" target="_blank"><i class="fa fa-file-pdf"></i>
      ${data.IAP_13x19_manuset_text[i]}</a>
      <br>
      <img  class ="hoverPrimeManuset13x19Image${i} ${data.IAP_13x19_manuset_thumbnail_size[i]} hide" src="${data.IAP_13x19_manuset_thumbnail[i]}" >
      <i class=" archive-icon fas fa-file-archive"></i>
          <a class = "hoverPrimeManusetArchive13x19Link${i}" href = "${data.IAP_13x19_manuset_archive_link[i]}">
          ${data.IAP_13x19_manuset_archive_text[i]}
          </a>
          <img src = "/showcase/Intellicut_Archive_PopUp.png"class = "hoverVelocityManusetArchive13x19Image${i} hide size">
          
    </td>
</tr>
  `)
for(let i = 0;i<data.IAP_13x19_manuset_text.length;i++){
$('.hoverPrimeManuset13x19Link'+i).hover(function(){
  $('.hoverPrimeManuset13x19Image'+i).css('display','block')
},function(){
  $('.hoverPrimeManuset13x19Image'+i).css('display','none')
})
}
}
//archive
for(let i = 0;i<data.IAP_13x19_manuset_text.length;i++){
$('.hoverPrimeManusetArchive13x19Link'+i).hover(function(){
$('.hoverVelocityManusetArchive13x19Image'+i).css('display','block')
},function(){
$('.hoverVelocityManusetArchive13x19Image'+i).css('display','none')
})
}

})
// ------------------------------------------------------------------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------INTELLICUT VELOCITY 12X18-------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------------------------------------------------
$.ajax({
method:'GET',
url: 'https://api.apispreadsheets.com/data/1021/?accessKey=76edbf35bedcf3e0dd54380573079d3e&secretKey=1cce68ab530175718c2bd79a1e032aa2&dataFormat=column&blank=False',
dataType: 'json'
}).done(function(data){
//preset

for(let i = 0;i<data.IAV_12x18_preset_text.length;i++){

  // ------------------------Prime------------------
    $('#outputPreset_IVP_12x18').append(`
    <tr>
        <td>
          <a class = "hoverVelocityPreset12x18Link${i}" href="${data.IAV_12x18_preset_pdf_download[i]}" target="_blank"><i class="fa fa-file-pdf"></i>
          ${data.IAV_12x18_preset_text[i]}</a>
          <br>
          <img  class ="hoverVelocityPreset12x18Image${i} ${data.IAV_12x18_preset_thumbnail_size[i]} hide" src="${data.IAV_12x18_preset_thumbnail[i]}" >
          <i class=" archive-icon fas fa-file-archive"></i>
              <a class = "hoverVelocityPresetArchive12x18Link${i}" href = "${data.IAV_12x18_preset_archive_link[i]}">
              ${data.IAV_12x18_preset_archive_text[i]}
              </a>
              <img src = "/showcase/Intellicut_Archive_PopUp.png"class = "hoverVelocityPresetArchive12x18Image${i} hide size">
              
        </td>
    </tr>
      `)
    
  //product images
  for(let i = 0;i<data.IAV_12x18_preset_text.length;i++){
        $('.hoverVelocityPreset12x18Link'+i).hover(function(){
          $('.hoverVelocityPreset12x18Image'+i).css('display','block')
        },function(){
          $('.hoverVelocityPreset12x18Image'+i).css('display','none')
        })
      }
    
  //Archive Images
  for(let i = 0;i<data.IAV_12x18_preset_text.length;i++){
    $('.hoverVelocityPresetArchive12x18Link'+i).hover(function(){
      $('.hoverVelocityPresetArchive12x18Image'+i).css('display','block')
    },function(){
      $('.hoverVelocityPresetArchive12x18Image'+i).css('display','none')
    })
  }

}
for(let i = 0;i<data.IAV_12x18_manuset_text.length;i++){
// ------------------------Manuset------------------
$('#outputManuset_IVM_12X18').append(`
<tr>
    <td>
      <a class = "hoverVelocityManuset12x18Link${i}" href="${data.IAV_12x18_manuset_pdf_download[i]}" target="_blank"><i class="fa fa-file-pdf"></i>
      ${data.IAV_12x18_manuset_text[i]}</a>
      <br>
      <img  class ="hoverVelocityManuset12x18Image${i} ${data.IAV_12x18_manuset_thumbnail_size[i]} hide" src="${data.IAV_12x18_manuset_thumbnail[i]}" >
      <i class=" archive-icon fas fa-file-archive"></i>
          <a class = "hoverVelocityManusetArchive12x18Link${i}" href = "${data.IAV_12x18_manuset_archive_link[i]}">
          ${data.IAV_12x18_manuset_archive_text[i]}
          </a>
          <img src = "/showcase/Intellicut_Archive_PopUp.png"class = "hoverVelocityManusetArchive12x18Image${i} hide size">
          
    </td>
</tr>
  `)
for(let i = 0;i<data.IAV_12x18_manuset_text.length;i++){
$('.hoverVelocityManuset12x18Link'+i).hover(function(){
  $('.hoverVelocityManuset12x18Image'+i).css('display','block')
},function(){
  $('.hoverVelocityManuset12x18Image'+i).css('display','none')
})
}
}
//archive
for(let i = 0;i<data.IAV_12x18_manuset_text.length;i++){
$('.hoverVelocityManusetArchive12x18Link'+i).hover(function(){
$('.hoverVelocityManusetArchive12x18Image'+i).css('display','block')
},function(){
$('.hoverVelocityManusetArchive12x18Image'+i).css('display','none')
})
}

})
// ------------------------------------------------------------------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------INTELLICUT VELOCITY 13X19-------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------------------------------------------------
$.ajax({
method:'GET',
url: 'https://api.apispreadsheets.com/data/1022/?accessKey=0e9c2d795c266f1e73921697f5d285f6&secretKey=09319c8249b47fcea873ca2614b19e6d&dataFormat=column&blank=False',
dataType: 'json'
}).done(function(data){
for(let i = 0;i<data.IAV_13x19_preset_text.length;i++){

  // ------------------------Prime------------------
    $('#outputPreset_IVP_13x19').append(`
    <tr>
        <td>
          <a class = "hoverVelocityPreset13x19Link${i}" href="${data.IAV_13x19_preset_pdf_download[i]}" target="_blank"><i class="fa fa-file-pdf"></i>
          ${data.IAV_13x19_preset_text[i]}</a>
          <br>
          <img  class ="hoverVelocityPreset13x19Image${i} ${data.IAV_13x19_preset_thumbnail_size[i]} hide" src="${data.IAV_13x19_preset_thumbnail[i]}" >
          <i class=" archive-icon fas fa-file-archive"></i>
              <a class = "hoverVelocityPresetArchive13x19Link${i}" href = "${data.IAV_13x19_preset_archive_link[i]}">
              ${data.IAV_13x19_preset_archive_text[i]}
              </a>
              <img src = "/showcase/Intellicut_Archive_PopUp.png"class = "hoverVelocityPresetArchive13x19Image${i} hide size">
              
        </td>
    </tr>
      `)
    
  //product images
  for(let i = 0;i<data.IAV_13x19_preset_text.length;i++){
        $('.hoverVelocityPreset13x19Link'+i).hover(function(){
          $('.hoverVelocityPreset13x19Image'+i).css('display','block')
        },function(){
          $('.hoverVelocityPreset13x19Image'+i).css('display','none')
        })
      }
    
  //Archive Images
  for(let i = 0;i<data.IAV_13x19_preset_text.length;i++){
    $('.hoverVelocityPresetArchive13x19Link'+i).hover(function(){
      $('.hoverVelocityPresetArchive13x19Image'+i).css('display','block')
    },function(){
      $('.hoverVelocityPresetArchive13x19Image'+i).css('display','none')
    })
  }

}
for(let i = 0;i<data.IAV_13x19_manuset_text.length;i++){

// ------------------------Manuset------------------
$('#outputPreset_IVM_13x19').append(`
<tr>
    <td>
      <a class = "hoverVelocityManuset13x19Link${i}" href="${data.IAV_13x19_manuset_pdf_download[i]}" target="_blank"><i class="fa fa-file-pdf"></i>
      ${data.IAV_13x19_manuset_text[i]}</a>
      <br>
      <img  class ="hoverVelocityManuset13x19Image${i} ${data.IAV_13x19_manuset_thumbnail_size[i]} hide" src="${data.IAV_13x19_manuset_thumbnail[i]}" >
      <i class=" archive-icon fas fa-file-archive"></i>
          <a class = "hoverVelocityManusetArchive13x19Link${i}" href = "${data.IAV_13x19_manuset_archive_link[i]}">
          ${data.IAV_13x19_manuset_archive_text[i]}
          </a>
          <img src = "/showcase/Intellicut_Archive_PopUp.png"class = "hoverVelocityManusetArchive13x19Image${i} hide size">
          
    </td>
</tr>
  `)
for(let i = 0;i<data.IAV_13x19_manuset_text.length;i++){
$('.hoverVelocityManuset13x19Link'+i).hover(function(){
  $('.hoverVelocityManuset13x19Image'+i).css('display','block')
},function(){
  $('.hoverVelocityManuset13x19Image'+i).css('display','none')
})
}
}
//archive
for(let i = 0;i<data.IAV_13x19_manuset_text.length;i++){
$('.hoverVelocityManusetArchive13x19Link'+i).hover(function(){
$('.hoverVelocityManusetArchive13x19Image'+i).css('display','block')
},function(){
$('.hoverVelocityManusetArchive13x19Image'+i).css('display','none')
})
}

})

// --------------------------Vision Focus 11x17-----------------------------------
$.ajax({
method:'GET',
url: 'https://api.apispreadsheets.com/data/1024/?accessKey=09de579cb4480fe041371d64c233e8b3&secretKey=4949f653b565c867f77d0e591f77e5cf&dataFormat=column&blank=False',
dataType: 'json'
}).done(function(data){

  for(let i = 0;i<data.VF_11x17_text.length;i++){
    // ------------------------Preset------------------
  $('#ouput_focus_11x17').append(` 
    <tr>
          <td>
            <a class="d-xl-flex align-items-xl-center defaultFocus11x17Link${i}"  href="${data.VF_11x17_pdf_download[i]}" target="_blank">
                <i class="fa fa-file-pdf"></i>${data.VF_11x17_text[i]}
            </a>
                <img  class ="defaultFocus11x17Image${i} ${data.VF_11x17_thumbnail_size[i]} hide" src="${data.VF_11x17_thumbnail[i]}" >
            <a class="d-xl-flex align-items-xl-center hoverVF_11x17ArchiveLink${i}"  href="${data.VF_11x17_archive_link[i]}" target="_blank">
                <i class="fa fa-file-pdf"></i>${data.VF_11x17_archive_text[i]}
            </a>
            <img src = "/showcase/IntellicutNano_Archive_PopUp.png"class = "hoverArchiveCustomImage${i} hide size">
          </td>
      </tr>
        `)

}

for(let i = 0;i<data.VF_11x17_text.length;i++){
  $('.defaultFocus11x17Link'+i).hover(function(){
    $('.defaultFocus11x17Image'+i).css('display','block')
  },function(){
    $('.defaultFocus11x17Image'+i).css('display','none')
  })
}

for(let i = 0;i<data.VF_11x17_text.length;i++){
  $('.hoverVF_11x17ArchiveLink'+i).hover(function(){
    $('.hoverArchiveCustomImage'+i).css('display','block')
  },function(){
    $('.hoverArchiveCustomImage'+i).css('display','none')
  })
}
})
// --------------------------Vision Focus 12x18-----------------------------------
$.ajax({
method:'GET',
url: 'https://api.apispreadsheets.com/data/1025/?accessKey=71b0e15411ad9ccae3f0169d34942f83&secretKey=a456ab9c319cd6a90fbdad035a1fc2f5&dataFormat=column&blank=False',
dataType: 'json'
}).done(function(data){

  for(let i = 0;i<data.VF_12x18_text.length;i++){
    // ------------------------Preset------------------
  $('#ouput_focus_12x18').append(` 
    <tr>
          <td>
            <a class="d-xl-flex align-items-xl-center defaultFocus12x18Link${i}"  href="${data.VF_12x18_pdf_download[i]}" target="_blank">
                <i class="fa fa-file-pdf"></i>${data.VF_12x18_text[i]}
            </a>
                <img  class ="defaultFocus12x18Image${i} ${data.VF_12x18_thumbnail_size[i]} hide" src="${data.VF_12x18_thumbnail[i]}" >

          <a class="d-xl-flex align-items-xl-center hoverVF_12x18ArchiveLink${i}"  href="${data.VF_12x18_archive_link[i]}" target="_blank">
                <i class="fa fa-file-pdf"></i>${data.VF_12x18_archive_text[i]}
            </a>
            <img src = "/showcase/IntellicutNano_Archive_PopUp.png"class = "hoverArchiveCustomImage${i} hide size">
          </td>
      </tr>
        `)
  
}

for(let i = 0;i<data.VF_12x18_text.length;i++){
  $('.defaultFocus12x18Link'+i).hover(function(){
    $('.defaultFocus12x18Image'+i).css('display','block')
  },function(){
    $('.defaultFocus12x18Image'+i).css('display','none')
  })
}

for(let i = 0;i<data.VF_12x18_text.length;i++){
  $('.hoverVF_12x18ArchiveLink'+i).hover(function(){
    $('.hoverArchiveCustomImage'+i).css('display','block')
  },function(){
    $('.hoverArchiveCustomImage'+i).css('display','none')
  })
}
})
// --------------------------Vision Focus 13x19-----------------------------------
$.ajax({
method:'GET',
url: 'https://api.apispreadsheets.com/data/1026/?accessKey=cb533ab3128da5d4b1812763d5acb60e&secretKey=fdfd645df1e45b3a85e88848deb892bd&dataFormat=column&blank=False',
dataType: 'json'
}).done(function(data){

  for(let i = 0;i<data.VF_13x19_text.length;i++){
    // ------------------------Preset------------------
  $('#ouput_focus_13x19').append(` 
    <tr>
          <td>
            <a class="d-xl-flex align-items-xl-center defaultFocus13x19Link${i}"  href="${data.VF_13x19_pdf_download[i]}" target="_blank">
                <i class="fa fa-file-pdf"></i>${data.VF_13x19_text[i]}
            </a>
                <img class ="defaultFocus13x19Image${i} ${data.VF_13x19_thumbnail_size[i]} hide" src="${data.VF_13x19_thumbnail[i]}" >

                <a class="d-xl-flex align-items-xl-center hoverVF_13x19ArchiveLink${i}"  href="${data.VF_13x19_archive_link[i]}" target="_blank">
                      <i class="fa fa-file-pdf"></i>${data.VF_13x19_archive_text[i]}
                      </a>
                  <img src = "/showcase/IntellicutNano_Archive_PopUp.png"class = "hoverArchiveCustomImage${i} hide size">
          </td>
      </tr>
        `)
  }

  for(let i = 0;i<data.VF_13x19_text.length;i++){
    $('.defaultFocus13x19Link'+i).hover(function(){
      $('.defaultFocus13x19Image'+i).css('display','block')
    },function(){
      $('.defaultFocus13x19Image'+i).css('display','none')
    })
  }

  for(let i = 0;i<data.VF_13x19_text.length;i++){
    $('.hoverVF_13x19ArchiveLink'+i).hover(function(){
      $('.hoverArchiveCustomImage'+i).css('display','block')
    },function(){
      $('.hoverArchiveCustomImage'+i).css('display','none')
    })
  }


})

// --------------------------Nano-----------------------------------
$.ajax({
  method:'GET',
  url: 'https://api.apispreadsheets.com/data/i7JP8hjLlEnqsjmm/?accessKey=3f5324b8b8d96f1f801b6e378f46d836&secretKey=5e40fe39c9b5c39f3e210526ff2da6f5&dataFormat=column&blank=False',
  dataType: 'json'
  }).done(function(data){

    console.log(data)
    for(let i = 0;i<data.VF_320x450_text.length;i++){
      // ------------------------Preset------------------
    $('#ouput_nano_320x450').append(` 
        <tr>
              <td>
                <a class="d-xl-flex align-items-xl-center defaultFocus13x19Link${i}"  href="${data.VF_320x450_pdf_download[i]}" target="_blank">
                    <i class="fa fa-file-pdf"></i>${data.VF_320x450_text[i]}
                </a>
                    <img  class ="defaultVF_320x450Image${i} hide" src="${data.VF_320x450_thumbnail[i]}" >

                    <a class="d-xl-flex align-items-xl-center hoverAC11x17ArchiveLink${i}"  href="${data.VF_320x450_archive_link[i]}" target="_blank">
                      <i class="fa fa-file-pdf"></i>${data.VF_320x450_archive_text[i]}
                      </a>
                  <img src = "/showcase/IntellicutNano_Archive_PopUp.png"class = "hoverArchiveCustomImage${i} hide size">
              </td>
          </tr>

       
          `)
    
  }
  for(let i = 0;i<data.VF_320x450_text.length;i++){
    // console.log($('.defaultVF_320x450Image'+i))
      $('.defaultFocus13x19Link'+i).hover(function(){
        $('.defaultVF_320x450Image'+i).css('display','block')
      },function(){
        $('.defaultVF_320x450Image'+i).css('display','none')
      })
    }

      for(let i = 0;i<data.VF_320x450_text.length;i++){
        $('.hoverAC11x17ArchiveLink'+i).hover(function(){
          $('.hoverArchiveCustomImage'+i).css('display','block')
        },function(){
          $('.hoverArchiveCustomImage'+i).css('display','none')
        })
      }


  })

  

    

// ------------------------VRCUT 11X17------------------------------------------------

$.ajax({
method:'GET',
url: 'https://api.apispreadsheets.com/data/1027/?accessKey=22c4cbb4f28beeb15b6d116fb8d08f61&secretKey=a0e14f378cf51e8e786b6ccef2985971&dataFormat=column&blank=False',
dataType: 'json'
}).done(function(data){

  for(let i = 0;i<data.VRCut_11x17_text_AC.length;i++){
    // ------------------------AC------------------
  $('#output_vrcut_11x17_AC').append(` 
    <tr>
          <td>
              <a class="d-xl-flex align-items-xl-center hoverAC11x17Link${i}"  href="${data.VRCut_11x17_pdf_download_AC[i]}" target="_blank">
                  <i class="fa fa-file-pdf"></i>${data.VRCut_11x17_text_AC[i]}
              </a>
              <img  class ="hoverAC11x17Image${i} ${data.VRCut_11x17_thumbnail_size_AC[i]} hide" src="${data.VRCut_11x17_thumbnail_AC[i]}" >

             
              <a class="d-xl-flex align-items-xl-center hoverAC11x17ArchiveLink${i}"  href="${data.VRCut_11x17_archive_default_link[i]}" target="_blank">
                  <i class="fa fa-file-pdf"></i>${data.VRCut_11x17_archive_default_text[i]}
              </a>
              <img src = "/showcase/VRCutImpose_Archive_PopUp.png"class = "hoverAC11x17ArchiveCustomImage${i} hide size">
            
          </td>
      </tr>
        `)
}


   for(let i = 0;i<data.VRCut_11x17_text_AC.length;i++){
      $('.hoverAC11x17Link'+i).hover(function(){
        $('.hoverAC11x17Image'+i).css('display','block')
      },function(){
        $('.hoverAC11x17Image'+i).css('display','none')
      })
    }
    for(let i = 0;i<data.VRCut_11x17_text_AC.length;i++){
      $('.hoverAC11x17ArchiveLink'+i).hover(function(){
        $('.hoverAC11x17ArchiveCustomImage'+i).css('display','block')
      },function(){
        $('.hoverAC11x17ArchiveCustomImage'+i).css('display','none')
      })
    }
//--------------------------CUSTOM-------------------
for(let i = 0;i<data.VRCut_11x17_text_custom.length;i++){

$('#output_vrcut_11x17_custom').append(` 
  <tr>
      <td>
          <a class="d-xl-flex align-items-xl-center hoverCustom11x17Link${i}"  href="${data.VRCut_11x17_pdf_download_custom[i]}" target="_blank">
              <i class="fa fa-file-pdf"></i>${data.VRCut_11x17_text_custom[i]}
          </a>
          <img  class ="hoverCustom11x17Image${i} ${data.VRCut_11x17_thumbnail_size_custom[i]} hide" src="${data.VRCut_11x17_thumbnail_custom[i]}" >
          <!------------------>

          <a class = "hoverCustom11x17ArchiveLink${i}" href = "${data.VRCut_11x17_archive_custom_link[i]}">
              <i class=" archive-icon fas fa-file-archive"></i>${data.VRCut_11x17_archive_custom_text[i]}
          </a>
          <img src = "/showcase/VRCutImpose_Archive_PopUp.png"class = "hoverCustom11x17ArchiveImage${i} hide size">
          <br>
          <!--------------->
          <a class = "hoverCustom11x17ArchiveCustomLink${i}" href = "${data.VRCut_11x17_vrc_archive_custom_link[i]}">
              <i class=" archive-icon fas fa-file-archive"></i>${data.VRCut_11x17_vrc_archive_custom_text[i]}
          </a>
          <img src = "/showcase/VRCut_Archive_PopUp.png"class = "hoverCustom11x17ArchiveCustomImage${i} hide size">
          <!------------------>
         
          
          
        

      </td>
    </tr>
      `)
  for(let i = 0;i<data.VRCut_11x17_text_custom.length;i++){
    $('.hoverCustom11x17Link'+i).hover(function(){
      $('.hoverCustom11x17Image'+i).css('display','block')
    },function(){
      $('.hoverCustom11x17Image'+i).css('display','none')
    })
  }
  for(let i = 0;i<data.VRCut_11x17_text_custom.length;i++){
    $('.hoverCustom11x17ArchiveLink'+i).hover(function(){
      $('.hoverCustom11x17ArchiveImage'+i).css('display','block')
    },function(){
      $('.hoverCustom11x17ArchiveImage'+i).css('display','none')
    })
  }

  for(let i = 0;i<data.VRCut_11x17_text_custom.length;i++){
    $('.hoverCustom11x17ArchiveCustomLink'+i).hover(function(){
      $('.hoverCustom11x17ArchiveCustomImage'+i).css('display','block')
    },function(){
      $('.hoverCustom11x17ArchiveCustomImage'+i).css('display','none')
    })
  }
}

})

//------------------------- VRCUT 12x18----------------------------------------

$.ajax({
method:'GET',
url: 'https://api.apispreadsheets.com/data/1029/?accessKey=ee6e391b3dcba0fab4a0ea4b68555564&secretKey=d64734d986f53ccfba4aa1695aec61e5&dataFormat=column&blank=False',
dataType: 'json'
}).done(function(data){

for(let i = 0;i<data.VRCut_12x18_text_AC.length;i++){
  // ------------------------AC------------------
$('#output_vrcut_12x18_AC').append(` 
  <tr>
        <td>
            <a class="d-xl-flex align-items-xl-center hoverAC12x18Link${i}"  href="${data.VRCut_12x18_pdf_download_AC[i]}" target="_blank">
                <i class="fa fa-file-pdf"></i>${data.VRCut_12x18_text_AC[i]}
            </a>
            <img  class ="hoverAC12x18Image${i} ${data.VRCut_12x18_thumbnail_size_AC[i]} hide" src="${data.VRCut_12x18_thumbnail_AC[i]}" >

           
            <a class="d-xl-flex align-items-xl-center hoverAC12x18ArchiveLink${i}"  href="${data.VRCut_12x18_archive_default_link[i]}" target="_blank">
                <i class="fa fa-file-pdf"></i>${data.VRCut_12x18_archive_default_text[i]}
            </a>
            <img src = "/showcase/VRCutImpose_Archive_PopUp.png"class = "hoverAC12x18ArchiveCustomImage${i} hide size">
          
        </td>
    </tr>
      `)
}


 for(let i = 0;i<data.VRCut_12x18_text_AC.length;i++){
    $('.hoverAC12x18Link'+i).hover(function(){
      $('.hoverAC12x18Image'+i).css('display','block')
    },function(){
      $('.hoverAC12x18Image'+i).css('display','none')
    })
  }
  for(let i = 0;i<data.VRCut_12x18_text_AC.length;i++){
    $('.hoverAC12x18ArchiveLink'+i).hover(function(){
      $('.hoverAC12x18ArchiveCustomImage'+i).css('display','block')
    },function(){
      $('.hoverAC12x18ArchiveCustomImage'+i).css('display','none')
    })
  }


//--------------------------CUSTOM-------------------

for(let i = 0;i<data.VRCut_12x18_text_custom.length;i++){
  $('#output_vrcut_12x18_custom').append(` 
  <tr>
      <td>
          <a class="d-xl-flex align-items-xl-center hoverCustom12x18Link${i}"  href="${data.VRCut_12x18_pdf_download_custom[i]}" target="_blank">
              <i class="fa fa-file-pdf"></i>${data.VRCut_12x18_text_custom[i]}
          </a>
          <img  class ="hoverCustom12x18Image${i} ${data.VRCut_12x18_thumbnail_size_custom[i]} hide" src="${data.VRCut_12x18_thumbnail_custom[i]}" >
          <!------------------>

          <a class = "hoverCustom12x18ArchiveLink${i}" href = "${data.VRCut_12x18_archive_custom_link[i]}">
              <i class=" archive-icon fas fa-file-archive"></i>${data.VRCut_12x18_archive_custom_text[i]}
          </a>
          <img src = "/showcase/VRCutImpose_Archive_PopUp.png"class = "hoverCustom12x18ArchiveImage${i} hide size">
          <br>
          <!--------------->
          <a class = "hoverCustom12x18ArchiveCustomLink${i}" href = "${data.VRCut_12x18_vrc_archive_custom_link[i]}">
              <i class=" archive-icon fas fa-file-archive"></i>${data.VRCut_12x18_vrc_archive_custom_text[i]}
          </a>
          <img src = "/showcase/VRCut_Archive_PopUp.png"class = "hoverCustom12x18ArchiveCustomImage${i} hide size">
          <!------------------>
      </td>
    </tr>
      `)
  for(let i = 0;i<data.VRCut_12x18_text_custom.length;i++){
    $('.hoverCustom12x18Link'+i).hover(function(){
      $('.hoverCustom12x18Image'+i).css('display','block')
    },function(){
      $('.hoverCustom12x18Image'+i).css('display','none')
    })
  }
  for(let i = 0;i<data.VRCut_12x18_text_custom.length;i++){
    $('.hoverCustom12x18ArchiveLink'+i).hover(function(){
      $('.hoverCustom12x18ArchiveImage'+i).css('display','block')
    },function(){
      $('.hoverCustom12x18ArchiveImage'+i).css('display','none')
    })
  }

  for(let i = 0;i<data.VRCut_12x18_text_custom.length;i++){
    $('.hoverCustom12x18ArchiveCustomLink'+i).hover(function(){
      $('.hoverCustom12x18ArchiveCustomImage'+i).css('display','block')
    },function(){
      $('.hoverCustom12x18ArchiveCustomImage'+i).css('display','none')
    })
  }
}
})
//------------------------- VRCUT 13x19----------------------------------------
$.ajax({
method:'GET',
url: 'https://api.apispreadsheets.com/data/1030/?accessKey=1cdd8036d95a11acbcd15f0fa7927e60&secretKey=46655fe23f5d41ded8ce0ad30dd86f4d&dataFormat=column&blank=False',
dataType: 'json'
}).done(function(data){
for(let i = 0;i<data.VRCut_13x19_text_AC.length;i++){
  // ------------------------AC------------------
$('#output_vrcut_13x19_AC').append(` 
  <tr>
        <td>
            <a class="d-xl-flex align-items-xl-center hoverAC13x19Link${i}"  href="${data.VRCut_13x19_pdf_download_AC[i]}" target="_blank">
                <i class="fa fa-file-pdf"></i>${data.VRCut_13x19_text_AC[i]}
            </a>
            <img  class ="hoverAC13x19Image${i} ${data.VRCut_13x19_thumbnail_size_AC[i]} hide" src="${data.VRCut_13x19_thumbnail_AC[i]}" >

           
            <a class="d-xl-flex align-items-xl-center hoverAC13x19ArchiveLink${i}"  href="${data.VRCut_13x19_archive_default_link[i]}" target="_blank">
                <i class="fa fa-file-pdf"></i>${data.VRCut_13x19_archive_default_text[i]}
            </a>
            <img src = "/showcase/VRCutImpose_Archive_PopUp.png"class = "hoverAC13x19ArchiveCustomImage${i} hide size">
          
        </td>
    </tr>
      `)
}


 for(let i = 0;i<data.VRCut_13x19_text_AC.length;i++){
    $('.hoverAC13x19Link'+i).hover(function(){
      $('.hoverAC13x19Image'+i).css('display','block')
    },function(){
      $('.hoverAC13x19Image'+i).css('display','none')
    })
  }
  for(let i = 0;i<data.VRCut_13x19_text_AC.length;i++){
    $('.hoverAC13x19ArchiveLink'+i).hover(function(){
      $('.hoverAC13x19ArchiveCustomImage'+i).css('display','block')
    },function(){
      $('.hoverAC13x19ArchiveCustomImage'+i).css('display','none')
    })
  }
//--------------------------CUSTOM-------------------
for(let i = 0;i<data.VRCut_13x19_text_custom.length;i++){
$('#output_vrcut_13x19_custom').append(` 
<tr>
    <td>
        <a class="d-xl-flex align-items-xl-center hoverCustom13x19Link${i}"  href="${data.VRCut_13x19_pdf_download_custom[i]}" target="_blank">
            <i class="fa fa-file-pdf"></i>${data.VRCut_13x19_text_custom[i]}
        </a>
        <img  class ="hoverCustom13x19Image${i} ${data.VRCut_13x19_thumbnail_size_custom[i]} hide" src="${data.VRCut_13x19_thumbnail_custom[i]}" >
        <!------------------>

        <a class = "hoverCustom13x19ArchiveLink${i}" href = "${data.VRCut_13x19_archive_custom_link[i]}">
            <i class=" archive-icon fas fa-file-archive"></i>${data.VRCut_13x19_archive_custom_text[i]}
        </a>
        <img src = "/showcase/VRCutImpose_Archive_PopUp.png"class = "hoverCustom13x19ArchiveImage${i} hide size">
        <br>
        <!--------------->
        <a class = "hoverCustom13x19ArchiveCustomLink${i}" href = "${data.VRCut_13x19_vrc_archive_custom_link[i]}">
            <i class=" archive-icon fas fa-file-archive"></i>${data.VRCut_13x19_vrc_archive_custom_text[i]}
        </a>
        <img src = "/showcase/VRCut_Archive_PopUp.png"class = "hoverCustom13x19ArchiveCustomImage${i} hide size">
        <!------------------>
    </td>
  </tr>
    `)
for(let i = 0;i<data.VRCut_13x19_text_custom.length;i++){
  $('.hoverCustom13x19Link'+i).hover(function(){
    $('.hoverCustom13x19Image'+i).css('display','block')
  },function(){
    $('.hoverCustom13x19Image'+i).css('display','none')
  })
}
for(let i = 0;i<data.VRCut_13x19_text_custom.length;i++){
  $('.hoverCustom13x19ArchiveLink'+i).hover(function(){
    $('.hoverCustom13x19ArchiveImage'+i).css('display','block')
  },function(){
    $('.hoverCustom13x19ArchiveImage'+i).css('display','none')
  })
}

for(let i = 0;i<data.VRCut_13x19_text_custom.length;i++){
  $('.hoverCustom13x19ArchiveCustomLink'+i).hover(function(){
    $('.hoverCustom13x19ArchiveCustomImage'+i).css('display','block')
  },function(){
    $('.hoverCustom13x19ArchiveCustomImage'+i).css('display','none')
  })
}
}
})



//------------------------- IAV_SRA3_320x450----------------------------------------
$.ajax({
method:'GET',
url: 'https://api.apispreadsheets.com/data/19325/?accessKey=26879d48d19690775e576db10eb1c7ea&secretKey=70b7e5edd6347d17620119520a5bb594&dataFormat=column&blank=False',
dataType: 'json'
}).done(function(data){

console.log(data)
for(let i = 0;i<data.IAV_320x450_preset_archive_text.length;i++){

  // ------------------------AC------------------
$('#outputPreset_IAV_SRA3').append(` 
  <tr>
        <td>
            <a class="d-xl-flex align-items-xl-center IAV_320x450_preset_link${i}"  href="${data.IAV_320x450_preset_pdf_download[i]}" target="_blank">
                <i class="fa fa-file-pdf"></i>${data.IAV_320x450_preset_text[i]}
            </a>
            <img  class ="IAV_320x450_preset_image${i} ${data.IAV_320x450_preset_thumbnail_size[i]} hide" src="${data.IAV_320x450_preset_thumbnail[i]}" >

           
            <a class="d-xl-flex align-items-xl-center IAV_320x450_preset_archive_link${i}"  href="${data.IAV_320x450_preset_archive_link[i]}" target="_blank">
                <i class="fa fa-file-pdf"></i>${data.IAV_320x450_preset_archive_text[i]}
            </a>
            <img src = "/showcase/IntellicutGlobal_Archive_PopUp.png"class = "IAV_320x450_preset_image_link${i} hide size">
          
        </td>
    </tr>
      `)


      $('.IAV_320x450_preset_link'+i).hover(function(){
        $('.IAV_320x450_preset_image'+i).css('display','block')
      },function(){
        $('.IAV_320x450_preset_image'+i).css('display','none')
      })



      $('.IAV_320x450_preset_archive_link'+i).hover(function(){
        $('.IAV_320x450_preset_image_link'+i).css('display','block')
      },function(){
        $('.IAV_320x450_preset_image_link'+i).css('display','none')
      })
}
//
for(let i = 0;i<data.IAV_320x450_manuset_text.length;i++){

// ------------------------AC------------------

$('#outputManuset_IAV_SRA3').append(` 
<tr>
      <td>
          <a class="d-xl-flex align-items-xl-center IAV_320x450_manuset_link${i}"  href="${data.IAV_320x450_manuset_pdf_download[i]}" target="_blank">
              <i class="fa fa-file-pdf"></i>${data.IAV_320x450_manuset_text[i]}
          </a>
          <img  class ="IAV_320x450_manuset_image${i} ${data.IAV_320x450_manuset_thumbnail_size[i]} hide" src="${data.IAV_320x450_manuset_thumbnail[i]}" >

         
          <a class="d-xl-flex align-items-xl-center IAV_320x450_manuset_archive_link${i}"  href="${data.IAV_320x450_manuset_archive_link[i]}" target="_blank">
              <i class="fa fa-file-pdf"></i>${data.IAV_320x450_manuset_archive_text[i]}
          </a>
          <img src = "/showcase/IntellicutGlobal_Archive_PopUp.png"class = "IAV_320x450_manuset_archive_image${i} hide size">
        
      </td>
  </tr>
    `)

    $('.IAV_320x450_manuset_link'+i).hover(function(){
      $('.IAV_320x450_manuset_image'+i).css('display','block')
    },function(){
      $('.IAV_320x450_manuset_image'+i).css('display','none')
    })



    $('.IAV_320x450_manuset_archive_link'+i).hover(function(){
      $('.IAV_320x450_manuset_archive_image'+i).css('display','block')
    },function(){
      $('.IAV_320x450_manuset_archive_image'+i).css('display','none')
    })
}



})


//------------------------- IAP_SRA3_320x450----------------------------------------
$.ajax({
  method:'GET',
  url: 'https://api.apispreadsheets.com/data/19323/?accessKey=8f87cdeba01063acdeccc71094fca024&secretKey=cfe28fe943731857f2d89047b1fc4cdd&dataFormat=column&blank=False',
  dataType: 'json'
  }).done(function(data){
  
  console.log(data)
  for(let i = 0;i<data.IAP_320x450_preset_text.length;i++){
    // ------------------------AC------------------
  $('#outputPreset_IAP_SRA3').append(` 
    <tr>
          <td>
              <a class="d-xl-flex align-items-xl-center IAV_320x450_preset_link${i}"  href="${data.IAP_320x450_preset_pdf_download[i]}" target="_blank">
                  <i class="fa fa-file-pdf"></i>${data.IAP_320x450_preset_text[i]}
              </a>
              <img  class ="IAV_320x450_preset_image${i} ${data.IAP_320x450_preset_thumbnail_size[i]} hide" src="${data.IAP_320x450_preset_thumbnail[i]}" >
  
             
              <a class="d-xl-flex align-items-xl-center IAV_320x450_archive_link${i}"  href="${data.IAP_320x450_preset_archive_link[i]}" target="_blank">
                  <i class="fa fa-file-pdf"></i>${data.IAP_320x450_preset_archive_text[i]}
              </a>
              <img src = "/showcase/IntellicutGlobal_Archive_PopUp.png"class = "IAV_320x450_archive_image${i} hide size">
            
          </td>
      </tr>
        `)
  
          $('.IAV_320x450_preset_link'+i).hover(function(){
            $('.IAV_320x450_preset_image'+i).css('display','block')
          },function(){
            $('.IAV_320x450_preset_image'+i).css('display','none')
          })
  
  
  
          $('.IAV_320x450_archive_link'+i).hover(function(){
            $('.IAV_320x450_archive_image'+i).css('display','block')
          },function(){
            $('.IAV_320x450_archive_image'+i).css('display','none')
          })
     
  }
  
  
  for(let i = 0;i<data.IAP_320x450_manuset_text.length;i++){
  
    $('#outputManuset_IAP_SRA3').append(` 
      <tr>
            <td>
                <a class="d-xl-flex align-items-xl-center IAP_320x450_manuset_link${i}"  href="${data.IAP_320x450_manuset_pdf_download[i]}" target="_blank">
                    <i class="fa fa-file-pdf"></i>${data.IAP_320x450_manuset_text[i]}
                </a>
                <img  class ="IAP_320x450_manuset_image${i} ${data.IAP_320x450_manuset_thumbnail_size[i]} hide" src="${data.IAP_320x450_manuset_thumbnail[i]}" >
  
              
                <a class="d-xl-flex align-items-xl-center IAP_320x450_manuset_archive_link${i}"  href="${data.IAP_320x450_manuset_archive_link[i]}" target="_blank">
                    <i class="fa fa-file-pdf"></i>${data.IAP_320x450_manuset_archive_text[i]}
                </a>
                <img src = "/showcase/IntellicutGlobal_Archive_PopUp.png"class = "IAP_320x450_manuset_archive_image${i} hide size">
              
            </td>
        </tr>
          `)
  
          $('.IAP_320x450_manuset_link'+i).hover(function(){
            $('.IAP_320x450_manuset_image'+i).css('display','block')
          },function(){
            $('.IAP_320x450_manuset_image'+i).css('display','none')
          })
  
  
  
          $('.IAP_320x450_manuset_archive_link'+i).hover(function(){
            $('.IAP_320x450_manuset_archive_image'+i).css('display','block')
          },function(){
            $('.IAP_320x450_manuset_archive_image'+i).css('display','none')
          })
  
  }
  
  for(let i = 0;i<data.IAP_320x450_flexmode_text.length;i++){
  
  $('#outputFlexMode_IAP_SRA3').append(` 
    <tr>
          <td>
              <a class="d-xl-flex align-items-xl-center IAP_320x450_flexmode_link${i}"  href="${data.IAP_320x450_flexmode_pdf_download[i]}" target="_blank">
                  <i class="fa fa-file-pdf"></i>${data.IAP_320x450_flexmode_text[i]}
              </a>
              <img  class ="IAP_320x450_flexmode_image${i} ${data.IAP_320x450_flexmode_thumbnail_size[i]} hide" src="${data.IAP_320x450_flexmode_thumbnail[i]}" >
  
            
              <a class="d-xl-flex align-items-xl-center IAP_320x450_flexmode_archive_link${i}"  href="${data.IAP_320x450_flexmode_archive_link[i]}" target="_blank">
                  <i class="fa fa-file-pdf"></i>${data.IAP_320x450_flexmode_archive_text[i]}
              </a>
              <img src = "/showcase/IntellicutGlobal_Archive_PopUp.png"class = "IAP_320x450_flexmode_archive_image${i} hide size">
            
          </td>
      </tr>
        `)
  
        $('.IAP_320x450_flexmode_link'+i).hover(function(){
          $('.IAP_320x450_flexmode_image'+i).css('display','block')
        },function(){
          $('.IAP_320x450_flexmode_image'+i).css('display','none')
        })
  
  
  
        $('.IAP_320x450_flexmode_archive_link'+i).hover(function(){
          $('.IAP_320x450_flexmode_archive_image'+i).css('display','block')
        },function(){
          $('.IAP_320x450_flexmode_archive_image'+i).css('display','none')
        })
        
  }
  
  
  })
//----Intellicut and Intellicut Global Aerocut X Pro 13X19-------------------------------


// $.ajax({
//   method:'GET',
//   url: 'https://api.apispreadsheets.com/data/1009/?accessKey=aa163696b92b10a19b10589d78ed1414&secretKey=9b8ec36f59d21f10ac40c53d63c1920e&dataFormat=column&blank=False',
//   dataType: 'json'
// }).done(function(data){
//   for(let i = 0;i<data.Aerocut_X_Pro_13x19.length;i++){
//       $('#output_aero_x_pro_13x19_output').append(`
//       <tr>
//           <td>
//             <a href="/showcase/downloads/Aerocut X Pro/13x19/${data.Aerocut_X_Pro_13x19[i]}" target="_blank"><i class="fa fa-file-pdf"></i>
//             ${data.Aerocut_X_Pro_13x19[i]}</a> 
//           </td>
//       </tr>
//         `)
//   }
// })
//-----Intellicut and Intellicut Global Aerocut X Pro 12X18----
// $.ajax({
//   method:'GET',
//   url: 'https://api.apispreadsheets.com/data/15439/?accessKey=9e8a7934fa8acbd2d6fdbd563c4bb756&secretKey=55133755e2d0bafe48fe74218a644c7a&dataFormat=column&blank=False',
//   dataType: 'json'
// }).done(function(data){
//   console.log(data)
//   for(let i = 0;i<data.Aerocut_X_Pro_12x18.length;i++){
//       $('#output_aero_x_pro_12x18_output').append(`
//       <tr>
//           <td>
//             <a href="/showcase/downloads/Aerocut X Pro/12x18/${data.Aerocut_X_Pro_12x18[i]}" target="_blank"><i class="fa fa-file-pdf"></i>
//             ${data.Aerocut_X_Pro_12x18[i]}</a> 
//           </td>
//       </tr>
//         `)
//   }
// })

//-----Intellicut and Intellicut Global Aerocut X Pro 11X17----
// $.ajax({
//   method:'GET',
//   url: 'https://api.apispreadsheets.com/data/15443/?accessKey=85a0412b4fa947b345f594003bcc96d6&secretKey=5b3b2cb8f7c05294f5ace8aaa8aa8c02&dataFormat=column&blank=False',
//   dataType: 'json'
// }).done(function(data){
//   console.log(data)
//   for(let i = 0;i<data.Aerocut_X_Pro_11x17.length;i++){
//       $('#output_aero_x_pro_11x17_output').append(`
//       <tr>
//           <td>
//             <a href="/showcase/downloads/Aerocut X Pro/11x17/${data.Aerocut_X_Pro_11x17[i]}" target="_blank"><i class="fa fa-file-pdf"></i>
//             ${data.Aerocut_X_Pro_11x17[i]}</a> 
//           </td>
//       </tr>
//         `)
//   }
// })


//-----Intellicut and Intellicut Global Aerocut X  11X17----
// $.ajax({
//   method:'GET',
//   url: 'https://api.apispreadsheets.com/data/15444/?accessKey=fe8e2ac3c96fca2414c56fa0f8e4148d&secretKey=88d428cc6185f573f858653f05644cc3&dataFormat=column&blank=False',
//   dataType: 'json'
// }).done(function(data){
//   console.log(data)
//   for(let i = 0;i<data.Aerocut_X_11x17.length;i++){
//       $('#output_aero_x_11x17_output').append(`
//       <tr>
//           <td>
//             <a href="/showcase/downloads/Aerocut X/11x17/${data.Aerocut_X_11x17[i]}" target="_blank"><i class="fa fa-file-pdf"></i>
//             ${data.Aerocut_X_11x17[i]}</a> 
//           </td>
//       </tr>
//         `)
//   }
// })


//12x18

// $.ajax({
//   method:'GET',
//   url: 'https://api.apispreadsheets.com/data/15445/?accessKey=a6d621c924528f3647126d91262e35b7&secretKey=460ff4abc1ac4dca141bf63198ceaf7b&dataFormat=column&blank=False',
//   dataType: 'json'
// }).done(function(data){
//   console.log(data)
//   for(let i = 0;i<data.Aerocut_X_12x18.length;i++){
//       $('#output_aero_x_12x18_output').append(`
//       <tr>
//           <td>
//             <a href="/showcase/downloads/Aerocut X/11x17/${data.Aerocut_X_12x18[i]}" target="_blank"><i class="fa fa-file-pdf"></i>
//             ${data.Aerocut_X_12x18[i]}</a> 
//           </td>
//       </tr>
//         `)
//   }
// })

// //-----Intellicut and Intellicut Global Aerocut X  11X17----
// $.ajax({
//   method:'GET',
//   url: 'https://api.apispreadsheets.com/data/15446/?accessKey=baef5b1a1574597ea56d3f760320fa62&secretKey=243efb9b6587b7e663f4cfe728beba81&dataFormat=column&blank=False',
//   dataType: 'json'
// }).done(function(data){
//   console.log(data)
//   for(let i = 0;i<data.Aerocut_X_13x19.length;i++){
//       $('#output_aero_x_13x19_output').append(`
//       <tr>
//           <td>
//             <a href="/showcase/downloads/Aerocut X/13x19/${data.Aerocut_X_13x19[i]}" target="_blank"><i class="fa fa-file-pdf"></i>
//             ${data.Aerocut_X_13x19[i]}</a> 
//           </td>
//       </tr>
//         `)
//   }
// })
