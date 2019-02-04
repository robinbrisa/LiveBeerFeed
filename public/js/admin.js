$(document).ready(function() {
	if ($('#validation-messages').length !== 0) {
		pushServerValidation();	
	}
});

function pushServerValidation(){
	var conn = new ab.Session(websocket, function() {
        conn.subscribe("validation", function(topic, data) {
        	handleNewMessageToValidate(data);
        });
    }, function() {
        console.warn('WebSocket connection closed');
        setTimeout(function(){
            location = '/admin/validate'
        }, 20000)
    }, {
        'skipSubprotocolCheck': true
    });	
}

function handleNewMessageToValidate(data) {
	$('#validation-messages').prepend(data.message);
	navigator.vibrate(300, 150, 150, 150, 150);
}