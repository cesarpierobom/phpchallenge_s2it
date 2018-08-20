'use strict';

var droppedFiles = [];

$(document).ready(function () {

	$(".form")
	.on("drag dragstart dragend dragover dragenter dragleave drop", function(e){
		e.preventDefault();
		e.stopPropagation();
	})
	.on("dragover dragenter", function () {
		$("#icon").empty();
		$("#icon").append("<i class='material-icons'>cloud_upload</i>");
	})
	.on("dragleave dragend drop", function () {
		$("#icon").empty();
		$("#icon").append("<i class='material-icons'>folder_open</i>");
	})
	.on("drop", function(e){
		$.each(e.originalEvent.dataTransfer.files, function (index, fileElement) {
			droppedFiles.push(fileElement);
		});

		$("#counter").html(droppedFiles.length + " files");
	});

	$("#file").on("change", function (e) {
		$.each(e.target.files, function (index, fileElement) {
			droppedFiles.push(fileElement);
		});

		$("#file").val("");

		$("#counter").html(droppedFiles.length + " files");

	});

	$("#submit").on("click", function () {
		uploadFiles();
	});

	$("#reset").on("click", function () {
		droppedFiles = [];
		$("#counter").html("0 files");
	});
});


function showloader(){
	$("#loader").removeClass("d-none");
}

function hideLoader(){
	$("#loader").addClass("d-none");
}

function uploadFiles() {
	if (!droppedFiles) {
		return false;
	}

	showloader();

	$.each(droppedFiles, function (index, fileElement) {
		storeMetadata(fileElement);
	});

	resetCounter();
	hideLoader();
}

function resetCounter(){
	droppedFiles = [];
	$("#counter").html("0 files");
}

function storeMetadata(fileElement) {
	var request = $.ajax({
		url: api_v1 + "files",
		method: "POST",
		datatype: "json",
		cache: false,
		data:{
			filename:fileElement.name
		}
	})
	.done(function (data, textStatus, jqXHR) {
		switch(jqXHR.status) {
		    case 201:
		    	var id = data.id;
		    	storeFile(id, fileElement, jqXHR);
		        break;
		    default:
		    	showMessageDialog("Results","This is unexpected. Looks like something wrong happened. Please try again.");
		}
	})
	.fail(function (jqXHR, textStatus, errorThrown) {

		switch(jqXHR.status) {
		    case 401:
		    	appendResult(fileElement.name + " : Unauthenticated.",2);
		        break;
		    case 403:
		    	appendResult(fileElement.name + " : Unauthorized.",2);
		        break;
		    case 400:
		    case 409:
		    case 404:
		    case 500:
		    	appendResult(fileElement.name + " : Error, please try again.",2);
		        break;
		    default:
		    	showMessageDialog("Results", "This is unexpected. Looks like something wrong happened. Please try again.");
		}
	})
	.always(function(data){

	});
}


function storeFile(id, fileElement, response){
	var ajaxData = new FormData();

	ajaxData.append("file", fileElement);

	$.ajax({
		url: response.getResponseHeader("Location") + "/content",
		method: "POST",
		processData: false,
    	contentType: false,
		cache: false,
		dataType:"text",
		data:ajaxData
	})
	.done(function(data, textStatus, jqXHR){
		switch(jqXHR.status) {
		    case 201:
		    appendResult("Success! The " + fileElement.name + " file was uploaded!", 1,jqXHR.getResponseHeader("Location"));
		        break;
		    default:
		    	showMessageDialog("Results","This is unexpected. Looks like something wrong happened. Please try again.");
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown){
		switch(jqXHR.status) {
		    case 401:
		    	appendResult(fileElement.name + " : Unauthenticated.",2);
		        break;
		    case 403:
		    	appendResult(fileElement.name + " : Unauthorized.",2);
		        break;
		    case 400:
		    case 404:
		    case 409:
		    case 500:
		    	appendResult(fileElement.name + " : Error, please try again.",2);
		        break;
		    default:
		    	showMessageDialog("Results", "This is unexpected. Looks like something wrong happened. Please try again.");
		}
	})
	.always(function(data){

	});
}

function showMessageDialog(title = null, body = null){
	$("#modalMessageLabel").empty();
	$("#modalMessageBody").empty();
	$("#modalMessageLabel").append(title);
	$("#modalMessageBody").append(body);
	$("#modalMessage").modal();
}

function dismiss(element){
	$(element).parent("div").parent("div").remove();
}

function getFileContent(id){
	window.open(api_v1 + "files/" + id + "/content", "_blank");
}

function appendResult(message, color = 1, url = null){
	var borderclass = "";
	var textclass = "";

	switch(color){
		case 1:
		borderclass = "border-success";
			textclass = "text-success";
			break;

		case 2:
			borderclass = "border-danger";
			textclass = "text-danger";
			break;
	}

	var newCard = $("<div class='card " + borderclass + " mb-3' ></div>");
	var newCardBody = $("<div class='card-body " + textclass + "'>");
	var newCardText = $("<p class='card-text'>" + message + "</p>");
	var newCardDismissButton = $("<button type='button' class='btn btn-sm btn-danger' onclick='dismiss(this)'>Dismiss</button>");

	$(newCardBody).append(newCardText);

	if (url) {
		var newCardDownloadButton = $("<a href='" + url + "' target='_blank' class='btn btn-sm btn-primary'>Download</a>");
		$(newCardBody).append(newCardDownloadButton);
	}

	$(newCardBody).append(newCardDismissButton);
	$(newCard).append(newCardBody);
	$("#results").append(newCard);

}
