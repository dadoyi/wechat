ws = new WebSocket("ws://47.105.170.129:9001");
// ws = new WebSocket("ws://127.0.0.1:9001");

var user_id = $('#user_id').val();
console.log(user_id);
var to_user_id;
var from_id;
var img = $('#me').attr('src');
ws.onopen = function(e){
    // 获取群聊信息
    $.ajax({
        dataType:'json',
        type:'post',
        url:'index/index/getGroupId',
        data: {
            'user_id':user_id ,
        },
        success:function (res) {
            if(res.code == 0){
                var arr = {'type':'init','message':'connect start__'+user_id,'user_id':user_id ,'data':res.data};
                var data = JSON.stringify(arr);
                ws.send(data)
            }
        }
    });

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
                changeLeftContent(data.message,data.user_id,from_id);
                chat.innerHTML += '<li class="other"><img src="'+other_img+'"><span>'+data.message+'</span></li>';
            }
            break;
        case 'send':
            console.log(data);
            if(data.user_id !== user_id){
                console.log(2222222222);
                changeLeftContent(data.message,0,from_id);
                chat.innerHTML += '<li class="other"><img src="'+data.img+'"><span>'+data.message+'</span></li>';
            }
            break;
        default :
            console.log('心跳检测：'+ data.type);
    }
};
