var fileUploadsFieldController = Marionette.Object.extend( {
    initialize: function() {
        Backbone.Radio.channel( 'conditions-key-select-field-file_upload' ).reply( 'hide', function( modelType ){ if ( 'when' == modelType ) { return true; } else { return false; } } );
        Backbone.Radio.channel( 'conditions-file_upload' ).reply( 'get:triggers', this.getTriggers );
    },

    getTriggers: function(defaults) {
		let allowed = ['show_field', 'hide_field', 'set_required', 'unset_required'];
		let remove = _.reject(defaults, (value, key) => {return allowed.includes(key)});
		_.each(remove, (item) => {delete defaults[item.value]});
		return defaults;
    }
});

jQuery( document ).ready( function( $ ) {
    new fileUploadsFieldController();
});