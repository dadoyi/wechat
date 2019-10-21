<?php
namespace app\index\controller;

use think\Controller;
use think\Db;

class Index extends Controller
{
    public function index()
    {
        $param = $this->request->param();
        $data = Db::table('user')->where('id' ,'neq',$param['id'])->select();
        $this->assign('user_id',$param['id']);
        $userInfo = Db::table('user')->where('id',$param['id'])->find();
        return $this->fetch('index/index',['data' => $data,'user_info' => $userInfo]);
    }

    public function hello($name = 'ThinkPHP5')
    {
        return 'hello,' . $name;
    }

    public function getUserTalkHistory()
    {
        $param = $this->request->param();
        $where = [
            'user_id' => $param['user_id'],
            'to_user_id' => $param['to_user_id'],
        ];
        $whereOr = [
            'user_id' => $param['to_user_id'],
            'to_user_id' => $param['user_id'],
        ];
        $data = Db::table('talk_history')
            ->where(function ($query) use ($where){
                $query->where($where);
            })->whereOr(function ($query) use ($whereOr){
                $query->where($whereOr);
            })->leftJoin('user','user.id=talk_history.user_id')
            ->order('talk_history.id')
            ->field('talk_history.*,user.head_img')
            ->select();
        // halt(Db::table('talk_history')->getLastSql());
        return json(['code' => 0,'msg'=>'success','data' => $data ?? []]);
    }


    public function saveTalkInfo()
    {
        $param = $this->request->param();
        $data = [
            'user_id' => $param['user_id'],
            'to_user_id' => $param['to_user_id'],
            'content' => $param['content'],
            'type' => 'str',
            'status' => $param['status'],
            'created_at' => date('Y-m-d H:i:s',time()),
            'updated_at' => date('Y-m-d H:i:s',time()),
        ];

        $res = Db::table('talk_history')->insert($data);
        if($res){
            return json(['code' => 0,'msg'=>'success']);
        }else{
            return json(['code' => 500,'msg'=>'error']);
        }
    }
}
