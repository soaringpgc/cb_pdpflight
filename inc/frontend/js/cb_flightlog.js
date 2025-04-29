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
	 var app = app || {};
	 if (Time === undefined){
  		var Time ="0";
  	 }
	 $("#flight_button").on('click', function(e){
	 		$('#addFlight').addClass("edit"); 	
	 		$('#flight_log_table').addClass("edit"); 	 	  
	 });	
	  $("#reload").on('click', function(e){
	  		window.location = window.location.pathname;
	 });	
	 $("#Tow_Altitude").on('blur', function(){ // if it is self launch set TP & Tp to blank
	 	if($("#Tow_Altitude").val() == 'Self' ){
	 		$("#Tow_Pilot").val(' ');	
	 		$("#Tow_Plane").val(' ');	  	 	
	 	}
	 });	 
	 $("#Flight_Type").on('blur', function(){ // is AOF set altitude to 0 
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
	app.last = {yearkey: 0, Tow_pilot: "", Tow_Plane: "" };
	var dateObj = new Date() // Gets the current date and time as an object
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
// backbone model
	 app.Flight = Backbone.Model.extend({
//  		url:  specified in collection. 
 		sync: function( method, model, options ){
    		return Backbone.sync(method, this, jQuery.extend( options, {
      			beforeSend: function (xhr) {
        		xhr.setRequestHeader( 'X-WP-NONCE', cloud_base_public_vars.nonce );
      			},
    		} ));	
      	},	
	});
// backbone collection
	 app.FlightList= Backbone.Collection.extend({
    	model: app.Flight,
     	url: cloud_base_public_vars.root + 'cloud_base/v1/pdp_flightlog',  
//  			url: 'https://pgctest.local:8890/wp-json/cloud_base/v1/pdp_flightlog',
			sync: function( method, model, options ){
    			return Backbone.sync(method, this, jQuery.extend( options, {
      			beforeSend: function (xhr) {
         		xhr.setRequestHeader( 'X-WP-NONCE', cloud_base_public_vars.nonce );
      			},
    			} ));	
    		},	
    		comparator: function(Flight){  // reverse order.  and push completed flight to the bottom. 
    			return(  Flight.get("Landing") != "00:00:00", -Number(Flight.get("yearkey")));  // we want newest flight on top. 
    		},
   	 }) ; 		
// backbone model views
	// model view 
	app.FlightView = Backbone.View.extend({  // Model View. 
	 	template: flighttemplate_pdp,  
	 	initialize: function(){		
       		this.listenTo(this.model, 'change',  this.render);       	
//          	this.listenTo(this.model, 'change:Takeoff',  this.renderTime);
//          	this.listenTo(this.model, 'change:Landing',  this.renderTime);      		
  		}, 
		tagName: 'tr',
		localDivTag: 'div',
        className: 'Row',
		render: function(){		
      		if( typeof this.model.get("Time") !== 'undefined'){
			  this.$el.html( this.template(this.model.toJSON() ) );
			  this.renderTime();
			}
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
 			return this; 	
		},
		events:{
			'click label' : 'update',
			'click .buttonlaunch' : 'launch_time',
			'click .buttonlanding' : 'landing_time',
		},
   		update: function(){   		   			
			var localmodel = this.model;
 			$('#addFlight #id').val(localmodel.id);  // insure id is saved in the form. 
 			$('#addFlight #yearkey').val(localmodel.get("yearkey"));  // insure id is saved in the form. 
 			$('#addFlight #Taleoff').val(localmodel.get("Takeoff"));  // insure id is saved in the form. 
 			$("#addorupdate").addClass('editing'); 	
	 		$('#addFlight').addClass("edit"); 	
	 		$('#flight_log_table').addClass("edit"); 	
            $(this.localDivTag).children('input').each(function(i, el ){
// we do not have checkboxes 
//       		   if(el.type === "checkbox" ){
//       		   		if (localmodel.get(el.id) === "1" ){
//       		   			$('#'+el.id).prop("checked", true);
//       		   		} else {
//       		   		    $('#'+el.id).prop("checked", false);
//       		   		}
//       		   } else {
      		      $('#'+el.id).val(localmodel.get(el.id));
//       		   }  
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
			this.model.save(
				{
 				patch:true,
			    success: function(model, resp, options) {
// 			    	off_line(options);  		
//   			       alert('updated takeoff'); 
			    }, 
			    error: function(model, resp, options){
//  			    	off_line(options);  		
			    	alert('Error: ' + error);
// 			    	console.log(resp);
			    },
			    id: this.model.id			
			});          	  
			return this; 
		},
		landing_time: function(){
 			var landing = new Date(); 
			// recreate take off time.
			var now = new Date();
			var dd = String(now.getDate()).padStart(2, '0');
			var mm = String(now.getMonth() + 1).padStart(2, '0'); //January is 0!
			var yyyy = now.getFullYear();			
		    var today = yyyy + '-' + mm + '-' + dd; 													
			var launch =  new Date(today+'T'+(this.model.get('Takeoff')).replace(/-/g,"/"));
			var temptime = Math.abs(landing.getTime()-launch.getTime())/3.6e6; 
			var hours = Math.round(temptime *100) / 100 ;		

			this.model.set({ FlightEnd:  landing.toLocaleTimeString('en-US',  {hour12:false})});	
 			this.$el.addClass('landed'); 
			this.model.set({ Landing:  landing.toLocaleTimeString('en-US',  {hour12:false}), Time: hours});
			this.model.save(
				{
 				patch:true,
			    success: function(model, resp, options) {
// 			    	off_line(options);  		
//    			       alert('updated landing'); 
			    }, 
			    error: function(model, resp, options){
// 			    	off_line(options);  		
// 			    	alert('Error: ' + resp);
// 			    	console.log(error);
			    }			
			});	
			return this; 
 		},
	});
	// collection View
    app.FlightLogView =  Backbone.View.extend({ // Flight Log View
		el: '#eflights', 
		tagName: 'tbody',
     	className: 'Row',
		localDivTag: 'div',	 		 	 	        
      	initialize: function(){
      	    var self=this;
            this.collection = new app.FlightList();           // create new collection 
          
             this.collection.fetch({ 
             	wait: true , 
             	data: $.param({start: app.working_date,
             	done: onDataHandler,  
             	error: onErrorHandler,
             	            
             })});  // fetch any existing flights    	    
		var onDataHandler = function(collection, response, options) {
		      console.log('membersview fetch onedatahandler');
		      this.render();
		  };
		
		  var onErrorHandler = function(collection, response, options) {
		      console.log('membersview fetch oneerorhandler');
		      alert(response.responseText);
		  };
		  

             this.listenTo(this.collection, 'reset', this.render);
             this.listenTo(this.collection, 'change', this.statusUpdate);
             this.listenTo(this.collection, 'add', this.render_add);                  
   			_.bindAll(this, 'addItem', 'cancelItem', 'updateItem'); //'resetLanding', 'resetTakeoff');   			  			
// form buttons 				
            $('#addFlight #cancel').bind('click', this.cancelItem);               
            $('#addFlight #add').bind('click', this.addItem);   
            $('#addFlight #update').bind('click', this.updateItem); 
            $('#addFlight #reset_time_landing').bind('click', this.resetLanding);   
            $('#addFlight #reset_time_launch').bind('click', this.resetTakeoff);   
  
         $( "#datepicker" ).datepicker({
         	dateFormat: 'yy-mm-dd',
         	onSelect: function (dateText, inst) {
				$('.Row').remove();
         		app.working_date= dateText;	
         		self.collection.fetch({ wait: true, reset:true, data: $.param({start: app.working_date })});				
         		$('#editDate').text('Flight Log: ' +app.working_date).css("color", "red");
         	}         
          });           
       },
      statusUpdate: function(){
        	$('#flightCount').text('Flights: ' + this.collection.length);
 			$('#activeCount').text('Active: ' + activeFlights(this.collection));        	       	 
      },
      render: function(){
      	this.collection.each(function(item){	    	
	 		this.renderItem(item);    	
      	}, this );
      	$('#flightCount').text('Flights: ' + this.collection.length);
 		$('#activeCount').text('Active: ' + activeFlights(this.collection));        	       	 
      },
      render_add: function(item){
   		this.$('.Row').html(''); 	
   		this.collection.reset(this.collection.fetch({reset:true, wait: true , data: $.param({start: app.working_date })}));  // fetch any existing flights       	       	 
      },
      addItem: function(e){
        e.preventDefault();
      	var formData = formProcessing();  
       	var model_count =  this.collection.length;
      	if(model_count != 0 ){
      		var max_flight = this.collection.max('yearkey');
      		var max_key = Number(max_flight.get('yearkey'));      	
      	} else {
      		var max_key = app.last['yearkey'];
      	} 
      	if(formData['Pilot1'] == ' ') {
      		alert('Pilot 1 can not be blank');
      	} else {    	      	   	
      		formData['yearkey'] = max_key+1;     			 
 			$("body").addClass("loading");      			 	      		
      		var new_model = this.collection.create( formData, 
      			{
       			wait: true,
      			success: function(model, resp, options) {
      				$("body").removeClass("loading");  
//       				off_line(options);  			
      			},
      			error: function(model, resp, options) {
      				alert('Add new record failed.');
      				console.log(resp)
      				$("body").removeClass("loading");
//       				off_line(options);  
      			},   	
      		}); 	
 		this.cancelItem(e);
		}
      },
      updateItem: function(e){    
		e.preventDefault();				 		
		var formData = formProcessing();;	
      	var updateModel = this.collection.get(formData.id);    	
     	var old_takeoff = updateModel.get('Takeoff');
   		var old_landing = updateModel.get('Landing');  	
   		if(typeof formData['Takeoff'] === 'undefined' )  	{
   			formData['Takeoff'] = '00:00:00';	
   		}
        	updateModel.save(formData, {
        		wait: true,
        		patch:false,    // < dual storage does not wort with this true 
        		error: function(model, response, error){	
//         				off_line(options);  		
      					var mresult= JSON.parse(response.responseText);  	
      					alert(mresult["message"])},
      			success: function(model, resp, options){
// 						off_line(options);  	
//      	 		   			this.$el.removeClass('landed');   
      					},          					 
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
      	 	$('#addFlight').removeClass("edit"); 	
	 		$('#flight_log_table').removeClass("edit"); 
    		$('#flightCount').text('Flights: ' + this.collection.length);
 			$('#activeCount').text('Active: ' + activeFlights(this.collection));  
      },
      resetLanding : function(e){
     	 	$('#Landing').val('00:00:00');
     	 	$('#Time').val(0);	
      	},
      resetTakeoff: function(e){
      		$('#Takeoff').val('00:00:00');
      		$('#Time').val(0);	
      	},
      renderItem: function(item){       
       	    var itemView = new app.FlightView({ model: item});
      		this.$el.append( itemView.render().el);    	
         }
	});
	// collection view functions
	function formProcessing(){
	  	var form = $('#addFlight');  //  $(document).
	  	var formData ={};	  
		if ( form.find('#id').val() != "" ){
	  		formData['id'] = form.find('#id').val();  // for update will be null for new. 
	  	}
	  	formData['Flight_Type'] = form.find('#Flight_Type').find(":selected").val(); 
	  	formData['Glider'] = form.find('#Glider').find(":selected").val(); 
	  	formData['Pilot1'] = form.find('#Pilot1').find(":selected").val(); 
	  	formData['Pilot2'] = form.find('#Pilot2').find(":selected").val(); 
	  	formData['Tow_Altitude'] = form.find('#Tow_Altitude').find(":selected").val(); 
	  	formData['Tow_Pilot'] = form.find('#Tow_Pilot').find(":selected").val(); 
	  	formData['Takeoff'] = form.find('#Takeoff').val(); 	  	
	  	formData['Landing'] = form.find('#Landing').val(); 
	  	formData['Notes'] = form.find('#Notes').val(); 
	  	formData['Date'] = app.working_date;
      	formData['pilot_is_number'] = 1; 	   // flag to RESTfull endpoint. 
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
   			formData['Time'] = '0';	 		
   		}
   		form.find('#id').val(""); // clean out the id
//    		console.log(form.find('#id').val());
   		return formData;
	 };		
	 function activeFlights(collection){
    	 var not_landed = collection.where({Landing : '00:00:00'}).length;    	
    	 var not_takeoff = collection.where({Takeoff : '00:00:00'}).length ;
    	 return (not_landed-not_takeoff);   			 	 
	 }
// 	 function off_line(options){  // for use with dual storage. 
// 		if(options.dirty){
// 			$('#connectstatus').text("OFF LINE");
// 			$('#connectstatus').removeClass("connectstatus");
// 			$('#connectstatus').addClass("connectstatusoffline");
// 		} else {
// 			$('#connectstatus').text("On line");
// 			$('#connectstatus').removeClass("connectstatusoffline");
// 			$('#connectstatus').addClass("connectstatus");
// 		}
// 	}
// end of collection view functions    
	// start the application. 		
	new app.FlightLogView();
})( jQuery );


