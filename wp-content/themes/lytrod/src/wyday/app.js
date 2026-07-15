
// // alert()
// let $ = (val)=> document.querySelector(val)

// //add vrcut post data
// $('#add_vrcut_post_data').addEventListener('click',function(e){

//     e.preventDefault();
//     $('.spinner-loader').style.display = 'block'
//     $('.spinner-message').style.display = 'block'

//     var fd = new FormData();
//     fd.append('action', 'get_vrcut_wyday_from_api');  
//     axios({
//         method: "post",
//         url: backend_js.root_url +'/wp-admin/admin-ajax.php',
//         data: fd,
//         headers: { 
//             "Content-Type": "multipart/form-data" 
//         },
//         })
//         .then(function (response) {
//             //handle success
//             console.log(response)
//             $('.spinner-loader').style.display = 'none'
//             $('.spinner-message').style.display = 'none'
//         })
//         .catch(function (response) {
//             //handle error
//             console.log(response);
//         });

// })


// //add Intellicut Global Post Data

// $('#add_intellicut_global_post_data').addEventListener('click',function(e){

//     e.preventDefault();

//     $('.spinner-loader').style.display = 'block'
//     $('.spinner-message').style.display = 'block'
//     var fd = new FormData();
//     fd.append('action', 'get_intellicut_global_wyday_from_api');  
//     axios({
//         method: "post",
//         url: backend_js.root_url +'/wp-admin/admin-ajax.php',
//         data: fd,
//         headers: { 
//             "Content-Type": "multipart/form-data" 
//         },
//         })
//         .then(function (response) {
//             //handle success
//             console.log(response)
//             $('.spinner-loader').style.display = 'none'
//             $('.spinner-message').style.display = 'none'

//         })
//         .catch(function (response) {
//             //handle error
//             console.log(response);
//         });

// })


// //add Intellicut Post Data

// $('#add_intellicut_post_data').addEventListener('click',function(e){

//     e.preventDefault();

//     $('.spinner-loader').style.display = 'block'
//     $('.spinner-message').style.display = 'block'
//     var fd = new FormData();
//     fd.append('action', 'get_intellicut_wyday_from_api');  
//     axios({
//         method: "post",
//         url: backend_js.root_url +'/wp-admin/admin-ajax.php',
//         data: fd,
//         headers: { 
//             "Content-Type": "multipart/form-data" 
//         },
//         })
//         .then(function (response) {
//             //handle success
//             console.log(response)
//             $('.spinner-loader').style.display = 'none'
//             $('.spinner-message').style.display = 'none'

//         })
//         .catch(function (response) {
//             //handle error
//             console.log(response);
//         });

// })



// //clear all vrcut data
// $('#clear_all_vrcut').addEventListener('click',function(e){
//     e.preventDefault();
//     $('.spinner-loader').style.display = 'block'
//     $('.spinner-message').style.display = 'block'
//     var fd = new FormData();
//         fd.append('action', 'delete_vrcut_posts');  
//     axios({
//         method: "post",
//         url: backend_js.root_url +'/wp-admin/admin-ajax.php',
//         data: fd,
//         headers: { 
//             "Content-Type": "multipart/form-data" 
//         },
//         })
//         .then(function (response) {
//             //handle success
//             console.log(response)

//             $('.spinner-loader').style.display = 'none'
//             $('.spinner-message').style.display = 'none'
//         })
//         .catch(function (response) {
//             //handle error
//             console.log(response);
//         });
// })

// //clear all intellicut 

// $('#clear_all_intellicut').addEventListener('click',function(e){
//     e.preventDefault();
//     $('.spinner-loader').style.display = 'block'
//     $('.spinner-message').style.display = 'block'
//     var fd = new FormData();
//         fd.append('action', 'delete_intellicut_posts');  
//     axios({
//         method: "post",
//         url: backend_js.root_url +'/wp-admin/admin-ajax.php',
//         data: fd,
//         headers: { 
//             "Content-Type": "multipart/form-data" 
//         },
//         })
//         .then(function (response) {
//             //handle success
//             console.log(response)

//             $('.spinner-loader').style.display = 'none'
//             $('.spinner-message').style.display = 'none'
//         })
//         .catch(function (response) {
//             //handle error
//             console.log(response);
//         });
// })

// // clear all intellicut global
// $('#clear_all_intellicut_global').addEventListener('click',function(e){
//     e.preventDefault();
//     $('.spinner-loader').style.display = 'block'
//     $('.spinner-message').style.display = 'block'
//     var fd = new FormData();
//         fd.append('action', 'delete_intellicut_global_posts');  
//     axios({
//         method: "post",
//         url: backend_js.root_url +'/wp-admin/admin-ajax.php',
//         data: fd,
//         headers: { 
//             "Content-Type": "multipart/form-data" 
//         },
//         })
//         .then(function (response) {
//             //handle success
//             console.log(response)

//             $('.spinner-loader').style.display = 'none'
//             $('.spinner-message').style.display = 'none'
//         })
//         .catch(function (response) {
//             //handle error
//             console.log(response);
//         });
// })

// //show and hide custom post types

// /*
//     let vrcutPost = $('#show_vrcut_post_data').value
//     let intellicutPost = $('#show_intellicut_data').value
//     let intellicutGlobalPost = $('#show_intellicut_global_data').value

//     let postData = {
//         'vrcut':vrcutPost,
//         'intellicut':intellicutPost,
//         'intellicutGlobal':intellicutGlobalPost
//     }

//     console.log(postData)
// */

// $('#handleWyday').addEventListener('submit',function(e){
//     e.preventDefault();
//     $('.spinner-loader').style.display = 'block'
//     $('.spinner-message').style.display = 'block'

//         var fd = new FormData();
//         fd.append('yes_no_vrcut',document.querySelector("#show_vrcut_post_data").value)
//         fd.append('yes_no_intellicut',document.querySelector("#show_intellicut_data").value)
//         fd.append('yes_no_intellicut_global',document.querySelector("#show_intellicut_global_data").value)
//         fd.append('action', 'show_hide_post_type');  
  
//     axios({
//         method: "post",
//         url: backend_js.root_url +'/wp-admin/admin-ajax.php',
//         data: fd,
//         headers: { 
//             "Content-Type": "multipart/form-data" 
//         },
//         })
//         .then(function (response) {
//             //handle success
//             console.log(response)
//             location.reload();
//             $('.spinner-loader').style.display = 'none'
//             $('.spinner-message').style.display = 'none'
//         })
//         .catch(function (response) {
//             //handle error
//             console.log(response);
//         });
// })