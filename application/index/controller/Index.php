<?php
namespace app\index\controller;

use think\Controller;
use think\Db;

class Index extends Controller
{
    const SINGLE_TYPE = 1;
    const GROUP_TYPE = 2;


    public function index()
    {
        $param = $this->request->param();
        $user_id = $param['id'];
        $this->assign('user_id',$param['id']);
        $userInfo = Db::table('user')->where('id',$user_id)->find();
        $data_1 = $this->getSingleTalk($user_id);
        $data_2 = $this->getRoomList($user_id);
        $data = array_merge($data_1,$data_2);
        $order = array_column($data,'date_time');
        array_multisort($order,SORT_DESC,$data);

        return $this->fetch('index/index',['data' => $data,'user_info' => $userInfo]);
    }

    /**
     * 单聊
     * @param $user_id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function getSingleTalk($user_id)
    {
        $where = ['user_id'=> $user_id];
        $whereOr = ['to_user_id'=> $user_id];
        $data = Db::table('single_talk')
            ->where(function ($query) use ($where) {
                $query->where($where);
            })->whereOr(function ($query) use ($whereOr){
                $query->where($whereOr);
            })->field('id,user_id,to_user_id')
            ->select();
        foreach ($data as $k => &$v){
            if($v['user_id'] == $user_id){
                $v['user_id'] = $v['to_user_id'];
            }
            unset($v['to_user_id']);
        }

        $user = array_column($data,'user_id','user_id');
        $userData = Db::table('user')->whereIn('id',$user)->field('id,user_name,head_img')->select();
        $userData = array_column($userData,null,'id');

        foreach ($data as $k => &$v){
            if(!empty($userData[$v['user_id']])){
                $v['name'] = $userData[$v['user_id']]['user_name'];
                $v['img'] = $userData[$v['user_id']]['head_img'];
                $v['from_type'] = self::SINGLE_TYPE; // 单聊
            }
            $where_1 = ['user_id'=> $v['user_id'],'to_user_id' => $user_id];
            $whereOr_1 = ['to_user_id'=> $v['user_id'], 'user_id' => $user_id];
            $content = Db::table('talk_history')
                ->where(function ($query) use ($where_1){
                    $query->where($where_1);
            })->whereOr(function ($query) use ($whereOr_1){
                    $query->where($whereOr_1);
                })->order('created_at desc')
                ->find();
            $v['type'] = $content['type'];
            $v['content'] = $content['content'];
            $v['time'] = date('H:i',strtotime($content['created_at']));
            $v['date_time'] = strtotime($content['created_at']);
        }
        return $data;
    }

    public function getRoomList($user_id)
    {
        $data = Db::table('room_user')
            ->where('room_user.user_id',$user_id)
            ->leftJoin('room','room.id=room_user.room_id')
            ->field('room.id,room.img,room.room_name as name')
            ->select();
        foreach ($data as $k => &$v){
            $content = Db::table('group_talk_history')->where('room_id',$v['id'])->order('created_at desc')->find();
            $v['user_id'] = 0;
            $v['from_type'] = self::GROUP_TYPE;
            $v['type'] = $content['type'];
            $v['content'] = $content['content'];
            $v['time'] = date('i:s',strtotime($content['created_at']));
            $v['date_time'] = strtotime($content['created_at']);
        }
        return $data;

    }

    public function getGroupId()
    {
        $param = $this->request->param();
        $group = Db::table('room_user')
            ->where('user_id',$param['user_id'])
            ->select();
        $group = array_column($group,'room_id','room_id');
        return json(['code' => 0,'msg'=>'success','data' => $group ?? []]);
    }

    /**
     * 获取单聊 + 群记录
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUserTalkHistory()
    {
        $param = $this->request->param();
        if($param['from_type'] == self::GROUP_TYPE){
            $data = Db::table('group_talk_history')
                ->where('group_talk_history.room_id',$param['from_id'])
                ->leftJoin('user','user.id=group_talk_history.user_id')
                ->field('group_talk_history.*,user.head_img')
                ->select();
        }else{
            $where = [
                'user_id' => $param['user_id'],
                'to_user_id' => $param['to_user_id'],
                'from_id' => $param['from_id'],
            ];
            $whereOr = [
                'user_id' => $param['to_user_id'],
                'to_user_id' => $param['user_id'],
                'from_id' => $param['from_id'],
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
        }
        return json(['code' => 0,'msg'=>'success','data' => $data ?? []]);
    }


    /**
     * 保存单聊内容
     * @return \think\response\Json
     */
    public function saveTalkInfo()
    {
        $param = $this->request->param();
        halt($param);
        $data = [
            'user_id' => $param['user_id'],
            'to_user_id' => $param['to_user_id'],
            'content' => $param['content'],
            'type' => 1, // 字符串
            'status' => $param['status'],
            'from_id' => $param['from_id'],
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
