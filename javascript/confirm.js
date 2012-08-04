/*
 * SimpleModal Confirm Modal Dialog
 * http://www.ericmmartin.com/projects/simplemodal/
 * http://code.google.com/p/simplemodal/
 *
 * Copyright (c) 2010 Eric Martin - http://ericmmartin.com
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Revision: $Id: confirm.js 254 2010-07-23 05:14:44Z emartin24 $
 */

jQuery(function ($) 
{
	// confirm-delete is the parent div class
	// input.confirm handles input buttons with confirm class
	// a.confirm handles a href with confirm class
	$('#confirm-delete input.confirm, #confirm-delete a.confirm').click(function (e) 
	{
		e.preventDefault();
		// Gets the href attr from the click
		var href = $(this).attr("href");
		
		//var href = $(this).attr("href");
		// example of calling the confirm function
		// you must use a callback function to perform the "yes" action
		confirm("Are you sure you want to delete this record?", function () {
			window.location.href = href;
		});
	});
});

function confirm(message, callback) 
{
	$('#confirm').modal({
		closeHTML: "<a href='' title='Close' class='modal-close'>x</a>",
		position: ["40%",],
		opacity: 80,
		overlayCss: {backgroundColor:"#000"},
		overlayId: 'confirm-overlay',
		containerId: 'confirm-container', 
		onShow: function (dialog) {
			var modal = this;

			$('.message', dialog.data[0]).append(message);

			// if the user clicks "yes"
			$('.yes', dialog.data[0]).click(function () 
			{
				// call the callback
				if ($.isFunction(callback)) {
					callback.apply();
				}
				
				//close the dialog
				modal.close(); // or $.modal.close();
			});
		}
	});
}