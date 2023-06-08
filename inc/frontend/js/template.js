// templates for public scripts. 
// loaded with wp_register_script  & wp_enque_script see class-cloud-base-public.php
//

var flighttemplate_pdp = _.template(`
     <div class="hidden"><%= id %></div>
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
     <label class="viewstop"> <%=  Takeoff %> </label >
     <label class="hidden"> <%=  Landing %> </label >
     <label class="time"> <%=  Time %> </label >
     </div>
     
     <label class="Cell2"><%= Tow_Altitude %></label>   
     <label class="hidden"><%= Tow_Pilot %></label>   				
     <label class="Cell"><%=  Tow_Plane %></label>   
     <label class="hidden"><%= Notes %></label>   			
`);

