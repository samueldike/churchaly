class Notifaya { 
    notify(message){
    	$("body").append('<div id="notifaya">'+message+'</div>'); 
    	$("")
		var slideout = document.getElementById('notifaya');
		slideout.classList.toggle('notifaya-visible');
    }
} 