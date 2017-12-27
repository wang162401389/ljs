<?php
    class tongdun {
        var $debug_config=array("url"=>array("submit"=>"https://credittest.api.tongdun.cn/preloan/apply",
                                                    "query"=>"https://credittest.api.tongdun.cn/preloan/report"),
                                   "partner_code"=>"ccfax","partner_key"=>"0d1d84faaa2f4f9d904aabcaa09e3435","app_name"=>"ccfax_web");

        var $product_config=array("url"=>array("submit"=>"https://credit.api.tongdun.cn/preloan/apply",
            "query"=>"https://credit.api.tongdun.cn/preloan/report"),
            "partner_code"=>"ccfax","partner_key"=>"bc246241f4a54e48a5af7099c2eadb72","app_name"=>"ccfax_web");

        var $config;
        function __construct(){
            $this->config=$this->product_config;
        }
        private function curlPost($url, $data) {
            $ch = curl_init ();
            curl_setopt ( $ch, CURLOPT_URL, $url );
            curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
            curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
            curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
            $data = curl_exec ( $ch );
            curl_close ( $ch );
            return $data;
        }
        private function curGet($url){
            $ch = curl_init ();
            curl_setopt ( $ch, CURLOPT_URL, $url );
            curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
            curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
            $data = curl_exec ( $ch );
            curl_close ( $ch );
            return $data;
        }
        private function createcurl_data($pay_params = array()) {
            $params_str = "";
            foreach ($pay_params as $key => $val ) {
                if (isset ( $val ) && ! is_null ( $val ) && @$val != "") {
                    $params_str .= "&" . $key . "=" . trim ( $val );
                }
            }
            if ($params_str) {
                $params_str = substr ($params_str, 1 );
            }
            return $params_str;
        }
        public function submit($name,$tel,$id){
            //get
            $info=$name.$tel.$id;
            $cache_id=session("tongdun_".md5($info));
            if($cache_id){
                return 1;
            }
            $url_parm['partner_code']=$this->config['partner_code'];
            $url_parm['partner_key']=$this->config['partner_key'];
            $url_parm['app_name']=$this->config['app_name'];
            $url=$this->config['url']['submit']."?".http_build_query($url_parm);
            //准备data数据
            $data['name']=$name;
            $data['mobile']=$tel;
            $data['id_number']=$id;
            $sent_data=$this->createcurl_data($data);
            $result=$this->curlPost($url,$sent_data);
            $json=json_decode($result);
            if($json->success){
                $id=$json->report_id;
                session("tongdun_".md5($info),$id,3600);
                return 1;
            }
            return 0;
        }
        private function get_risk_info($risk_info){
            $result="";
            if(isset($risk_info->discredit_times)){
                $result.="失信次数".$risk_info->discredit_times."  ";
            }
            if(isset($risk_info->overdue_details)){
                foreach($risk_info->overdue_details as $key=>$val){
                    if(isset($val['overdue_amount'])){
                        $result.="逾期金额".$val['overdue_amount']."   ";
                    }
                    if(isset($val['overdue_count'])){
                        $result.="逾期笔数".$val['overdue_count']."   ";
                    }
                    if(isset($val['overdue_day'])){
                        $result.="逾期天数".$val['overdue_day']."   ";
                    }
                }
            }
            if(isset($risk_info->platform_count)){
                $result.="多头借款".$risk_info->platform_count;
            }
            return $result;
        }
        private function  create_html($json){
            $html='<div class="result">';

            $html.='<div class="title">同盾检查结果</div>';

            $html.='<div class="item">';
            if($json->final_decision!=""){
                $decision=array("Accept"=>"<font color='green'>建议通过</font>","Review"=>"<font color='red'>建议审核</font>","Reject"=>"<font color='red'>建议拒绝</font>");
                $html.='<div class="suggest">';
                $html.='同盾风控建议'.$decision[$json->final_decision];
                $html.='</div>';
            }
            if($json->final_score!=""){
                $html.='<div class="score">';
                $html.='同盾风控分数'.$json->final_score;
                $html.='</div>';
            }
            $html.='</div>';


            $html.='<div class="table_container">';
            $html.="<table width='100%' border='1' style='border-collapse: collapse; border:1px;' >";
            $html.="    <thead>";
            $html.="        <tr>";
            $html.='            <th class="row1">检查项目</th>';
            $html.='            <th class="row2">风险等级</th>';
            $html.='            <th class="row3">备注</th>';
            $html.="        </tr>";
            $html.="    </thead>";
            $html.="<tbody>";
            if(isset($json->risk_items)&&count($json->risk_items)!=0){
                foreach($json->risk_items as $key=>$val){
                    if(isset($val->item_detail)){
                        $html_detail=$this->get_risk_info($val->item_detail);
                    }else{
                        $html_detail="无";
                    }

                    $html.="<tr>";
                    $html.='    <td class="row1">'.$val->item_name.'</td>';
                    $html.='    <td class="row2">'.$val->risk_level.'</td>';
                    $html.='    <td class="row3">'.$html_detail.'</td>';
                    $html.="</tr>";
                }

            }
            $html.="</tbody>";
            $html.="</table>";
            $html.='</div>';
            $html.="</div>";
            return $html;
        }
        public function get_user_result($name,$tel,$id){
             $info=$name.$tel.$id;
            $cache_id=session("tongdun_".md5($info));
            if( $cache_id==""){
                echo "fail";
            }else{
                $url_parm['partner_code']=$this->config['partner_code'];
                $url_parm['partner_key']=$this->config['partner_key'];
                $url_parm['app_name']=$this->config['app_name'];
                $url_parm['report_id']=$cache_id;
                $url=$this->config['url']['query']."?".http_build_query($url_parm);
                $result=$this->curGet($url);
                $json=json_decode($result);
                if($json->success){
                    $html=$this->create_html($json);
                }else{
                    $html="fail";
                }

                return $html;
            }

        }
    }
?>