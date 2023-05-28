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

var flighttemplate_pdp = _.template(`
     <div class="edit"><%= id %></div>
     <label class="hidden"><%=  Flight_Type %></label>
     <label class="Cell0"> <%=  yearkey %></label>
     <label class="Cell">  <%=  Glider %></label>
     <label class="Cell2"> <%= Pilot1 %></label>   
     <label class="hidden"> <%= Pilot2 %></label>     	  	 
     <div class="Cell0" id="button"> 
     	 <button id="launch"  class="viewstart buttonlaunch "></button>
  		 <button id="landing" class="viewstop  buttonlanding"></button>
     </div  >
     <div class="Cell" >
     <label class="timestart"> <%=  Takeoff %> </label >
     <label class="hidden"> <%=  Landing %> </label >
     <label class="time"> <%=  Time %> </label >
     </div>
     
     <label class="Cell2"><%= Tow_Altitude %></label>   
     <label class="hidden"><%= Tow_Pilot %></label>   				
     <label class="Cell"><%=  Tow_Plane %></label>   
     <label class="hidden"><%= Notes %></label>   				
	
`);


//      <label class="Cell"> <%=  Takeoff %> </label >
//      <label class="hidden"> <%=  Landing %> </label >
//      <label class="hidden"> <%=  Time %> </label >
// 
