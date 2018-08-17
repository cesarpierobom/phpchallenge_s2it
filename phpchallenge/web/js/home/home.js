'use strict';

$(document).ready(function(){
	$(".box")
});


var isAdvancedUpload = function () {
	var div = document.createElement("div");
	return (("draggable" in div) || ("ondragstart" in div && "ondrop" in div)) && "FormData" in window && "FileReader" in window;
}();



function uploadFiles(id, file) {
	if ($("#file")[0].files) {
		$.each($("#file")[0].files, function (index, fileElement) {
			storeMetadata(fileElement);
		});
	}
}


function storeMetadata(fileElement) {
	var request = $.ajax({
		url: location.origin + "/api/v1/files",
		method: "POST", 
		datatype: "json",
		processData: false,
		cache: false,
		before: function () {

		},
		data:{
			filename:fileElement.name
		}
	})
	.done(function(data, textStatus, jqXHR){
		switch(jqXHR.status) {
		    case 201:
		    	var id = data.id;
		    	storeFile(id, fileElement);
		        break;
		    default:
		    	showMessageDialog("Results","This is unexpected. Looks like something wrong happened. Please try again.");
		    	console.log("unexpected success status");
		    	console.log(data);
		}
	})
	.fail(function(error){
		$(".box").addClass("is-error");
		switch(jqXHR.status) {
		    case 401:
		    	showMessageDialog("Error", "You need to be logged in to store files.");
		        break;
		    case 403:
		    	showMessageDialog("Error", "You are not allowed to store files.");
		        break;
		    	showMessageDialog("Error", "There was an error with your request.");
		        break;
		    case 400:
		    case 409:
		    case 404:
		    case 500:
		    	showMessageDialog("Error", "There was an error with your request.");
		        break;
		    default:
		    	showMessageDialog("Results", "This is unexpected. Looks like something wrong happened. Please try again.");
		    	console.log("unexpected failure status");
		    	console.log(error);
		}
	})
	.always(function(data){

	});
}


function storeFile(id, fileElement){
	var ajaxData = new FormData();
	ajaxData.append("file", fileElement);

	$.ajax({
		url: location.origin + "/api/v1/files/",
		method: "POST", 
		datatype: "json",
		processData: false,
		cache: false,
		before: function () {

		},
		data:ajaxData
	})
	.done(function(data){
		$(".box").addClass("is-success");

		switch(jqXHR.status) {
		    case 201:
		    	$(".box").addClass("is-success");
		        break;
		    default:
		    	showMessageDialog("Results","This is unexpected. Looks like something wrong happened. Please try again.");
		    	console.log("unexpected success status");
		    	console.log(data);
		}
	})
	.fail(function(error){
		$(".box").addClass("is-error");
		switch(jqXHR.status) {
		    case 401:
		    	showMessageDialog("Error", "You need to be logged in to store files.");
		        break;
		    case 403:
		    	showMessageDialog("Error", "You are not allowed to store files.");
		        break;
		    case 400:
		    case 404:
		    case 409:
		    case 500:
		    	showMessageDialog("Error", "There was an error with your request.");
		        break;
		    default:
		    	showMessageDialog("Results", "This is unexpected. Looks like something wrong happened. Please try again.");
		    	console.log("unexpected failure status");
		    	console.log(error);
		}
	})
	.always(function(data){
		$(".box").removeClass("is-uploading");
	});
}

function showMessageDialog(title = null, body = null){
	$("#modalMessageLabel").empty();
	$("#modalMessageBody").empty();
	$("#modalMessageLabel").append(title);
	$("#modalMessageBody").append(body);
	$("#modalMessage").modal();
}


