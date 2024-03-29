// templates for public scripts. 
// loaded with wp_register_script  & wp_enque_script see class-cloud-base-public.php
//

var flighttemplate_pdp = _.template(`
     <div class="hidden"><%= id %></div>
     <label class="hidden"><%=  Flight_Type %></label>
     <label class="fl_Cell0"> <%=  yearkey %></label>
     <label class="fl_Cell0a"><%=  Flight_Type %></label>
     <label class="fl_Cell0a">  <%=  Glider %></label>
     <label class="fl_Cell"> <%= Pilot1 %></label>   
     <label class="fl_Cell"> <%= Pilot2 %></label>     	  	 
     <div class="fl_Cell0" id="button"> 
     	 <button id="launch"  class="viewstart buttonlaunch "></button>
  		 <button id="landing" class="viewstop  buttonlanding"></button>
     </div  >
     <div class="fl_Cell0a" >
     <label class="viewstop"> <%=  Takeoff %> </label >
     <label class="hidden"> <%=  Landing %> </label >
     <label class="time"> <%=  Time %> </label >
     </div>
     
     <label class="fl_Cell0"><%= Tow_Altitude %></label>   
     <label class="hidden"><%= Tow_Pilot %></label>   				
     <label class="fl_Cell0a"><%=  Tow_Plane %></label>   
     <label class="hidden"><%= Notes %></label>   			
`);

