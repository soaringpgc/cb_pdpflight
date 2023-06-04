(function( $ ) {
	'use strict';
// 	console.log('cloud_base_public_vars');
//  console.log(cloud_base_public_vars);
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
// 	 		alert('add flight');
	 		$("#addFlight").removeClass("hidden");
	 		$(".flight_list").addClass("editing");    	  
	 });	 
	 
	var app = app || {};
	app.working_date = (new Date()).toISOString().split('T')[0];
	$('#editDate').text('Flight Log for: ' +app.working_date);
 
	app.Model = Backbone.Model.extend({
	// over ride the sync function to include the Wordpress nonce. 
	// going to need this for everything so do it once.  
	  	sync: function( method, model, options ){
    		return Backbone.sync(method, this, jQuery.extend( options, {
      			beforeSend: function (xhr) {
//      			alert(cloud_base_public_vars.nonce);
        		xhr.setRequestHeader( 'X-WP-NONCE', cloud_base_public_vars.nonce );
      			},
   			} ));	
   		},	
	});

	app.Flight = app.Model.extend({
		initialize: function(){
// 			_.extend(this, new Backbone.Workflow(this, {attrName:'status'})
// 			);
// 			this.bind('transition:from:staged', function(){
// 				this.set('start_time', new Date().toISOString());
// 			});
// 			this.bind('transition:from:inflight', function(){
// 				this.set('end_time', new Date().toISOString());
// 			});
		},
// 		validation:{
// 			Glider:{
// 				required: true,
// 				  length: 4
// 			},
// 			Pilot1:{
// 				required: true,
// 				  length: 4
// 			},	
// 		},
		defaults: {	
			Flight_Type	: "REG"
		},
		wait: true
	});
// collections	
    app.Collection = Backbone.Collection.extend({	
    	sync: function( method, model, options ){
    		return Backbone.sync(method, this, jQuery.extend( options, {
      			beforeSend: function (xhr) {
        		xhr.setRequestHeader( 'X-WP-NONCE', cloud_base_public_vars.nonce );
      			},
   			} ));	
   		},	
   	 }) ; 

    app.FlightList= app.Collection.extend({
    	model: app.Flight,
    	url: cloud_base_public_vars.root + 'cloud_base/v1/pdp_flightlog?start=' + app.working_date,  
    	comparator: function(Flight){
    			return(-Number(Flight.get("yearkey")));
    		},
//     		landed: function(Flight){
//     			return( Flight.get(Landing) != "" ? true : false);
//     		}
//     	}
   	 }) ; 		
// model view	
	app.ModelView = Backbone.View.extend({
		tagName: 'div',
        className: 'Row',
		render: function(){
			this.$el.html( this.template(this.model.toJSON() ) );
			this.$input = this.$('.edit');
			return this;
		},
		initialize: function(){
    		this.model.on('change', this.render, this);
  		},
		events:{
			'click label' : 'update',
			'click .buttonlaunch' : 'launch_time',
			'click .buttonlanding' : 'landing_time',
		},
   		update: function(){
			var localmodel = this.model;
 			$("#addorupdate").addClass('editing'); 	
 			$("#addFlight").removeClass("hidden");		
             // NTFS this requires the form id's to be the same as the model id's.
             // we are looping over the form, picking up the id's and then getting the 
             // value of the same id in the model and then loading it back into the form
             //  someone (probably me) is going to hate me in the future.  -dsj
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
			var tzoffset = (new Date()).getTimezoneOffset() * 60000; //offset in milliseconds
			var launch = new  Date();
// 			this.model.set({'Takeoff': launch.toLocaleTimeString('en-US',  {hour12:false})});								
// 			this.model.set({'Takeoff':  launch.toLocaleTimeString('en-US',  {hour12:false})});
// 			this.model.set({'start_display_time':  launch.toLocaleTimeString('en-US',  {hour12:false})});
			this.$el.addClass('inflight'); 	
			this.model.save(
 				{ Takeoff:  launch.toLocaleTimeString('en-US',  {hour12:false})},
				{
				patch:true,
// 				wait: true,
			    success: function(model, resp, opt) {
// 			       alert('updated'); 
			    }, 
			    error: function(model, error){
// 			    	alert('Error: ' + error);
// 			    	console.log(error);
			    }			
			});
		},
		landing_time: function(){
			var tzoffset = (new Date()).getTimezoneOffset() * 60000; //offset in milliseconds
			var landing = new Date(); 
			var now = new Date();
			var dd = String(now.getDate()).padStart(2, '0');
			var mm = String(now.getMonth() + 1).padStart(2, '0'); //January is 0!
			var yyyy = now.getFullYear();			
		    var today = yyyy + '-' + mm + '-' + dd;
			// recreate take off time. 													
			var launch =  new Date(today+'T'+(this.model.get('Takeoff')).replace(/-/g,"/"));
			var temptime = Math.abs(landing.getTime()-launch.getTime())/3.6e6; 
			var hours = Math.round(temptime *100) / 100 ;
// 			this.model.set({'Time' : hours});
			
			this.$el.addClass('landed'); 
// 			this.model.save();
			this.model.save(
 				{ Landing:  landing.toLocaleTimeString('en-US',  {hour12:false}), Time: hours},
				{
				patch:true,
// 				wait: true,
			    success: function(model, resp, opt) {
//  			       alert('updated'); 
			    }, 
			    error: function(model, error){
			    	alert('Error: ' + error);
			    	console.log(error);
			    }			
			});	
		}
	});
	app.FlightView = app.ModelView.extend({
	        template: flighttemplate_pdp,     
	});	
