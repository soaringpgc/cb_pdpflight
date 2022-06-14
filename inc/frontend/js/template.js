<-- Template -->
// Aircraft Types 
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

<-- End template ->