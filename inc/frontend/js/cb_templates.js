// templates for public scripts. 
// loaded with wp_register_script  & wp_enque_script see class-cloud-base-public.php
//

var flighttemplate_pdp = _.template(` 
	  <td  class="hidden" id="id" ><%= id %></td>  
      <td class="fl_Cell0"> <label > <%=  yearkey %></label></td>
      <td class="fl_Cell0a"> <label ><%=  Flight_Type %></label></td> 
      <td class="fl_Cell0"> <label >  <%=  Glider %></label></td> 
      <td class="fl_Cell0a"> <label > <%= Pilot1 %></label>  </td>  
      <td class="fl_Cell0"> <label > <%= Pilot2 %></label>  </td>   	 
      <td id="ft<%= id %>" class="fl_Cell0a"> 
      	<div  id="button"> 
     	 	<button id="launch"  class="viewstart buttonlaunch  "></button>
  			<button id="landing" class="viewstop  buttonlanding "></button>
     	</div  > 
      </td> 
      <td class="fl_Cell0">       		
        	<label class="viewstop"> <%=  Takeoff %> </label >
        	<label class="hidden"> <%=  Landing %> </label >    
        	<label class="time"> <%=  Time %> </label >    
      </td>       
      <td class="fl_Cell0a"> <label ><%= Tow_Altitude %></label>  </td>  
      <td class="fl_Cell0"> <label ><%= Tow_Pilot %></label>   	</td> 			
      <td class="fl_Cell0a"> <label ><%= Tow_Plane %></label>   </td> 
      <td class="fl_Cell0"> <label ><%= Tow_Charge  %></label>  </td>     
`);


