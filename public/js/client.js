
sound_message = new Audio('/sounds/message.mp3');

function getContent(timestamp)
{
    var queryString = {'timestamp' : timestamp};

    $.ajax(
        {
            type: 'GET',
            cache: false,
            url: '/mail/server/'+timestamp,
            data: queryString,
            success: function(data){
if(data == "0"){
	getContent(Math.round(new Date()/1000)+1);
	return 0;
	}
	
	if(data == "r"){
	$('div.alert').css('background-color', '#dff0d8');
	getContent(Math.round(new Date()/1000)+1);
	return 0;
	}

 var obj = jQuery.parseJSON(data);
       var tm = new Date();
       var resTxt = '';
       resTxt += tm.getDate() + "-" + (tm.getMonth() + 1) + "-" + tm.getFullYear();
       resTxt += " в " + tm.getHours() + ":"+ tm.getMinutes();
       sound_message.play();
                $(".list").prepend("<div class='alert alert-warning' role='alert'><b>"+obj.name+"</b>: "+obj.data_from_file+"<br><span class='glyphicon glyphicon-time' aria-hidden='true'></span> "+resTxt+"</div>");
                getContent(obj.timestamp);	

               
            }
        }
    );
}

// initialize jQuery
$(function() {
   getContent(Math.round(new Date()/1000)+1);
});
