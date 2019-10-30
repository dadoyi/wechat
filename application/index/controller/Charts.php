<?php

namespace app\index\controller;

use think\Controller;
use think\Db;

class Charts extends Controller
{
    public function index()
    {
        $data = Db::table('li_month')
            ->leftJoin('li_money','li_month.id=li_money.m_id')->select();
        //
        // $month = [];
        // foreach ($data as $k => $v){
        //     if($v['status'] == 1){
        //         $month[] = $v['year'].'-'.$v['month'].'（前）';
        //     }else{
        //         $month[] = $v['year'].'-'.$v['month'].'（后）';
        //     }
        // }
        // halt($data);

        return view();
    }

    public function getInfo()
    {
        $data = Db::table('li_month')
            ->leftJoin('li_money','li_month.id=li_money.m_id')
            ->order('li_month.id ')
            ->select();

        $month = [];
        $zhaoshang = [];
        $jianshe = [];
        $guangda = [];
        $pingan = [];
        $jiaotong = [];
        $huaxia = [];
        $zheshang = [];
        $zhaolian = [];
        $huabei = [];
        $jiebei = [];
        $total = [];
        foreach ($data as $k => $v){

            if($v['status'] == 1){
                $month[] = $v['year'].'-'.$v['month'].'（还款前）';
            }else{
                $month[] = $v['year'].'-'.$v['month'].'（还款后）';
            }
            $zhaoshang[] = $v['zhaoshang'];
            $jianshe[] = $v['jianshe'];
            $guangda[] = $v['guangda'];
            $pingan[] = $v['pingan'];
            $jiaotong[] = $v['jiaotong'];
            $huaxia[] = $v['huaxia'];
            $zheshang[] = $v['zheshang'];
            $zhaolian[] = $v['zhaolian'];
            $huabei[] = $v['huabei'];
            $jiebei[] = $v['jiebei'];
            $total[] = $v['total'];
        }

        $data = [
            'month' => $month,
            'info' => [
                [
                    'name' => '招商银行',
                    'type' => 'line',
                    'stack' => '总量',
                    'data' => $zhaoshang
                ],
                [
                    'name' => '建设银行',
                    'type' => 'line',
                    'stack' => '总量',
                    'data' => $jianshe
                ],
                [
                    'name' => '光大银行',
                    'type' => 'line',
                    'stack' => '总量',
                    'data' => $guangda
                ],
                [
                    'name' => '平安银行',
                    'type' => 'line',
                    'stack' => '总量',
                    'data' => $pingan
                ],
                [
                    'name' => '交通银行',
                    'type' => 'line',
                    'stack' => '总量',
                    'data' => $jiaotong
                ],
                [
                    'name' => '华夏银行',
                    'type' => 'line',
                    'stack' => '总量',
                    'data' => $huaxia
                ],
                [
                    'name' => '浙商银行',
                    'type' => 'line',
                    'stack' => '总量',
                    'data' => $zheshang
                ],
                [
                    'name' => '招联金融',
                    'type' => 'line',
                    'stack' => '总量',
                    'data' => $zhaolian
                ],
                [
                    'name' => '蚂蚁花呗',
                    'type' => 'line',
                    'stack' => '总量',
                    'data' => $huabei
                ],
                [
                    'name' => '蚂蚁借呗',
                    'type' => 'line',
                    'stack' => '总量',
                    'data' => $jiebei
                ],
                [
                    'name' => '总计',
                    'type' => 'line',
                    'stack' => '总量',
                    'data' => $total
                ],
            ],
        ];

        return json(['code' => 200 ,'data' => $data]);
    }
}