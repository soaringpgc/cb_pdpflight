// (function( $ ) {
// 	'use strict';
// 	/**
// 	 * All of the code for your public-facing JavaScript source
// 	 * should reside in this file.
// 	 *
// 	 * Note: It has been assumed you will write jQuery code here, so the
// 	 * $ function reference has been prepared for usage within the scope
// 	 * of this function.
// 	 *
// 	 * This enables you to define handlers, for when the DOM is ready:
// 	 *
// 	 * $(function() {
// 	 *
// 	 * });
// 	 *
// 	 * When the window is loaded:
// 	 *
// 	 * $( window ).load(function() {
// 	 *
// 	 * });
// 	 *
// 	 * ...and/or other possibilities.
// 	 *
// 	 * Ideally, it is not considered best practise to attach more than a
// 	 * single DOM-ready or window-load handler for a particular page.
// 	 * Although scripts in the WordPress core, Plugins and Themes may be
// 	 * practising this, we should strive to set a better example in our own work.
// 	 */
// 	var app = app || {};
//  
// 	app.Flight = Backbone.Model.extend({
// 	// over ride the sync function to include the Wordpress nonce. 
// 	// going to need this for everything so do it once.  
// 	  	sync: function( method, model, options ){
//     		return Backbone.sync(method, this, jQuery.extend( options, {
//       			beforeSend: function (xhr) {
// //      			alert(cloud_base_public_vars.nonce);
//         		xhr.setRequestHeader( 'X-WP-NONCE', cloud_base_public_vars.nonce );
//       			},
//    			} ));	
//    		},
//    		initialize: function(){
// 			_.extend(this, new Backbone.Workflow(this, {attrName:'status'})
// 			);
// 			this.bind('transition:from:staged', function(){
// 				this.set('start_time', new Date().toISOString());
// 			});
// 			this.bind('transition:from:inflight', function(){
// 				this.set('end_time', new Date().toISOString());
// 			});
// 		},
// 		validation: {
// 			pilot_id: {required: true },
// 			aircraft_id:  {required: true },
// 		},
// 		wait: true   			
// 	});
// // collections	
//      app.FlightList = Backbone.Collection.extend({	
//     	sync: function( method, model, options ){
//     		return Backbone.sync(method, this, jQuery.extend( options, {
//       			beforeSend: function (xhr) {
//  //     			alert(cloud_base_public_vars.nonce);
//         		xhr.setRequestHeader( 'X-WP-NONCE', cloud_base_public_vars.nonce );
//       			},
//    			} ));	
//    		},
//    		model: app.Flight,
//     	url: cloud_base_public_vars.root + 'cloud_base/v1/flights',    			
//    	 }) ; 
// // model view	
// 	app.FlightView = Backbone.View.extend({
// 	    template: cb_pdp_flighttemplate,    
// 		tagName: 'div',
//         className: 'Row',
// 		render: function(){
// 			this.$el.html( this.template(this.model.toJSON() ) );
// 			this.$input = this.$('.edit');
// 			return this;
// 		},
// 		initialize: function(){
//     		this.model.on('change', this.render, this);
//   		},
// //   		set_status: function(){
// //   			alert('here');
// //   		 	this.$el.addClass('inflight');
// //   		},
// 		events:{
// 			'dblclick label' : 'update',
// 			'click .buttonlaunch' : 'launch_time',
// 			'click .buttonlanding' : 'landing_time',
// 		},
//    		update: function(){
// 			var localmodel = this.model;
//  			$("div.editform").addClass('editing'); 			
//              // NTFS this requires the form id's to be the same as the model id's.
//              // we are looping over the form, picking up the id's and then getting the 
//              // value of the same id in the model and then loading it back into the form
//              //  someone (probably me) is going to hate me in the future.  -dsj
//             $(this.localDivTag).children('input').each(function(i, el ){
//       		   if(el.type === "checkbox" ){
//       		   		if (localmodel.get(el.id) === "1" ){
//       		   			$('#'+el.id).prop("checked", true);
//       		   		} else {
//       		   		    $('#'+el.id).prop("checked", false);
//       		   		}
//       		   } else {
//       		      $('#'+el.id).val(localmodel.get(el.id));
//       		   }  
//       		});     		
//       		$(this.localDivTag).children('select').each(function(i, el ){
// 				$('#'+el.id).val(localmodel.get(el.id));
//       		});
// 		},  
// 		launch_time: function(){
// //		    alert('you pushed the button!');
// 			var tzoffset = (new Date()).getTimezoneOffset() * 60000; //offset in milliseconds
//  			var storelaunch = (new Date(Date.now() - tzoffset));
// 			var launch = new  Date();
// 			this.model.set({'start_time': storelaunch.toISOString().slice(0, 19).replace('T', ' ') });								
// 			this.model.set({'start_display_time':  launch.toLocaleTimeString('en-US',  {hour12:false})});
// 			this.$el.addClass('inflight'); 	
// 			this.model.save();
// //		alert(JSON.stringify(this.model));
// 		},
// 		landing_time: function(){
// 			var landing = new Date(); 
// //			alert (landing);
// 			this.model.set({'end_time': landing.toISOString().slice(0, 19).replace('T', ' ') });								
// 			var launch =  new Date((this.model.get('start_time')).replace(/-/g,"/"));
// //			alert(launch);
// 			var temptime = Math.abs(landing.getTime()-launch.getTime())/3.6e6; 
// 			var hours = Math.round(temptime *100) / 100 ;
// 			
// //			this.model.set({'start_display_time': landing.toLocaleTimeString('en-US',  {hour12:false})});
// 			this.model.set({'start_display_time': hours});
// 			this.$el.addClass('landed'); 
// 			this.model.save();	
// 		}
// 	});
// 	app.CollectionView =  Backbone.View.extend({         
//       initialize: function(){
//         this.collection.fetch({reset:true});
//         this.render();
//         this.listenTo(this.collection, 'add', this.renderItem);
//         this.listenTo(this.collection, 'reset', this.render);
//       },
//       render: function(){
//       	this.collection.each(function(item){	
//   			this.renderItem(item);    	
//       	}, this );
//       },
//       events:{
//       	'click #add' : 'addItem',
//       	'click #update' : 'updateItem'
//       },
//       addItem: function(e){
//         e.preventDefault();
//       	var formData ={};
//       	// grab all of the input fields
//  		$(this.localDivTag).children('input').each(function(i, el ){
// 		  if($(el).val() != ''){
// 		  	if($(el).hasClass('checked_class')){
// 		  		formData[el.id]=($(el).is(":checked")? true : false );
// 		  	} else {
//         		formData[el.id] = $(el).val();
//         	}
//       	  } 
//       	});
//       	//grab all of the <select> fields 
//       	$(this.localDivTag).children('select').each(function(i, el ){
//       		if($(el).val() != ''){
//       			formData[el.id] = $(el).val();
//       		}
//       	});
//       	$(this.localDivTag).children('textarea').each(function(i, el ){
//       		if($(el).val() != ''){
//       			formData[el.id] = $(el).val();
//       		}
//       	});
//  //     	alert(JSON.stringify(formData));
//       	this.collection.create( formData, {wait: true});
//       },
//       updateItem: function(e){     	
// 		e.preventDefault();
//  		var formData ={};
// 		// grab all of the input fields
//  		$(this.localDivTag).children('input').each(function(i, el ){
//  		 if($(el).val() != ''){
// 		  	if($(el).hasClass('checked_class')){
// 		  		formData[el.id]=($(el).is(":checked")? true : false );
// 		  	} else {
//         		formData[el.id] = $(el).val();
//         	}
//       	  } 		
//       	});
//       	//grab all of the <select> fields 
//       	$(this.localDivTag).children('select').each(function(i, el ){
//       	  if($(el).val() != ''){
//       		formData[el.id] = $(el).val();
//       	  }
//       	});
// //      	alert(JSON.stringify(formData));
//       	var updateModel = this.collection.get(formData.id);
//         updateModel.save(formData, {wait: true});
// // clean out the form:
//       		$(this.localDivTag).children('input').each(function(i, el ){
// 				$('#'+el.id).val('');
//       		});       
//       		$(this.localDivTag).children('select').each(function(i, el ){
// 				$('#'+el.id).val('');
//       		});       
// 		$("div.editform").removeClass('editing');	
//       	}
// 	});
// 	app.FlightsView = app.CollectionView.extend({
// 	 	el: '#flights', 
// 		localDivTag: '#addFlight Div',	
// 	    initialize: function(){
// 	      var self = this;
// //      	console.log('the view has been initialized. ');
// 	 	  this.aircraft = new app.AircraftList();
// 	 	  this.pilots = new app.PilotList();
// 	 	  this.fees = new app.FeeList();
// 	 	  this.collection = new app.FlightList();
// //NTFS : we fetch the aircraft, if that is successful, we fetch pilots, and if that is successful 
// // fetch the flights. It took forever to figure this out. 
// // 		  this.aircraft.fetch({reset:true, success:function(){
// //     		  self.pilots.fetch({reset:true, success:function( ){
// //     		  	  self.collection.fetch({reset:true });    
// //     		  }});		  
// // 		  }})	
// // 		  
// 		  this.aircraft.fetch({reset:true, async:false} );
//     	  this.pilots.fetch({reset:true, async:false} );
//     	  this.fees.fetch({reset:true, async:false} );
//     	  this.collection.fetch({reset:true });    
// 		  
//           this.listenTo(this.pilots, 'reset', this.render);                
// //          this.render();
//           this.listenTo(this.collection, 'add', this.renderItem);
//           this.listenTo(this.collection, 'reset', this.render);
//         },
//         render: function(){
//       	  this.collection.each(function(item){
// // NTFS Here we are basically doing a SQL join we are copying elements from the aircraft and pilots models into the
// // flight model so it can be displayed for basic human consumption. These values are for display only so 
// // we add the silent so they are not sent back to the server, (where they will be ignored anyway. )
//    		  item.set({"p_first_name" : this.pilots.findWhere({pilot_id: parseInt(item.get('pilot_id'), 10) }).get("first_name")}, {silent: true }); 
//    		  item.set({"p_last_name" :this.pilots.findWhere({pilot_id: parseInt(item.get('pilot_id'), 10) }).get("last_name")}, {silent: true } ); 
// 
// 		  if(item.get('aircraft_id') !== '0'){
//    		    item.set({"glider" :this.aircraft.findWhere({aircraft_id: item.get('aircraft_id')}).get("compitition_id")}, {silent: true }); 
//    		  } else {
//    		  	item.set({"glider" : 'PVT'}); 
//    		  }
// 		  if(item.get('tow_plane_id') !== '0'){
//    		    item.set({"towplane" : this.aircraft.findWhere({aircraft_id: item.get('tow_plane_id')}).get("registration")}, {silent: true }); 
//    		  } else {
//    		  	item.set({"towplane" : ''}); 
//    		  }
//   		  this.renderItem(item);    	
//       	}, this );
//       },
//       renderItem: function(item){    
// // convert SQL time to Javascript   
// 			if(item.get('start_time') != null){
//       			var launch =  new Date((item.get('start_time')).replace(/-/g,"/"));
// 				// adding 'inflight' class to the row. used below.... 
//              	var expandedView = app.FlightView.extend({ localDivTag:this.localDivTag, className: 'Row inflight'});      			
// // convert javascript time to SQL
// //      		alert(launch.toISOString().slice(0, 19).replace('T', ' '));
//       			item.set({'start_display_time':  launch.toLocaleTimeString('en-US',{ hour12:false})}, {silent: true });
//       		} else {
//       			item.set({'start_display_time': ""}, {silent: true });
//       		}
//       		if(item.get('end_time') != null && item.get('end_time') != 0){
//       			var landing =  new Date(item.get('end_time'));
//       			// add 'landed' class to this row... used below. 
//       			var expandedView = app.FlightView.extend({ localDivTag:this.localDivTag, className: 'Row landed'});
//       			item.set({'end_display_time':  landing.toLocaleTimeString('en-US', { hour12:false})}, {silent: true });
//       		} else {
//       			item.set({'end_display_time': ""}, {silent: true });
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
// 	app.EditView = app.CollectionView.extend({
// 	 	el: '#eflights', 
// 		localDivTag: '#editflights div',
//  	 	preinitialize(){
//  	 	   this.collection = new app.FlightList();
//  	 	},	
//          renderItem: function(item){
//          }
// 	}); 
// 
//    $(function(){
//   	
//   	 if (typeof cb_admin_tab !== 'undefined' ){
//   	 		switch(cb_admin_tab){
//   	 			case "flights" : 
//   	 				new app.FlightsView();
//   	 				new app.EditView();
//   	 			break;
//   	 		}
//   	 	} else {
// 	
//   	 	console.log("not defined");}
//    });
// })( jQuery );
