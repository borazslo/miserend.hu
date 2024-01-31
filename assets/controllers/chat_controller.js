import { Controller } from '@hotwired/stimulus';
import Autocomplete from 'bootstrap5-autocomplete';

export default class extends Controller {
    static targets = ['message']

    connect() {
        this.sendInProgress = false
        this.initEventListeners()
    }

    initEventListeners() {
        window.addEventListener('focus', this.windowDidFocus.bind(this))
        window.addEventListener('blur', this.windowDidBlur.bind(this))
    }

    windowDidFocus(event) {
        console.log('focus')
    }

    windowDidBlur(event) {
        console.log('blur')
    }

    sendButtonDidClick(event) {
        event.preventDefault()

        this.sendMessage()
    }

    async sendMessage() {
        let message = this.messageTarget.value

        if (message.length === 0) {
            return
        }

        if (this.sendInProgress) {
            return
        }

        this.sendInProgress = true

        const response = await fetch('/ajax/chat/send', {
            method: 'POST', // *GET, POST, PUT, DELETE, etc.
            cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
            credentials: "same-origin", // include, *same-origin, omit
            headers: {
                "Content-Type": "application/json",
            },
            redirect: "follow", // manual, *follow, error
            referrerPolicy: "no-referrer", // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
            body: JSON.stringify(data), // body data type must match "Content-Type" header
        });
        return response.json();

        this.sendInProgress = false

        console.log(message)
    }
}

/*
	var focus = true;
	var loadmore = false;
	var title = document.title;

 	$(document).ready(function() {


		$(window).focus(function(){
			focus = true;
			$("#chat_comments").data('unread',0);
			setTimeout(function(){
			  document.title = title;
			}, 500);

		});
		$(window).blur(function(){
			focus = false;
		});



 		$('#chat_text').each(function(){
    		this.contentEditable = true;
		});

 		$('#chat_text').keydown(function(event) {
    		if (event.keyCode == 13) {
 				if (!event.shiftKey) chat_send();
     		}
     	});
 		$("body").on('click', '#chat_submit', function() {
			chat_send();
 		});

		$("body").on('click', '.response_closed', function() {
			if($("#chat_text").html().match(/^(<i>.*?<\/i>)$/ig) ) { $("#chat_text").html(''); }


			if($("#chat_text").text().match(/^([$]{1})/ig)) {}
			else {
				//$("#chat_text").prepend("<img class='lakat link' title='Válasz csak neki' src=img/lakat.gif align=absmiddle height='13' border=0 style='margin-right:3px'>" + $(this).data('to') + ": " );
				var html = "<span class='lakat link' title='Zárt válasz csak neki'>$</span>" + $(this).data('to') + ":&nbsp;";
				placeCaretAtEnd(document.getElementById("chat_text") );
			}
 		});
		$("body").on('click', '.response_open', function() {
			if($("#chat_text").html().match(/^(<i>.*?<\/i>)$/ig) ) { $("#chat_text").html(''); }
			var html = "<span class='lakat link' title='Nyilvános válasz neki'>@</span>" + $(this).data('to');
			if($("#chat_text").html() == '' ) $("#chat_text").append(html + ":&nbsp;");
			else  $("#chat_text").append("&nbsp;" + html + "&nbsp;");
			placeCaretAtEnd(document.getElementById("chat_text") );

 		});

		$("body").on('click', '#chat_loadnext', function() {
			$.post( "/ajax/chat", { action: "load", date: $( this ).prev().data("date"), rev: true }, function( data ) {
	  			if(data.result === "loaded") {
	  				var index;
					for (index = 0; index < data.comments.length; ++index) {
					 	$('#chat_loadnext').before(data.comments[index].html);
				    }
	  			}
	  			else {
					$("#chat_text").html('<i>Hiba történt betöltés közben.</i>');
	  				return false;
	  			}
		},'json');
 		});

    });

		function placeCaretAtEnd(el) {
		    el.focus();
		    if (typeof window.getSelection != "undefined"
		            && typeof document.createRange != "undefined") {
		        var range = document.createRange();
		        range.selectNodeContents(el);
		        range.collapse(false);
		        var sel = window.getSelection();
		        sel.removeAllRanges();
		        sel.addRange(range);
		    } else if (typeof document.body.createTextRange != "undefined") {
		        var textRange = document.body.createTextRange();
		        textRange.moveToElementText(el);
		        textRange.collapse(false);
		        textRange.select();
		    }
		}

		var content_id = 'chat_text';

		max = 250;
		//binding keyup/down events on the contenteditable div
		$('#'+content_id).keyup(function(e){ check_charcount(content_id, max, e); });
		$('#'+content_id).keydown(function(e){ check_charcount(content_id, max, e); });
		function check_charcount(content_id, max, e)
		{
		    if(e.which != 8 && $('#'+content_id).text().length > max)
		    {
		       // $('#'+content_id).text($('#'+content_id).text().substring(0, max));
		    	alert('Sajnos hosszabbat nem lehet írni!');
		      	e.preventDefault();
		    }
		}


	var c = 1;
	var lim = 10;
	setInterval(function(){
		chat_update('update');
		if(c === lim) {
			c = 1;
			chat_users();
		} else c++;
	},5000);


	function chat_send() {
		if($("#chat_text").text() != "") {

			var string = $("#chat_text").html();
			string = $('<div/>').html(string.replace(/(<br>)/ig,"\\n"));
			string = $('<div/>').html(string.html().replace(/^(<img.*?>)/ig,"$"));

	 		var text = $(string).text();
	 		$("#chat_text").html('<i>Küldés folyamatban...</i>');
	 		$.post( "/ajax/chat", { action: "save", text: text }, function( data ) {
	  			if(data.result === "saved") {
					$("#chat_text").html('<i>Frissítés folyamatban...</i>');
	  				chat_update('clear');
	  			} else {
	  				if(data.text != "") {
						$("#chat_text").html('<i>' + data.text + '</i>');
	  				} else {
						$("#chat_text").html('<i>Nem sikerült menteni. Elnézést.</i>');
					}
	  			}
			}, "json");
		}
	}

	function chat_update(clear) {
		$.post( "/ajax/chat", { action: "load", date: $("#chat_comments").data("last") }, function( data ) {

	  			if(data.result === "loaded") {
	  				var index;
					for (index = 0; index < data.comments.length; ++index) {
					    $(data.comments[index].html).hide().prependTo('#chat_comments').show('slow');

					}

					if(data.new > 0) {
						$("#chat_comments").data('last',data.comments[data.comments.length-1].datum_raw);
						if(loadmore != true ) $("#chat_comments div").slice(10,-1).each( function() { $(this).hide('slow',function() { $( this ).remove();});});
					}

					if(focus == false) {
						var unread = $("#chat_comments").data('unread') + data.alert;
						$("#chat_comments").data('unread',unread);

						if (unread > 0) {
				      		setTimeout(function(){
				        		document.title = '(' + unread + ') ' + title;
				      		}, 500);
				    	}
				    } else 	$("#chat_comments").data('unread',0);

				    if(clear === 'clear') {
				    	$("#chat_text").html('');
				    }
	  				return true;
	  			}
	  			else {
					$("#chat_text").html('<i>Hiba történt a frissítés közben.</i>');
	  				return false;
	  			}
		},'json');
	}

	function chat_users() {
		$.post( "/ajax/chat", { action: "getusers" }, function( data ) {
	  			if(data.result === "loaded") {
	  				$("#chat_users").html(data.text);
	  			}
	  	},'json');
	}
 */
