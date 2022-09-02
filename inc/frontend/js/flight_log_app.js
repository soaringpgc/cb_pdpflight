(function( $ ) {
	'use strict';
	$(function(){
	var pdp = pdp || {};
  	
// MODELS --------------------------------  

  	pdp.Flight = Backbone.Model.extend({ 
  		defaults : {
  			Key : '0',
  			altitude : '0',
  			start : '00:00:00',
  			stop  : '00:00:00',
  			active: true
  		},
		launch_time: function(){
//		    alert('you pushed the button!');
			var tzoffset = (new Date()).getTimezoneOffset() * 60000; //offset in milliseconds
 			var storelaunch = (new Date(Date.now() - tzoffset));
			var launch = new  Date();
			this.model.set({'start_time': storelaunch.toISOString().slice(0, 19).replace('T', ' ') });								
			this.model.set({'start_display_time':  launch.toLocaleTimeString('en-US',  {hour12:false})});
			this.$el.addClass('inflight'); 	
			this.model.save();
//		alert(JSON.stringify(this.model));
		},
		landing_time: function(){
			var landing = new Date(); 
//			alert (landing);
			this.model.set({'end_time': landing.toISOString().slice(0, 19).replace('T', ' ') });								
			var launch =  new Date((this.model.get('start_time')).replace(/-/g,"/"));
//			alert(launch);
			var temptime = Math.abs(landing.getTime()-launch.getTime())/3.6e6; 
			var hours = Math.round(temptime *100) / 100 ;
			
//			this.model.set({'start_display_time': landing.toLocaleTimeString('en-US',  {hour12:false})});
			this.model.set({'start_display_time': hours});
			this.$el.addClass('landed'); 
			this.model.save();	
		},  	
		archived : function(){
			this.save({
				active : !this.get('active') 
			})
		},
		idAttribute: '_id',
		validate: function(attrs, options) {
			if (attrs.glider_id && attrs.pilot_id ) { 
				return "valid";
			} else {
				return "invalid";
			}
		} 
  	});  
 
 	pdp.edit_flight =   Backbone.Model.extend({ 
 
 		urlRoot: 'http://localhost:8888/wordpress/wp-json/cloud_base/v1/pdp_flights',
 		validate: function(attributes) {
 			if (attributes.glider_id == ' ') {
 				alert("Glider is missing");
 				return 'glider_id missing';
 			} else if (attributes.pilot_id == ' ') { 	
 				alert("Pilot is missing");	
 				return 'pilot_id missing';
 			}
 		}
 	}); 

  		
// COLLECTIONS  ----------------------------
 
  	pdp.FlightList = Backbone.Collection.extend({
  		model : pdp.Flight,
//  		url: 'http://localhost:8888/wordpress/wp-json/cloud_base/v1/flights/',
  		url: 'http://localhost:8888/wordpress/wp-json/cloud_base/v1/pdp_flights',
  		data: function(){
  			var today = new Date();
  			var StrDate = "Y-m-d".replace('Y', today.getFullYear()).replace('m', today.getMonth()+1).replace('d', today.getDate());

//  			return "?date=2022-06-30";
//  			return "?date=" + $date->format('Y-m-d');
  		},
//	    localStorage: new Backbone.LocalStorage("flight-log"),
		active : function(){
			return this.filter(function( flight ){
				return flight.get('active')
			})
		},
// 		constructURL: function(data){
// 			var result = this.urlBase + data;
// 			return result;
// 		},
// 		url: function(){
// 			return this.constructURL(this.data);
// 		},
  		archived : function(){
  			return this.without.apply( this, this.active);
  		}
  	});
// VIEWS ----------------------------  	

// flight.js (model View )  	
  	pdp.FlightView = Backbone.View.extend({
  		tagName : 'div',
  		className : 'flightContainer',
  		template : $('#flightTemplate').html(),
  //		template : pdp.flightTemplate,
  		events : {
  			'dblclick label' : 'edit',
  			'keypress .edit' : 'updateOnEntry',
  			'blur .edit' 	 : 'close'
  		},
  		initialize : function(){
  			this.listenTo(this.model, 'change', this.render)
  		},
  		render : function(){
			//tmpl is a function that takes a JSON object and returns html
			var tmpl = _.template( this.template );
			//this.el is what we defined in tagName. use $el to get access to jQuery html() function
			this.$el.html( tmpl( this.model.toJSON() ) );
	
			return this;  		  		
  		},
  		edit : function() {
  			this.$el.addClass('editing');
  			this.$input.focus();
  		},
  		close: function() {
  			var value = this.$input.val();
  			if (value) {
  				this.model.save({ title: value })
  			}
  			this.$el.removeClass('editing');
  		},
  		updateOnEntry: function(e) {
  			if (e.which === ENTER_KEY ) {
  				this.close();
  			}
  		} 	
  	}); 	 	
  	
// flightlog.js (collection View )  

pdp.FlightLogView = Backbone.View.extend({
//	el: $( '#books' ),
	el: $( '#FlightLog' ),
	
	initialize: function() {
		this.collection = new pdp.FlightList();
		this.collection.fetch();
//			console.log(this.collection);
		this.render();

		this.listenTo( this.collection, 'add', this.renderFlight );
		this.listenTo( this.collection, 'reset', this.render );
	},

	events: {
		'click #add': 'flightedit'
	},

	flightedit: function( e ) {
		e.preventDefault();

		var formData = {};

		$( '#flightedit div' ).children( 'input' ).each( function( i, el ) {

			if( $( el ).val() != "" )
			{
				formData[ el.id ] = $( el ).val();
			}
		});
		$( '#flightedit div' ).children( 'select' ).each( function( i, el ) {
			formData[ el.id ] = el.value;
      	});

//console.log(formData);

		pdp.new_flight = new pdp.edit_flight(formData);
			console.log(pdp.new_flight);

alert("add");
		pdp.new_flight.save(formData, {
			wait: true,
			dataType:"text",
			succcess: function(model){
				console.log("Saved Successfully");
			},
   			error:function(model){
       			console.log("Error");
   			}		
		})
//		this.collection.create( formData );
	},

	// render flightlog by rendering each flight in its collection
	render: function() {
		this.collection.each(function( item ) {
			this.renderFlight( item );
		}, this );
	},

	// render a flight by creating a FlightView and appending the
	// element it renders to the flightlog's element
	renderFlight: function( item ) {
		var flightView = new pdp.FlightView({
			model: item
		});
		this.$el.append( flightView.render().el );
	}
});
 	
  	
// Application ------------------

$(function() {
//	$( '#releaseDate' ).datepicker();
	new pdp.FlightLogView();
});

  }) // $(function) close
})( jQuery );

  	