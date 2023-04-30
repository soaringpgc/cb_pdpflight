// templates for public scripts. 
// loaded with wp_register_script  & wp_enque_script see class-cloud-base-public.php
//

// flight 
var cb_pdp_flighttemplate = _.template(`
     <div class="edit"><%= id %></div>
     <label class="Cell0"><%=  flight_number %></label>
     <label class="Cell"><%=  glider %></label>
     <label class="Cell2"><%=  p_last_name %>, <%=p_first_name %></label>     	 
     <div class="Cell0" id="button"> 
     	 <button id="launch"  class="viewstart buttonlaunch "></button>
  		 <button id="landing" class="viewstop  buttonlanding"></button>
     </div  >
     <label class="Cell"> 
     	  <div ><%=  start_display_time %></div>

     </label >
     	 					
     	 <label class="Cell"><%=  towplane %></label>   
     	 <div class="edit" > <%= tow_pilot_id %></div>	 
     	 <div class="edit" > <%= tow_plane_id %></div>	
`);

