<?php
/**
 * 首次投资统计
 */
class AggregateAction extends HCommonAction
{
    public function _initialize()
    {
        $ips=array("127.0.0.1","115.159.114.87","115.159.65.162","192.168.0.5","192.168.0.210","218.17.34.102","172.16.20.78","172.16.20.79");
        $userip=$_SERVER['REMOTE_ADDR'];
        if (!in_array($userip, $ips)) {
            //   $this->error('非法ip操作');
            Log::write("异步任务,非正常访问".$userip);
            exit;
        }
    }

    /**
     * 根据　borrow_invest　向 invest_aggregate 迁移数据
     * @return [type] [description]
     */
    public function migrate(){
        //查询所有borrow_investor记录
        $con['id'] = array("NEQ", 0);
        $investList = M('borrow_investor')->field('id,investor_uid,investor_capital,add_time')->where($con)->order('id asc')->select();

        $count = 0;
        //遍历所有记录，写入invest_aggregate 表
        foreach ($investList as $key => $value) {
            $isExist = M('invest_aggregate')->where(array('uid'=>$value['investor_uid']))->find();
              
            //如果存在该用户的记录，遍历下一条
            if($isExist)
                continue;

            $data['uid'] = $value['investor_uid'];
            $data['borrow_investor_id'] = $value['id'];
            $data['first_invest_amount'] = $value['investor_capital'];
            $data['add_time'] = $value['add_time'];
            $res = M('invest_aggregate')->add($data);
            if($res == false){
                exit($value['id']." write error <br/>");
            }else{
                echo $value['investor_uid'].' write success  ';
                $count++;
            }

        }
        echo "<br/> number = ".$count."<br/>";  
        exit('success');
    }   

    /**
     * 遍历　invest_aggregate ,取add_time 和当月第一天比较，如果小于当月第一天
     * @return [type] [description]
     */
    public function recursive(){

        list($startTime, $endTime) = $this->getLastMonthRange();

        //查询上月所有记录（本月的要到下个月才出结果)
        $cond['complete'] = 0;
        $cond['add_time'] = array('lt', $endTime);
        $investList = M('invest_aggregate')->where($cond)->select();

        foreach ($investList as $key => $value) {
            $da = date('Y-m-d', $value['add_time']);

            $url = "http://tp.dev.com/home/synchronized/investAggregate";
            $data['date'] = $da;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//此处$data就是上面的参数数组;
            $re = curl_exec($ch);
            //错误检查
            if (curl_error($ch)){
                echo "error: ".curl_error($ch).' '.curl_getinfo($ch);
                curl_close($ch);
            }

            curl_close($ch);

        }

        exit('success');

    }

    /**
     * 获取上个月的timestamp 时间段
     * @return [type] array
     */
    private function getLastMonthRange()
    {
        //查询时间
        $first_day = 'first day of last month';
        $dt=date_create($first_day);
        $first_day_morning =  $dt->format('Y-m-d 00:00:00');
        $first = strtotime($first_day_morning);

        $last_day = "last day of last month";
        $dt=date_create($last_day);
        $last_day_night =  $dt->format('Y-m-d 23:59:59');
        $last = strtotime($last_day_night);

        $month[] = $first;
        $month[] = $last;
        return $month;
    }
}
