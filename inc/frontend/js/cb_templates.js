// templates for public scripts. 
// loaded with wp_register_script  & wp_enque_script see class-cloud-base-public.php
//

var flighttemplate_pdp = _.template(` 
	  <td  class="hidden" id="id" ><%= id %></td>  
      <td class="cb_Cellsa"> <label > <%=  yearkey %></label></td>
      <td class="cb_Cells"> <label ><%=  Flight_Type %></label></td> 
      <td class="cb_Cellsa"> <label >  <%=  Glider %></label></td> 
      <td class="cb_Cell"> <label > <%= Pilot1 %></label>  </td>  
      <td class="cb_Cella"> <label > <%= Pilot2 %></label>  </td>   	 
      <td id="ft<%= id %>" class="cb_Cellb"> 
      	<div  id="button"> 
     	 	<button id="launch"  class="viewstart buttonlaunch  "></button>
  			<button id="landing" class="viewstop  buttonlanding "></button>
     	</div  > 
      </td> 
      <td class="cb_Cellsa">       		
        	<label class="viewstop"> <%=  Takeoff %> </label >
        	<label class="hidden"> <%=  Landing %> </label >    
        	<label class="time"> <%=  Time %> </label >    
      </td>       
      <td class="cb_Cells"> <label ><%= Tow_Altitude %></label>  </td>  
      <td class="cb_Cella"> <label ><%= Tow_Pilot %></label>   	</td> 			
      <td class="cb_Cells"> <label ><%= Tow_Plane %></label>   </td> 
      <td class="cb_Cellsa"> <label ><%= Tow_Charge  %></label>  </td>     
`);


