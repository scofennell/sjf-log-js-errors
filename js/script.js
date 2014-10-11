/**
 * When there is a JS error, log that error in a page.
 * 
 * @package WordPress
 * @subpackage SJF_Log_JS_Errors
 * @since SJF_Log_JS_Errors 0.1
 */

/**
 * When there is a JS error, log that error in a page.
 */
function logError( details ) {
					
	 var data = {

		// This value corresponds with a call to "localize script" in the php code.
		action: 'sjf_lje_ajax',

		// The $_REQUEST value that will be available for php.
		log: JSON . stringify({ context: navigator . userAgent, details: details })
	   
	};

	// Grab the second half of the post content and append it to the firs half of the post content.
	jQuery.post( sjf_lje . ajaxurl, data );
	
}

/**
 * When there is an error, call the log funtion, passing it the message, file, and line number.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/API/GlobalEventHandlers.onerror
 * @return boolean Always returns false. 
 */
window.onerror = function( message, file, line ) {
	
	logError( file + ':' + line + '\n\n' + message );
	
	return false;
	
}