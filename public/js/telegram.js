sound_message = new Audio('/sounds/message.mp3');

function htmlSpecialChars(text) {

  return text
  .replace(/&/g, "&amp;")
  .replace(/"/g, "&quot;")
  .replace(/'/g, "&#039;")
  .replace(/</g, "&lt")
  .replace(/>/g, "&gt");

}

function getContent(timestamp)
{
    var queryString = {'timestamp' : timestamp};

    $.ajax(
        {
            type: 'GET',
            cache: false,
            url: '/telegram_dialogs/server/'+dialogID+'/'+timestamp,
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

	d_name = "Я";
	if(obj.id == lastID)
		d_name = obj.name;

                $(".list").prepend("<div class='alert alert-warning' role='alert'><b>"+htmlSpecialChars(d_name)+"</b>: "+htmlSpecialChars(obj.data_from_file)+"<br><span class='glyphicon glyphicon-time' aria-hidden='true'></span> "+htmlSpecialChars(resTxt)+"</div>");
                getContent(obj.timestamp);


            }
        }
    );
}

// initialize jQuery
$(function() {
   getContent(Math.round(new Date()/1000)+1);
});