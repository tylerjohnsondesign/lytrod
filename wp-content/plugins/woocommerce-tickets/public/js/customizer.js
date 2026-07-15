




  
 
    document.addEventListener('DOMContentLoaded', (event) => {
        

        setTimeout(() => {
       
            if(window.location.search.match(/customizerdash=yes/) == 'customizerdash=yes'){
                document.querySelector("body > div.wp-full-overlay.expanded.preview-desktop").classList.add('section-open')
                document.querySelector("#sub-accordion-section-dashboard").classList.add('open')
            }
            if(window.location.search.match(/customizerdashadmin=yes/) == 'customizerdashadmin=yes'){
                document.querySelector("body > div.wp-full-overlay.expanded.preview-desktop").classList.add('section-open')
                document.querySelector("#sub-accordion-section-dashboard_admin").classList.add('open')
            }

            if(window.location.search.match(/customizerdashadmin=yes/) == 'customizerdashadmin=yes'){
                document.querySelector("body > div.wp-full-overlay.expanded.preview-desktop").classList.add('section-open')
                document.querySelector("#sub-accordion-section-dashboard_admin").classList.add('open')
            }


            if(window.location.search.match(/singleticket/) == 'singleticket'){
                document.querySelector("body > div.wp-full-overlay.expanded.preview-desktop").classList.add('section-open')
                document.querySelector("#sub-accordion-section-single_ticket").classList.add('open')
            }
         
         

        }, 1000);
    
    })


    