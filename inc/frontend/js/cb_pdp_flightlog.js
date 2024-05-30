(function( $ ) {
	'use strict';
	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 */
	 $("#flight_button").on('click', function(e){
	 		$("#addFlight").removeClass("hidden");
	 		$(".flight_list").addClass("editing");   
	 		$('.Title').toggleClass('editing'); 	
 		 	$('.Heading').toggleClass('editing'); 	 	 	
 		 	$('.Heading').addClass("hidden"); 	 	 	
 		 	$('.Row').addClass("hidden"); 	 	  
	 });	
	 $("#Tow_Altitude").on('blur', function(){
	 	if($("#Tow_Altitude").val() == 'Self' ){
	 		$("#Tow_Pilot").val(' ');	
	 		$("#Tow_Plane").val(' ');	  	 	
	 	}
	 });	 
	 $("#Flight_Type").on('blur', function(){
	 	if($("#Flight_Type").val() == 'AOF' ){
	 		$("#Tow_Altitude").val('0');	
	 		$("#Tow_Altitude").addClass('hidden');	
	 		$("#Notes").val('AOF :');	
	 		$("#Pilot2").val(' ');	
	 		$("#Pilot2").addClass('hidden');	  	  	 	
	 	} else {
	 		$("#Pilot2").removeClass('hidden');	
	 		$("#Tow_Altitude").removeClass('hidden');	
	 	}
	 });	 
	 
 	var $body = $("body");
	var app = app || {};
	app.last = {yearkey: 0, Tow_pilot: "", Tow_Plane: "" };

 	if (sessionStorage['working_Date']){
 		var dateObj = new Date(sessionStorage.getItem('working_Date')+'T00:00:00');
 	}  else {
		var dateObj = new Date() // Gets the current date and time as an object
	}
// 		console.log(sessionStorage.getItem('working_Date'));
// 	console.log(dateObj);
	const formattingOptions = {  // format string for Intl.DateTimeFormat
 		 day: 'numeric',
 		 month: 'numeric', 
 		 year: 'numeric',
 		 timeZone: 'America/NEW_YORK',
	}	
	// get the date in mm/dd/yyyyy form ??!!!!!!
	const pgc_date = new Intl.DateTimeFormat('en-us', formattingOptions).format(dateObj);
	// get a New date object with the previously generated date pattern
	// and spit it out as yyyy-mm-dd. Just to fix javascript from jumping ahead
	// at 8:00PM (EDT) -- this is nuts. 
	app.working_date = (new Date(pgc_date)).toISOString().split('T')[0];		
	$('#editDate').text('Flight Log for: ' +app.working_date);
// Define Flight Model and how to get it. 
	app.Flight = Backbone.GSModel.extend({
//  		url: cloud_base_public_vars.root + 'cloud_base/v1/pdp_flightlog',  
		sync: function( method, model, options ){
    		return Backbone.sync(method, this, jQuery.extend( options, {
      			beforeSend: function (xhr) {
        		xhr.setRequestHeader( 'X-WP-NONCE', cloud_base_public_vars.nonce );
      			},
    			} ));	
    		},	
		initialize: function(){
	
//      		this.on('change:Takeoff', function(){
//      			console.log(this);
//      		});		
		},
// 		getters: {
// 			Takeoff : function () {

//  		},
		setters: {
// 			Takeoff : function(t) {
// 				return t;
// 			},
			FlightEnd : function(t) {		
			 var landing = new Date(); 
			// recreate take off time.
			  var now = new Date();
			  var dd = String(now.getDate()).padStart(2, '0');
			  var mm = String(now.getMonth() + 1).padStart(2, '0'); //January is 0!
			  var yyyy = now.getFullYear();			
		      var today = yyyy + '-' + mm + '-' + dd; 													
			  var launch =  new Date(today+'T'+(this.get('Takeoff')).replace(/-/g,"/"));
			  var temptime = Math.abs(landing.getTime()-launch.getTime())/3.6e6; 
			  var hours = Math.round(temptime *100) / 100 ;			
// 			  this.$el.addClass('landed'); 
			  this.set({ Landing:  landing.toLocaleTimeString('en-US',  {hour12:false}), Time: hours});																
			  return landing.toLocaleTimeString('en-US',  {hour12:false});
			},
			Time : function(t){
				return t
			}
		},

		defaults: {	
			Flight_Type	: "REG",
			flightyear: new Date({timeZone:"America/NEW_YORK"}).getFullYear(),			
			Date: app.working_date,
			Glider: "",
			Pilot1: "",
			Pilot2: "",
			Takeoff	: '00:00:00',
			Landing	: '00:00:00',
			Time	: '0.0',
			Tow_Altitude: "9000",
			Tow_Pilot: ""	,	
			Tow_Plane: "76P",
			Tow_Charge: "999",
			Notes: " "
		},
		wait: true
	});
// Define Flight list collection. 
    app.FlightList= Backbone.Collection.extend({
    	model: app.Flight,
     	url: cloud_base_public_vars.root + 'cloud_base/v1/pdp_flightlog',  
    	comparator: function(Flight){
    			return(-Number(Flight.get("yearkey")));
    		},    		
   	 }) ; 	
   	 
// Define model view	
	app.FlightView = Backbone.View.extend({
	 	template: flighttemplate_pdp,   
	 	initialize: function(){
	 		this.listenTo(this.model, 'change', this.render);
	 	}, 
		tagName: 'div',
		localDivTag: 'div',
        className: 'Row',
		render: function(){	
			this.$el.html( this.template(this.model.toJSON() ) );
			this.renderTime();
			this.$input = this.$('.edit');
 			return this;
		},
		renderTime: function(){
			if ((this.model.get('Takeoff') !== '00:00:00' ) && (this.model.get('Landing') === '00:00:00' )){
 				this.$el.addClass('inflight');
 				this.$el.removeClass('landed');  	
 			} else if ((this.model.get('Takeoff') !== '00:00:00' ) && (this.model.get('Landing') !== '00:00:00' )){
 				this.$el.removeClass('inflight'); 
 				this.$el.addClass('landed'); 	 				 									
 			} else {
				this.$el.removeClass('inflight'); 
 				this.$el.removeClass('landed'); 		 			
 			}
		},
		initialize: function(){
        		this.model.on('change', this.render, this); 	
//        		this.model.bind('change',_.bind(this.render, this));
  		},
		events:{
			'click label' : 'update',
			'click .buttonlaunch' : 'launch_time',
			'click .buttonlanding' : 'landing_time',
			"#flights li a": "setSelectedItem"
		},
   		update: function(){   		   			
			var localmodel = this.model;

 			$("#addorupdate").addClass('editing'); 	
 			$("#addFlight").removeClass("hidden");	
 			
      	    $('.Heading').addClass("hidden"); 	 	 	
 		    $('.Row').addClass("hidden"); 	 	  

 		 	$('.Title').toggleClass('editing'); 	
 		    $('.Heading').toggleClass('editing'); 	
		// populate the form with existing datat from the model. 			
             // NTFS this requires the form id's to be the same as the model id's.
             // we are looping over the form, picking up the id's and then getting the 
             // value of the same id in the model and then loading it back into the form
             //  someone (probably me) is going to hate me in the future.  -dsj
             // this loops over the FORM and looks for a value for each input 
            $(this.localDivTag).children('input').each(function(i, el ){
      		   if(el.type === "checkbox" ){
      		   		if (localmodel.get(el.id) === "1" ){
      		   			$('#'+el.id).prop("checked", true);
      		   		} else {
      		   		    $('#'+el.id).prop("checked", false);
      		   		}
      		   } else {
      		      $('#'+el.id).val(localmodel.get(el.id));
      		   }  
      		});     		
      		$(this.localDivTag).children('select').each(function(i, el ){
				$('#'+el.id).val(localmodel.get(el.id));
      		});
      		$(this.localDivTag).children('textarea').each(function(i, el ){
				$('#'+el.id).val(localmodel.get(el.id));
      		});
		},  
		launch_time: function(){ 
			var launch = new Date();
			this.model.set({Takeoff:  launch.toLocaleTimeString('en-US',  {hour12:false})})	;		
// 			this.$el.addClass('inflight'); 	
// 			this.model.set({Takeoff:  launch.toLocaleTimeString('en-US',  {hour12:false})})	;		
			this.model.save(
//  				{ Takeoff:  launch.toLocaleTimeString('en-US',  {hour12:false})},
				{
 				patch:true,
			    success: function(model, resp, opt) {
//   			       alert('updated takeoff'); 
			    }, 
			    error: function(model, error){
			    	alert('Error: ' + error);
// 			    	console.log(error);
			    },
			    id: this.model.id			
			});

		},
		landing_time: function(){
 			var landing = new Date(); 
// 			// recreate take off time.
// 			  var now = new Date();
// 			  var dd = String(now.getDate()).padStart(2, '0');
// 			  var mm = String(now.getMonth() + 1).padStart(2, '0'); //January is 0!
// 			  var yyyy = now.getFullYear();			
// 		      var today = yyyy + '-' + mm + '-' + dd; 													
// 			var launch =  new Date(today+'T'+(this.model.get('Takeoff')).replace(/-/g,"/"));
// 			var temptime = Math.abs(landing.getTime()-launch.getTime())/3.6e6; 
// 			var hours = Math.round(temptime *100) / 100 ;		

			this.model.set({ FlightEnd:  landing.toLocaleTimeString('en-US',  {hour12:false})});	
 			this.$el.addClass('landed'); 
// 			this.model.set({ Landing:  landing.toLocaleTimeString('en-US',  {hour12:false}), Time: hours});
			this.model.save(
				{
 				patch:true,
			    success: function(model, resp, opt) {
//    			       alert('updated landing'); 
			    }, 
			    error: function(model, error){
// 			    	alert('Error: ' + error);
// 			    	console.log(error);
			    }			
			});	
 		},
 		setSelectedFlight: function(){
 		//extract the model ID from the event.currentTarget
 			var selectedFlight = $(event.currentTarget);
 		 	this.selectedFlightId =selectedFlight[0].childNodes[1].innerText;		
        	return false;
 		}
	});
	app.FlightLogView =  Backbone.View.extend({ 
		el: '#eflights', 
		localDivTag: 'div',
// 		localRowTag: '#flight_table div',	 		 	 	        
      	initialize: function(){
      	 var self=this;
         this.collection = new app.FlightList();
//          var fetch_string = '{reset:true, wait: true , data: $.param({start: ' +app.working_date +  '})}';
         this.collection.fetch({reset:true, wait: true , data: $.param({start: app.working_date })});      	 
//     	 this.collection.fetch({reset:true, wait: true });    		  
         this.listenTo(this.collection, 'reset', this.render);
         this.listenTo(this.collection, 'add', this.render_add);          
         $( "#datepicker" ).datepicker({
         	dateFormat: 'yy-mm-dd',
         	onSelect: function (dateText, inst) {
				$('.Row').remove();
         		app.working_date= dateText;
         		sessionStorage.setItem('working_Date', app.working_date);
//          		self.collection.reset(); 	
         		self.collection.fetch({ wait: true, reset:true, data: $.param({start: app.working_date })});
//           		self.collection.reset(results); 				
         		$('#editDate').text('Edit Flight Log for: ' +app.working_date).css("color", "yellow");
         	}         
         });    
//         this.render();
      },
      render: function(){

      	this.collection.each(function(item){	      	
	 		this.renderItem(item);    	
      	}, this );
      	 $('#flightCount').text('Flights: ' +this.collection.length);
      },
      render_add: function(){
   		this.$('.Row').html('');
      	this.collection.each(function(item){	
  			this.renderItem(item);    	
      	}, this );
      },
      clearList: function() { this.$('.Row').html('') },
      events:{
      	'click #add' : 'addItem',
      	'click #update' : 'updateItem',
      	'click #cancel' : 'cancelItem',
      	'click #reset_time_landing' : 'resetLanding',
      	'click #reset_time_launch'  : 'resetTakeoff'
      },
      addItem: function(e){
        e.preventDefault();
      	var formData ={};
      	// grab all of the input fields
 		$(this.localDivTag).children('input').each(function(i, el ){
		  if($(el).val() != ''){
		  	if($(el).hasClass('checked_class')){
		  		formData[el.id]=($(el).is(":checked")? true : false );
		  	} else {
        		formData[el.id] = $(el).val();
        	}
      	  } 
      	});
      	//grab all of the <select> fields 
      	$(this.localDivTag).children('select').each(function(i, el ){
      		if($(el).val() != ''){
      			formData[el.id] = $(el).val();
      		}
      	});
      	$(this.localDivTag).children('textarea').each(function(i, el ){
      		if($(el).val() != ''){
      			formData[el.id] = $(el).val();
      		}
      	});   
      	var model_count =  this.collection.length;
      	if(model_count != 0 ){
      		var max_flight = this.collection.max('yearkey');
      		var max_key = Number(max_flight.get('yearkey'));
      	
      	} else {
      		var max_key =  Number(cloud_base_public_vars.last_yearkey );
      	} 
      	if(formData['Pilot1'] == ' ') {
      		alert('Pilot 1 can not be blank');
      	} else {    	      	   	
      		formData['yearkey'] = max_key+1;     		
      		if(typeof formData['Takeoff'] === 'undefined' )  	{
   				formData['Takeoff'] = '00:00:00';	
   			}
  			if(typeof formData['Landing'] === 'undefined' )  	{
   				formData['Landing'] = '00:00:00';	
   			}
   			if ( (formData['Takeoff'] != '00:00:00' )  &&  ( formData['Landing'] != '00:00:00') ){
				var now = new Date();
				var dd = String(now.getDate()).padStart(2, '0');
				var mm = String(now.getMonth() + 1).padStart(2, '0'); //January is 0!
				var yyyy = now.getFullYear();			
				var today = yyyy + '-' + mm + '-' + dd;
				var launch =  new Date(today+'T'+(formData['Takeoff']).replace(/-/g,"/"));
				var landing =  new Date(today+'T'+(formData['Landing']).replace(/-/g,"/"));
				var temptime = Math.abs(landing.getTime()-launch.getTime())/3.6e6; 
				var hours = Math.round(temptime *100) / 100 ;      	
      			formData['Time'] =  hours;	   			
   			} else {
   				formData['Time'] = ' ';	 		
   			}
      		formData['Date'] = app.working_date;
      		formData['pilot_is_number'] = 1; 	 
 			$body.addClass("loading");       			 	      		
      		var new_model = this.collection.create( formData, 
      			{
       			wait: true,
      			success: function(model, resp, opt) {
      			$body.removeClass("loading");     			
//      				new_model.id = resp;
//      				console.log(new_model);
//      				alert('success');
      			},
      			error: function(model, resp, opt) {
      				alert('Add new record failed.');
      				$body.removeClass("loading");
      			},   	
      		});     	
 		$('#flightCount').text('Flights: ' +this.collection.length);
 // clean out the form:		
		this.cancelItem(e);
		return new_model; //??? 
		}
      },
      updateItem: function(e){     	
		e.preventDefault();		
 		var formData ={};
		// grab all of the input fields from the form. 
  		$(this.localDivTag).children('input').each(function(i, el ){
//  		$('#addFlight div').children('input').each(function(i, el ){ 	
 		 if($(el).val() != ''){
		  	if($(el).hasClass('checked_class')){
		  		formData[el.id]=($(el).is(":checked")? true : false );
		  	} else {
        		formData[el.id] = $(el).val();
        	}
      	  } 		
      	});
      	//grab all of the <select> fields 
      	$(this.localDivTag).children('select').each(function(i, el ){
      	  if($(el).val() != ''){
      		formData[el.id] = $(el).val();
      	  }
      	});
      	      	 				
      	$(this.localDivTag).children('textarea').each(function(i, el ){
      	  if($(el).val() != ''){
      		formData[el.id] = $(el).val();
      	  }
      	});
      	if (typeof formData.id === 'undefined'){
      		var updateModel = this.collection.findWhere({"yearkey": Number(formData.yearkey)});  
      	} else {
      		var updateModel = this.collection.get(formData.id);  
      	}
     	var old_takeoff = updateModel.get('Takeoff');
   		var old_landing = updateModel.get('Landing');  	
   		if(typeof formData['Takeoff'] === 'undefined' )  	{
   			formData['Takeoff'] = '00:00:00';	
   		}
  		if(typeof formData['Landing'] === 'undefined' )  	{
   			formData['Landing'] = '00:00:00';	
   		}
   		if ( (formData['Takeoff'] != '00:00:00' )  &&  ( formData['Landing'] != '00:00:00') ){
			var now = new Date();
			var dd = String(now.getDate()).padStart(2, '0');
			var mm = String(now.getMonth() + 1).padStart(2, '0'); //January is 0!
			var yyyy = now.getFullYear();			
			var today = yyyy + '-' + mm + '-' + dd;
			var launch =  new Date(today+'T'+(formData['Takeoff']).replace(/-/g,"/"));
			var landing =  new Date(today+'T'+(formData['Landing']).replace(/-/g,"/"));
			var temptime = Math.abs(landing.getTime()-launch.getTime())/3.6e6; 
			var hours = Math.round(temptime *100) / 100 ;      	
      		formData['Time'] =  hours;	   			
   		} else {
   			formData['Time'] = '00:00:00';	 		
   		}

        updateModel.save(formData, {
        	wait: false,
        	patch:true,
        	error: function(model, response, error){
      				var mresult= JSON.parse(response.responseText);     	
      				alert(mresult["message"])},
      		success: function(){
//       		   			this.$el.removeClass('landed');   
//       				var mresult= JSON.parse(response.responseText);     	
      				}        	       
        });         
// clean out the form:
		this.cancelItem(e); 
		$("#addorupdate").removeClass('editing');	
      	},
      cancelItem: function(e){
      	$(this.localDivTag).children('input').each(function(i, el ){
				$('#'+el.id).val('');
      	}); 
      	// don't clean out these fields       
      	$(this.localDivTag).children('select').each(function(i, el ){
      		if('#'+el.id == '#Tow_Pilot' || '#'+el.id == '#Tow_Plane' || '#'+el.id == '#Flight_Type' ){
      			return;
      		}
			$('#'+el.id).val('');
      	});         	
      	$(this.localDivTag).children('textarea').each(function(i, el ){
				$('#'+el.id).val('');
      	});  
      	$("#addorupdate").removeClass('editing');		    
      	$("#addFlight").addClass("hidden");  
      	 
      	 $('.Heading').removeClass("hidden"); 	 	 	
 		 $('.Row').removeClass("hidden"); 	 	  

      	 $('.Title').toggleClass('editing'); 	
 		 $('.Heading').toggleClass('editing'); 	 		         		    	
      },
      resetLanding : function(e){
     	 	$('#Landing').val(null);
     	 	$('#Time').val(null);	
      	},
      resetTakeoff: function(e){
      		$('#Takeoff').val(null);
      		$('#Time').val(null);	
      	},
      renderItem: function(item){    
       	    var itemView = new app.FlightView({ model: item});
      		this.$el.append( itemView.render().el);     	
         }
	});
	new app.FlightLogView();
})( jQuery );
