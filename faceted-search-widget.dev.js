//unminified version of the ajax code inline within the widget
jQuery(document).ready(function($){ 
		
	$(faceted_search_widget[0]+' a').live( 'click', function( event ){
				
		event.preventDefault();

		var url = $(this).attr( 'href' );
				
		$.each(faceted_search_widget, function( i, el ) { $(el).animate({opacity:0}) });
				
		$.ajax({
			url: url,
			context: document.body, 
			success: function( response ) {
					 
				var html = $('<div />').append(response.replace(/<script(.|\s)*?\/script>/g, "") );
				$.each(faceted_search_widget, function( i, el ) {
			 		$( el ).html( $(html).find( el ).html() );
				 });
					 	
				$.each(faceted_search_widget, function( i, el ) { $(el).animate({opacity:1}) });

				history.pushState({page:url}, url, url);
						
			}	 
		});
		
		return false;
	
	});
			
});