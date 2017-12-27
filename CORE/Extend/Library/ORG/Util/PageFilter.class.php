<?php
import("ORG.Util.Page");
class PageFilter extends Page{
         private  $filter;

         public function __construct($totalRows,$map,$listRows='',$parameter='') {
                foreach($map as $key=>$val){
                    $this->filter.="&".$key."=".$val;
                }
                parent::__construct($totalRows,$listRows,$parameter);
         }
         public function show() {
             if(0 == $this->totalRows) return '';
             $p = $this->varPage;
             $nowCoolPage      = ceil($this->nowPage/$this->rollPage);
             $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;
             $parse = parse_url($url);
             $idtagert = ($parse['fragment'])?"#".$parse['fragment']:"";
             if(isset($parse['query']) || isset($parse['fragment'])) {
                 parse_str($parse['query'],$params);
                 unset($params[$p]);
                 $querycount = count($params);
                 $url   =  $parse['path'].'?'.http_build_query($params);
             }else{
                 $querycount = 0;
             }
             $pspan = ($querycount==0)?"":"&";
             //上下翻页字符串
             $upRow   = $this->nowPage-1;
             $downRow = $this->nowPage+1;
             if ($upRow>0){
                 $upPage="<a href='".$url.$pspan.$p."=$upRow{$idtagert}".$this->filter."' class='prevnext'>".$this->config['prev']."</a>";
             }else{
                 $upPage="<a href='javascript:void(0);' class='prevnext delcolor'>".$this->config['prev']."</a>";
             }

             if ($downRow <= $this->totalPages){
                 $downPage="<a href='".$url.$pspan.$p."=$downRow{$idtagert}".$this->filter."' class='prevnext'>".$this->config['next']."</a>";
             }else{
                 $downPage="<a href='javascript:void(0);' class='prevnext delcolor'>".$this->config['next']."</a>";
             }
             // << < > >>
             if($nowCoolPage == 1){
                 $theFirst = "";
                 $prePage = "";
             }else{
                 $preRow =  $this->nowPage-$this->rollPage;
                 $prePage = "<a href='".$url.$pspan.$p."=$preRow{$idtagert}".$this->filter."' >上".$this->rollPage."页</a>";
                 $theFirst = "<a href='".$url.$pspan.$p."=1".$this->filter." ' >".$this->config['first']."</a>";
             }
             if($nowCoolPage == $this->coolPages){
                 $nextPage = "";
                 $theEnd="";
             }else{
                 $nextRow = $this->nowPage+$this->rollPage;
                 $theEndRow = $this->totalPages;
                 $nextPage = "<a href='".$url.$pspan.$p."=$nextRow{$idtagert}".$this->filter."' >下".$this->rollPage."页</a>";
                 $theEnd = "<a href='".$url.$pspan.$p."=$theEndRow{$idtagert}".$this->filter."' >".$this->config['last']."</a>";
             }
             // 1 2 3 4 5
             $linkPage = "";
             for($i=1;$i<=$this->rollPage;$i++){
                 $page=($nowCoolPage-1)*$this->rollPage+$i;
                 if($page!=$this->nowPage){
                     if($page<=$this->totalPages){
                         $linkPage .= "&nbsp;<a href='".$url.$pspan.$p."=$page{$idtagert}".$this->filter."'>&nbsp;".$page."&nbsp;</a>";
                     }else{
                         break;
                     }
                 }else{
                     if($this->totalPages != 1){
                         $linkPage .= "&nbsp;<span class='current'>".$page."</span>";
                     }
                 }
             }
             $pageStr	 =	 str_replace(
                 array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),
                 array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),$this->config['theme']);
             return $pageStr;
         }

     }
?>