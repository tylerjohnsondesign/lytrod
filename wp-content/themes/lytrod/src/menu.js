
// alert()
let menuItems = document.querySelectorAll('.menu-item-has-children > a')
   menuItems.forEach((item)=>{
      item.insertAdjacentHTML('afterend','<i class="fas fa-chevron-down lytroddownmenu"></i>')
   })
   // 

   let menus = document.querySelectorAll('.lytroddownmenu')

   menus.forEach((menu)=>{
      menu.addEventListener('click',(e)=>{
   
         let tl = gsap.timeline();
         if(!e.target.classList.contains('active')){
            e.target.classList.add('active')
            tl.fromTo(e.target.nextElementSibling,0.5,{display:'block',height:'0'},{minHeight:'20vh'})
               .to(e.target,0.5,{rotate:'180deg'},'-=0.5')
               .fromTo(e.target.nextElementSibling,0.5,{opacity:0},{opacity:1})
         }else{
            e.target.classList.remove('active')
   
            tl.fromTo(e.target.nextElementSibling,0.5,{opacity:1},{opacity:0})
              .fromTo(e.target.nextElementSibling,0.5,{minHeight:'20vh'},{minHeight:'0vh'})
              .to(e.target,0.5,{rotate:'0deg'},'-=0.5')
            
         }
      })
   })

   const burger = document.querySelector(".burger");
   function navToggle(e) {
      if (!e.target.classList.contains("active")) {
        e.target.classList.add("active");
        gsap.to(".line1", 0.5, { rotate: "45", y: 5, background: "#0d4e96" });
        gsap.to(".line2", 0.5, { rotate: "-45", y: -5, background: "#0d4e96" });
        gsap.to(".nav-bar", 1, { clipPath: "circle(2500px at 100% -10%)" });
        document.body.classList.add("hide");
      } else {
        e.target.classList.remove("active");
        gsap.to(".line1", 0.5, { rotate: "0", y: 0, background: "black" });
        gsap.to(".line2", 0.5, { rotate: "0", y: 0, background: "black" });
        gsap.to(".nav-bar", 1, { clipPath: "circle(50px at 100% -10%)" });
        document.body.classList.remove("hide");
      }
    }
    burger.addEventListener("click", navToggle);
