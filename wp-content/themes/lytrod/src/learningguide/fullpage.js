new fullpage('#fullpage', {
	//options here
	autoScrolling:true,
    scrollHorizontally: true,
    autoScrolling: true,
    navigation: true,
    anchors:['Intellicut', 'intellicutglobal', 'VRCut','Installation','DocumentLayout'],
    navigationTooltips: ['INTELLICUT','INTELLICUT GLOBAL', 'VRCUT', 'INSTALLATION', 'DOCUMENTLAYOUT'],
    showActiveTooltip: true,
    slidesNavigation: true,
    scrollHorizontally: true,
 

 
  onLeave: (origin, destination, direction) => {
    
    const section = destination.index;
    //  alert(section);
    const menu1 = document.querySelector("#first");
    const menu2 = document.querySelector("#second");
    const menu3 = document.querySelector("#three");
    const menu4 = document.querySelector("#fourth");
    const menu5 = document.querySelector("#fifth");
  if(destination.index === 0){
      menu1.style.background = "white";
      menu2.style.background = "rgba(128, 128, 128, 0)";
      menu3.style.background = "rgba(128, 128, 128, 0)";
      menu4.style.background = "rgba(128, 128, 128, 0)";
      menu5.style.background = "rgba(128, 128, 128, 0)";    
  }
if(destination.index === 1){
    menu2.style.background = "white";
    menu1.style.background = "rgba(128, 128, 128, 0)";
    menu3.style.background = "rgba(128, 128, 128, 0)";
    menu4.style.background = "rgba(128, 128, 128, 0)";
    menu5.style.background = "rgba(128, 128, 128, 0)";    
}else if(destination.index === 2){
    menu1.style.background = "rgba(128, 128, 128, 0)";
    menu3.style.background = "white";
    menu2.style.background = "rgba(128, 128, 128, 0)";
    menu4.style.background = "rgba(128, 128, 128, 0)";  
    menu5.style.background = "rgba(128, 128, 128, 0)";    
}else if(destination.index === 3){
    menu1.style.background = "rgba(128, 128, 128, 0)";
    menu2.style.background = "rgba(128, 128, 128, 0)";
    menu4.style.background = "white";
    menu3.style.background = "rgba(128, 128, 128, 0)";    
    menu5.style.background = "rgba(128, 128, 128, 0)";    
}else if(destination.index === 4){
  menu1.style.background = "rgba(128, 128, 128, 0)";
  menu2.style.background = "rgba(128, 128, 128, 0)";
  menu4.style.background = "rgba(128, 128, 128, 0)";
  menu3.style.background = "rgba(128, 128, 128, 0)";    
  menu5.style.background = "white";    
  
}




  } 


});