// 		
	app.CollectionView =  Backbone.View.extend({         
      initialize: function(){
        this.collection.fetch({reset:true});
//         this.collection.comparator = Collection.comparators['landed', 'yearkey'];
//         this.collection.sort();
        this.render();
        this.listenTo(this.collection, 'add', this.renderItem);
        this.listenTo(this.collection, 'reset', this.render);
//         this.collection.on('change', this.render, this);
      },
      render: function(){
      	this.collection.each(function(item){	
  			this.renderItem(item);    	
      	}, this );
      },
      events:{
      	'click #add' : 'addItem',
      	'click #update' : 'updateItem',
      	'click #cancel' : 'cancelItem'
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
      	formData['Time'] = " ";
//       	console.log(this.collection.max('yearkey'));
//         	alert(JSON.stringify(formData));
      	this.collection.create( formData, {wait: false, at: 0});   
//       	this.collection.sortBy('yearkey') ;   
// clean out the form:
		this.cancelItem(e);
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
     	var updateModel = this.collection.get(formData.id);         	
     		
		var old_takeoff = updateModel.get('Takeoff');
		var old_landing = updateModel.get('Landing');

		var now = new Date();
		var dd = String(now.getDate()).padStart(2, '0');
		var mm = String(now.getMonth() + 1).padStart(2, '0'); //January is 0!
		var yyyy = now.getFullYear();			
		var today = yyyy + '-' + mm + '-' + dd;
		// recreate take off time. 		
// 		alert(old_takeoff)	;									
		if((formData['Takeoff'] != old_takeoff || formData['Landing'] != old_landing) && (formData['Landing'] === undefined)){
// 				alert('Take off time has changed'); 
			var launch =  new Date(today+'T'+(formData['Takeoff']).replace(/-/g,"/"));	
			var landing =  new Date(today+'T'+(formData['Landing']).replace(/-/g,"/"));
			var temptime = Math.abs(landing.getTime()-launch.getTime())/3.6e6; 
			var hours = Math.round(temptime *100) / 100 ;      	
      		formData['Time'] =  hours;	
//       		updateModel.set("Time", hours);			
		}
// 		updateModel.set(formData);  		
        updateModel.save(formData, {wait: true,
        	error: function(model, response, error){
      				var mresult= JSON.parse(response.responseText);     	
      				alert(mresult["message"])},
      		success: function(){
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
      	$(this.localDivTag).children('select').each(function(i, el ){
      		if('#'+el.id == '#Tow_Pilot' || '#'+el.id == '#Tow_Plane'){
      			return;
      		}
			$('#'+el.id).val('');
      	});         	
      	$(this.localDivTag).children('textarea').each(function(i, el ){
				$('#'+el.id).val('');
      	});  
      	$("#addorupdate").removeClass('editing');		    
      	$("#addFlight").addClass("hidden");   
      	$(".flight_list").removeClass("editing");   		   
      		    	
      }
	});
// 	app.FlightsView = app.CollectionView.extend({
// 	 	el: '#flights', 
// 		localDivTag: '#addFlight Div',
// 	 	preinitialize(){
// 			this.collection = new app.FlightList();
// 	 	},	
// 	    initialize: function(){
// 	      var self = this;
// 	 	  this.collection = new app.FlightList();
//     	  this.collection.fetch({reset:true, wait: true });    
// 		  
//           this.listenTo(this.collection, 'add', this.renderItem);
//           this.listenTo(this.collection, 'reset', this.render);
//         },
//         render: function(){
//       	  this.collection.each(function(item){
//   		  this.renderItem(item);    	
//       	}, this );
//       },
//       renderItem: function(item){    
// // convert SQL time to Javascript   
// 			if(item.get('Takeoff') != null){
//       			var launch =  new Date((item.get('Takeoff')).replace(/-/g,"/"));
// 				// adding 'inflight' class to the row. used below.... 
//              	var expandedView = app.FlightView.extend({ localDivTag:this.localDivTag, className: 'Row inflight'});      			
// // convert javascript time to SQL
// //      		alert(launch.toISOString().slice(0, 19).replace('T', ' '));
// //       			item.set({'start_display_time':  launch.toLocaleTimeString('en-US',{ hour12:false})}, {silent: true });
//       		} else {
//        			item.set({'Takeoff': ""}, {silent: true });
//       		}
//       		if(item.get('Landing') != null && item.get('Landing') != 0){
//       			var landing =  new Date(item.get('Landing'));
//       			// add 'landed' class to this row... used below. 
//       			var expandedView = app.FlightView.extend({ localDivTag:this.localDivTag, className: 'Row landed'});
// //       			item.set({'end_display_time':  landing.toLocaleTimeString('en-US', { hour12:false})}, {silent: true });
//       		} else {
//       			item.set({'Landing': ""}, {silent: true });
//       		}
// 			if(expandedView === undefined ){
//             	var expandedView = app.FlightView.extend({ localDivTag:this.localDivTag, className: 'Row'});
//             }
//             var itemView = new expandedView({
//       	  		model: item
//       		})
//       		this.$el.append( itemView.render().el);   
//         }
// 	}); 
	app.EditView = app.CollectionView.extend({
	 	el: '#eflights', 
		localDivTag: '#addFlight div',
 	 	preinitialize(){
 	 	   this.collection = new app.FlightList();
 	 	},	
      	renderItem: function(item){    
// convert SQL time to Javascript   
			if(item.get('Takeoff') != null){
      			var launch =  new Date((item.get('Takeoff')).replace(/-/g,"/"));
				// adding 'inflight' class to the row. used below.... 
             	var expandedView = app.FlightView.extend({ localDivTag:this.localDivTag, className: 'Row inflight'});      			
// convert javascript time to SQL
      		} else {
       			item.set({'Takeoff': ""}, {silent: true });
      		}
      		if(item.get('Landing') != null && item.get('Landing') != 0){
      			var landing =  new Date(item.get('Landing'));
      			// add 'landed' class to this row... used below. 
      			var expandedView = app.FlightView.extend({ localDivTag:this.localDivTag, className: 'Row landed'});
//       			item.set({'end_display_time':  landing.toLocaleTimeString('en-US', { hour12:false})}, {silent: true });
      		} else {
      			item.set({'Landing': ""}, {silent: true });
      		}
			if(expandedView === undefined ){
            	var expandedView = app.FlightView.extend({ localDivTag:this.localDivTag, className: 'Row'});
            }
            var itemView = new expandedView({
      	  		model: item
      		})
      		this.$el.append( itemView.render().el);   
        }
	}); 

   $(function(){
  
   if (typeof cb_admin_tab !== 'undefined' ){
   		switch(cb_admin_tab){
   			case "flights" : 
//    				new app.FlightsView();
      			new app.EditView();
   			break;
   		}
   	} else {

   	}
   });
})( jQuery );