;( function( $, window, document, undefined )
{
	// feature detection for drag&drop upload

	// var isAdvancedUpload = function()
	// {
	// 	var div = document.createElement( "div" );
	// 	return ( ( 'draggable' in div ) || ( 'ondragstart' in div && 'ondrop' in div ) ) && 'FormData' in window && 'FileReader' in window;
	// }();


	// applying the effect for every form

	$( '.box' ).each( function()
	{
		var $form		 = $( this ),
			$input		 = $form.find( 'input[type="file"]' ),
			$label		 = $form.find( 'label' ),
			$errorMsg	 = $form.find( '.box__error span' ),
			$restart	 = $form.find( '.box__restart' ),
			droppedFiles = false,
			showFiles	 = function( files )
			{
				$label.text( files.length > 1 ? ( $input.attr( 'data-multiple-caption' ) || '' ).replace( '{count}', files.length ) : files[ 0 ].name );
			};

		// letting the server side to know we are going to make an Ajax request
		$form.append( '<input type="hidden" name="ajax" value="1" />' );

		// automatically submit the form on file select
		$input.on( 'change', function( e )
		{
			showFiles( e.target.files );
		});


		// drag&drop files if the feature is available
		if( isAdvancedUpload )
		{
			$form
			.addClass( 'has-advanced-upload' ) // letting the CSS part to know drag&drop is supported by the browser
			.on( 'drag dragstart dragend dragover dragenter dragleave drop', function( e )
			{
				// preventing the unwanted behaviours
				e.preventDefault();
				e.stopPropagation();
			})
			.on( 'dragover dragenter', function() //
			{
				$form.addClass( 'is-dragover' );
			})
			.on( 'dragleave dragend drop', function()
			{
				$form.removeClass( 'is-dragover' );
			})
			.on( 'drop', function( e )
			{
				droppedFiles = e.originalEvent.dataTransfer.files; // the files that were dropped
				showFiles( droppedFiles );

			});
		}


		// if the form was submitted

		$form.on( 'submit', function( e )
		{
			// preventing the duplicate submissions if the current one is in progress
			if( $form.hasClass( 'is-uploading' ) ) return false;

			$form.addClass( 'is-uploading' ).removeClass( 'is-error' );

			if( isAdvancedUpload ) // ajax file upload for modern browsers
			{
				e.preventDefault();

				// gathering the form data
				var ajaxData = new FormData( $form.get( 0 ) );
				if( droppedFiles )
				{
					$.each( droppedFiles, function( i, file )
					{
						ajaxData.append( $input.attr( 'name' ), file );
					});
				}

				// ajax request
				$.ajax(
				{
					url: 			$form.attr( 'action' ),
					type:			$form.attr( 'method' ),
					data: 			ajaxData,
					dataType:		'json',
					cache:			false,
					contentType:	false,
					processData:	false,
					complete: function()
					{
						$form.removeClass( 'is-uploading' );
					},
					success: function( data )
					{
						$form.addClass( data.success == true ? 'is-success' : 'is-error' );
						if( !data.success ) $errorMsg.text( data.error );
					},
					error: function()
					{
						alert( 'Error. Please, contact the webmaster!' );
					}
				});
			}
			else // fallback Ajax solution upload for older browsers
			{
				var iframeName	= 'uploadiframe' + new Date().getTime(),
					$iframe		= $( '<iframe name="' + iframeName + '" style="display: none;"></iframe>' );

				$( 'body' ).append( $iframe );
				$form.attr( 'target', iframeName );

				$iframe.one( 'load', function()
				{
					var data = $.parseJSON( $iframe.contents().find( 'body' ).text() );
					$form.removeClass( 'is-uploading' ).addClass( data.success == true ? 'is-success' : 'is-error' ).removeAttr( 'target' );
					if( !data.success ) $errorMsg.text( data.error );
					$iframe.remove();
				});
			}
		});


		// restart the form if has a state of error/success

		$restart.on( 'click', function( e )
		{
			e.preventDefault();
			$form.removeClass( 'is-error is-success' );
			$input.trigger( 'click' );
		});

		// Firefox focus bug fix for file input
		$input
		.on( 'focus', function(){ $input.addClass( 'has-focus' ); })
		.on( 'blur', function(){ $input.removeClass( 'has-focus' ); });
	});

})( jQuery, window, document );