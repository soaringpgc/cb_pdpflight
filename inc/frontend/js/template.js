
// Aircraft Types 
/* 
  var flightTemplate = _.template(`
   <div class="hiding" > <%= id %></div>
      <div >
   	 <label class="Cell"><%=  glider   %></label>
     <label class="Cell"><%=  type     %> </label>
     <label class="Cell"><%=  pilot1   %> </label>
     <label class="Cell"><%=  pilot2   %> </label>
     <label class="Cell"><%=  Takeoff  %> </label>
     <label class="Cell"><%=  Landing  %> </label>
     <label class="Cell"><%=  Alitude  %> </label>
     <label class="Cell"><%=  Tug      %> </label>
     <label class="Cell"><%=  TowPilot %> </label>
     <label class="Cell"><%=  Charge %> </label>
     <label class="Cell"><%=  Notes    %> </label>
     <div class="Cell"><button class="Enter" ">Enter</button></div>
   </div >
`);
 */
var pdp = pdp || {};

pdp.flightTemplate = _.template(`
   <div id="key" class="pdp-tableCell"><%=Key%></div>
   <div id="glider" class="pdp-tableCell "><%=Glider%></div> 
   <div id="pilot" class="pdp-tableCell "><%=Pilot1%></div>
   <div id="cfig" class="pdp-tableCell "><%=Pilot2%></div>
   <div id="altitude" class="pdp-tableCell"><%=tow_alitude%></div> 
   <div id="towpilot" class="pdp-tableCell "><%=tow_pilot%></div>		   
   <div id="towplane" class="pdp-tableCell "><%=tow_plane%></div>
   <div id="start" class="pdp-tableCell pdp-time"><%=start%></div>
   <div id="stop" class="pdp-tableCell pdp-time"><%=stop%></div>
   <div id="notes" class="pdp-note"><%=Notes%></div>    

`);		    

var archiveTemplate = _.template(` 
   <div id="id" class="tableCell pdp-narrow"><%=id%></div>
   <div id="glider_id" class="hidden"><%=glider_id%></div>
   <div id="glider" class="pdp-tableCell pdp-narrow"><%=glider%></div>
   <div id="pilot_id" class="hidden"><%=pilot_id%></div>
   <div id="pilot" class="pdp-tableCell pdp-name"><%=pilot%></div>
   <div id="cfig_id" class="hidden"><%=cfig_id%></div>
   <div id="cfig" class="pdp-tableCell pdp-name"><%=cfig%></div>
   <div id="altitude_id" class="hidden"><%=altitude_id%></div>
   <div id="altitude" class="pdp-tableCell"><%=altitude%></div>
   <div id="towpilot_id" class="hidden"><%=towpilot%></div>
   <div id="towpilot" class="pdp-tableCell pdp-name"><%=towpilot%></div>
   <div id="towplane_id" class="hidden"><%=towplane%></div>
   <div id="towplane" class="pdp-tableCell pdp-narrow"><%=towplane%></div>
   <div id="duration" class="pdp-tableCell pdp-time"><%=start%></div>
   <div id="notes" class="hidden"><%=towplane%></div>    
`);	  

//<-- End template ->