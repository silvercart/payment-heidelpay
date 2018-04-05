
var paymentFrameIframe = document.getElementById('paymentIframe');

targetOrigin = getDomainFromUrl(paymentFrameIframe.src);
paymentFrameForm = document.getElementById('paymentFrameForm');

if (paymentFrameForm.addEventListener) {
	paymentFrameForm.addEventListener('submit', sendMessage);
} else if (paymentFrameForm.attachEvent) {
	paymentFrameForm.attachEvent('onsubmit', sendMessage); 
}

function sendMessage(e) {	
	if (e.preventDefault) {
        e.preventDefault();
    } else {
        e.returnValue = false;
    }
	
	var data = {}; 
	
	for (var i = 0, len = paymentFrameForm.length; i < len; ++i) { 
		var input = paymentFrameForm[i]; 
		if (input.name) {
            data[input.name] = input.value;
        }
	}
	
	paymentFrameIframe.contentWindow.postMessage(JSON.stringify(data), targetOrigin);
}

function getDomainFromUrl(url) { 
	var arr = url.split("/");
	return arr[0] + "//" + arr[2]; 
}

if (window.addEventListener) {
	window.addEventListener('message', receiveMessage);
} else if (window.attachEvent) {
    window.attachEvent('onmessage', receiveMessage);
}

function receiveMessage(e) {
	if (e.origin !== targetOrigin) {
		return; 
	}
	var antwort = JSON.parse(e.data);
}