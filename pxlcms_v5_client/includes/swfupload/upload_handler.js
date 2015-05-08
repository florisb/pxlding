function swfupload(id, sid, mis) {
	window['swfu' + id] = new SWFUpload({
			upload_url : "core/upload_image.php?sid="+sid+"&fid="+id,
			flash_url : "includes/swfupload/swfupload.swf",
			flash9_url : "includes/swfupload/swfupload_fp9.swf",
			
			// Button settings
			button_image_url: "img/button.png",
			button_width: "112",
			button_height: "23",
			button_placeholder_id: "image_browse_button_"+id,
			button_text: '<span class="theFont">Browse</span>',
			button_text_style: ".theFont { font-size: 12;font-family:'Calibri','Trebuchet MS',Arial,Helvetica,sans-serif;cursor:pointer;text-align:center;color:#555555; }",
			button_text_left_padding: 0,
			button_text_top_padding: 3,
			button_cursor: 'pointer',
			
			// The event handler functions are defined in handlers.js
			swfupload_preload_handler : swfPreLoad,
			//swfupload_load_failed_handler : loadFailed,
			file_queued_handler : swfUploadFileQueued,
			file_queue_error_handler : swfFileQueueError,
			upload_progress_handler : swfUploadProgress,
			upload_error_handler : swfUploadError,
			upload_complete_handler : swfUploadComplete,
			
			custom_settings : {
				progressTarget : "image_progress_"+id,
				cancelButtonId : "image_cancel_button_"+id,
				fieldId: id
			},
			
			file_types : "*.jpg;*.gif;*.png;*.jpeg",
			file_types_description: "Web Image Files",
			file_size_limit : (mis || "600KB")
		});
}

function swfPreLoad() {
	if (!this.support.loading) {
		alert("You need the Flash Player 9.028 or above to use SWFUpload.");
		return false;
	}
}

function swfUploadFileQueued(file, target) {
	var li = document.createElement('li');
	li.setAttribute('id', file.id);
	var name = document.createElement('span');
	name.appendChild(document.createTextNode(file.name));
	li.appendChild(name);
	var progress = document.createElement('span');
	progress.appendChild(document.createTextNode(' 0%'));
	li.appendChild(progress);
	if(target) {
		$(target).appendChild(li);
	} else {
		$(this.customSettings.progressTarget).appendChild(li);
	}
	$$('#'+file.id+' span')[0].addClassName('file');
	$$('#'+file.id+' span')[1].addClassName('progress');
	
	if(file.filestatus == -1) {
     	$(file.id).up('.upload_multiple').down('.upload').setStyle({
      		display: 'inline'
     	});
	}
}

function swfUploadProgress(file, bytesLoaded, bytesTotal) {
	var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);
	$(file.id).select('.progress')[0].innerHTML = " "+percent+"%";
	$(file.id).select('.progress')[0].setStyle({
		backgroundPosition: '-'+(2 * (100 - percent))+'px 0'
	});
	$(file.id).up('.upload_multiple').down('.cancel').setStyle({
		display: 'inline'
	});
}

function swfUploadError(file, errorCode, message) {
	switch (errorCode) {
	case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
		swfSetError(file, "Upload Error: " + message);
		break;
	case SWFUpload.UPLOAD_ERROR.MISSING_UPLOAD_URL:
		swfSetError(file, "Configuration Error");
		break;
	case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
		swfSetError(file, "Upload Failed.");
		break;
	case SWFUpload.UPLOAD_ERROR.IO_ERROR:
		swfSetError(file, "Server (IO) Error");
		break;
	case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
		swfSetError(file, "Security Error");
		break;
	case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
		swfSetError(file, "Upload limit exceeded.");
		break;
	case SWFUpload.UPLOAD_ERROR.SPECIFIED_FILE_ID_NOT_FOUND:
		swfSetError(file, "File not found.");
		break;
	case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
		swfSetError(file, "Failed Validation.  Upload skipped.");
		break;
	case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
		/*if (this.getStats().files_queued === 0) {
			document.getElementById(this.customSettings.cancelButtonId).disabled = true;
		}*/
		swfSetError(file, "Cancelled");
		//progress.setCancelled();
		break;
	case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
		swfSetError(file, "Stopped");
		break;
	default:
		swfSetError(file, "Unhandled Error: " + error_code);
		break;
	}
}

function swfUploadComplete(file) {
	uploadComplete(this.customSettings.fieldId);
}

function swfSetError(file, message) {
	$(file.id).select('.progress')[0].innerHTML = message;
	$(file.id).select('.progress')[0].setStyle({
		backgroundImage: 'none',
		backgroundColor: '#cd2525'
	});
}

function swfFileQueueError(file, errorCode, message) {
	swfUploadFileQueued(file, this.customSettings.progressTarget);
	switch (errorCode) {
	case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
		swfSetError(file, "File is too big.");
		break;
	case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
		swfSetError(file, "Cannot upload Zero Byte files.");
		break;
	case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
		swfSetError(file, "Invalid File Type.");
		break;
	case SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED:
		alert("You have selected too many files.  " +  (message > 1 ? "You may only add " +  message + " more files" : "You cannot add any more files."));
		break;
	default:
		if (file !== null) {
			swfSetError(file, "Unhandled Error");
		}
		break;
	}
}