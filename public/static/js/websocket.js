ws = new WebSocket("ws://0.0.0.0:9001");

var user_id = $('#user_id').val();
console.log(user_id);
var to_user_id = 2;

ws.onopen = function(e){
    var arr = {'type':'init','message':'connect start__'+user_id,'user_id':user_id};
    var data = JSON.stringify(arr);
    ws.send(data)
};

ws.onerror = function(e){
    console.log(e);
}

ws.onmessage = function(e){
    var data = JSON.parse(e.data);
    var chat = document.getElementById('chatbox');
    var me_img = $('#me').attr('src');
    var other_img = $('#other_img').val();

    switch(data.type){
        case 'init':
            if(data.user_id === user_id){
                console.log(data.message);
            }
            // ws.send(JSON.stringify({'type':'push','user_id':user_id,'to_user_id':to_user_id,'message':'connect start__'+user_id}));
            break;
        case 'push':
            if(data.user_id !== user_id){
                changeLeftContent(data.message,data.user_id);
                chat.innerHTML += '<li class="other"><img src="'+other_img+'"><span>'+data.message+'</span></li>';
            }
            break;
        case 'send':
            // console.log(data.message);
            break;
        default :
            console.log('心跳检测：'+ data.type);
    }
};
