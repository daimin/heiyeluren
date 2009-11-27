<?php
/**
 * Class: Split Page class
 * Description: 
 *		项目中使用到的分页函数集合
 *
 * @author  hualiangxie <hualiangxie@tencent.com> 
 * @version 2009-11-06
 */

class Page
{
    /**
     * 分页显示
     * 
     * @param int $allItemTotal 所有记录数量
     * @param int $currPageNum 当前页数量
     * @param int $pageSize  每页需要显示记录的数量
     * @param string $pageName  当前页面的地址, 如果为空则由系统自动获取,缺省为空
     * @param array $getParamList  页面中需要传递的URL参数数组, 数组中key代表变量民,value代表变量值
     * @return string  返回最后解析出分页HTML代码, 可以直接使用
     * @example 
     *   echo pageStyle1(50, 2, 10, 's.php', array('id'=>1, 'name'=>'user'));
     *
     *   输出：第2/50页 上一页 1 2 3 4 5 下一页  跳到 [  ] 页 [GO]
     */
     public static function split($allItemTotal, $currPageNum, $pageSize, $pageName='',  $getParamList = array()){
        if ($allItemTotal == 0) return "";
    
        //页面名称
        if ($pageName==''){
            $url = $_SERVER['PHP_SELF']."?page=";
        } else {
            $url = $pageName."?page=";
        }
        
        //参数
        $urlParamStr = "";
        foreach ($getParamList as $key => $val) {
            $urlParamStr .= "&amp;". $key ."=". $val;
        }
        //计算总页数
        $pagesNum = ceil($allItemTotal/$pageSize);
        
        //上一页显示
        $prePage  = ($currPageNum <= 1) ? "上一页" : "<a href='". $url . ($currPageNum-1) . $urlParamStr ."'  title='上一页' class='page_pre'>上一页</a>";
        
        //下一页显示
        $nextPage = ($currPageNum >= $pagesNum) ? "下一页" : "<a href='". $url . ($currPageNum+1) . $urlParamStr ."'  title='下一页' class='page_next'>下一页</a>";
        
        //按页显示
        $listNums = "<select name='page_select' id='page_select'>\n";
        for ($i=1; $i<=$pagesNum; $i++) {
            if ($i < 1 || $i > $pagesNum) continue;
            if ($i == $currPageNum) $listNums .= "<option selected=true>{$i}</option>\n";
            else $listNums .= "<option>{$i}</option>\n";
        }
        $listNums .= "</select>\n";
        
        $returnUrl =  $prePage .' '. $nextPage . ' 共有'.$pagesNum.'页  跳到 '.$listNums ."&nbsp;页 ";
        $script =<<<EOF
        <script type="text/javascript">
        function _pageSelect(url){
            var o = document.getElementById("page_select");
            var v = o.options[o.selectedIndex].text;
            window.location.replace(url+v);
        }            
        </script>
            
EOF;
        $gotoForm = ' <a href="javascript:_pageSelect(\''.$url.'\');" onclick="//_pageSelect(\''.$url.'\')">GO</a>';
        
        return $script . $returnUrl . $gotoForm;
    }


    /**
     * 分页显示2
     * 
     * @param int $allItemTotal 所有记录数量
     * @param int $currPageNum 当前页数量
     * @param int $pageSize  每页需要显示记录的数量
     * @param string $pageName  当前页面的地址, 如果为空则由系统自动获取,缺省为空
     * @param array $getParamList  页面中需要传递的URL参数数组, 数组中key代表变量民,value代表变量值
     * @return string  返回最后解析出分页HTML代码, 可以直接使用
     * @example 
     *   echo pageStyle1(50, 2, 10, 's.php', array('id'=>1, 'name'=>'user'));
     *
     *   输出：第2/50页 上一页 1 2 3 4 5 下一页  跳到 [  ] 页 [GO]
     */
     public static function split2($allItemTotal, $currPageNum, $pageSize, $pageName='',  $getParamList = array()){
        if ($allItemTotal == 0) return "";
    
        //页面名称
        if ($pageName==''){
            $url = $_SERVER['PHP_SELF']."?page=";
        } else {
            $url = $pageName."?page=";
        }
        
        //参数
        $urlParamStr = "";
        foreach ($getParamList as $key => $val) {
            $urlParamStr .= "&amp;". $key ."=". $val;
        }
        //计算总页数
        $pagesNum = ceil($allItemTotal/$pageSize);
        
        //上一页显示
        $prePage  = ($currPageNum <= 1) ? "上一页" : "<a href='". $url . ($currPageNum-1) . $urlParamStr ."'  title='上一页' class='page_pre'>上一页</a>";
        
        //下一页显示
        $nextPage = ($currPageNum >= $pagesNum) ? "下一页" : "<a href='". $url . ($currPageNum+1) . $urlParamStr ."'  title='下一页' class='page_next'>下一页</a>";
        
        //按页显示
        $listNums = "";
        for ($i=($currPageNum-1); $i<($currPageNum+4); $i++) {
            if ($i < 1 || $i > $pagesNum) continue;
            if ($i == $currPageNum) $listNums.= "&nbsp;<span class='page_cur'>".$i."</span>";
            else $listNums.= "&nbsp;<a href='". $url . $i . $urlParamStr ."' title='第". $i ."页' class='page_other'>". $i ."</a>";
        }
        
        $returnUrl = '<span class="page_text">第'.$currPageNum.'/'.$pagesNum.'页</span> '. $prePage ." ". $listNums ."&nbsp;". $nextPage;
        $gotoForm = ' <span class="page_jump">跳到 <input type="text" class="page_enter" style="width:20px;" id="page_input" value="'. $currPageNum .'" /> 页 <input type="button" value="Go" class="page_submit" onclick="location.href=\''. $url .'\'+document.getElementById(\'page_input\').value+\''. $urlParamStr .'\'" />';
        
        return $returnUrl . $gotoForm;
    }


    /**
     * 分页显示3
     * 
     * @param int $allItemTotal 所有记录数量
     * @param int $currPageNum 当前页数量
     * @param int $pageSize  每页需要显示记录的数量
     * @param string $pageName  当前页面的地址, 如果为空则由系统自动获取,缺省为空
     * @param array $getParamList  页面中需要传递的URL参数数组, 数组中key代表变量民,value代表变量值
     * @return string  返回最后解析出分页HTML代码, 可以直接使用
     * @example 
     *   echo pageStyle1(50, 2, 10, 's.php', array('id'=>1, 'name'=>'user'));
     *
     *   输出：第2/50页 上一页 1 2 3 4 5 下一页  跳到 [  ] 页 [GO]
     */
     public static function split3($allItemTotal, $currPageNum, $pageSize, $pageName='',  $getParamList = array()){
        if ($allItemTotal == 0) return "";
    
        //页面名称
        if ($pageName==''){
            $url = $_SERVER['PHP_SELF']."?page=";
        } else {
            $url = $pageName."?page=";
        }
        
        //参数
        $urlParamStr = "";
        foreach ($getParamList as $key => $val) {
            $urlParamStr .= "&amp;". $key ."=". $val;
        }
        //计算总页数
        $pagesNum = ceil($allItemTotal/$pageSize);
        
        //上一页显示
        $prePage  = ($currPageNum <= 1) ? "<" : "<a href='". $url . ($currPageNum-1) . $urlParamStr ."'  title='上一页' class='page_pre'><</a>";
        
        //下一页显示
        $nextPage = ($currPageNum >= $pagesNum) ? ">" : "<a href='". $url . ($currPageNum+1) . $urlParamStr ."'  title='下一页' class='page_next'>></a>";
        
        //按页显示
        $listNums = "";
        for ($i=($currPageNum-2); $i<($currPageNum+6); $i++) {
            if ($i < 1 || $i > $pagesNum) continue;
            if ($i == $currPageNum) $listNums.= "&nbsp;<span class='page_cur'>".$i."</span>";
            else $listNums.= "&nbsp;<a href='". $url . $i . $urlParamStr ."' title='第". $i ."页' class='page_other'>". $i ."</a>";
        }
        
        $returnUrl = '<span class="page_text">'. $prePage ." ". $listNums ."&nbsp;". $nextPage;
        //$gotoForm = ' <span class="page_jump">跳到 <input type="text" class="page_enter" style="width:20px;" id="page_input" value="'. $currPageNum .'" /> 页 <input type="button" value="Go" class="page_submit" onclick="location.href=\''. $url .'\'+document.getElementById(\'page_input\').value+\''. $urlParamStr .'\'" />';
        
        return $returnUrl . $gotoForm;
    }



    /**
     * 分页显示4
     * 
     * @param int $allItemTotal 所有记录数量
     * @param int $currPageNum 当前页数量
     * @param int $pageSize  每页需要显示记录的数量
     * @param string $pageName  当前页面的地址, 如果为空则由系统自动获取,缺省为空
     * @param array $getParamList  页面中需要传递的URL参数数组, 数组中key代表变量民,value代表变量值
     * @return string  返回最后解析出分页HTML代码, 可以直接使用
     * @example 
     *   echo pageStyle1(50, 2, 10, 's.php', array('id'=>1, 'name'=>'user'));
     *
     *   输出：第2/50页 上一页 1 2 3 4 5 下一页  跳到 [  ] 页 [GO]
     */
     public static function split4($allItemTotal, $currPageNum, $pageSize, $pageName='',  $getParamList = array()){
        if ($allItemTotal == 0) return "";
    
        //页面名称
        if ($pageName==''){
            $url = $_SERVER['PHP_SELF']."?page=";
        } else {
            $url = $pageName."?page=";
        }
        
        //参数
        $urlParamStr = "";
        foreach ($getParamList as $key => $val) {
            $urlParamStr .= "&amp;". $key ."=". $val;
        }
        //计算总页数
        $pagesNum = ceil($allItemTotal/$pageSize);
        
        //上一页显示
        $prePage  = ($currPageNum <= 1) ? " <img src='/images/mirro_arr_left.jpg' width='19' height='19' border='0'>" : "<a href='". $url . ($currPageNum-1) . $urlParamStr ."'  title='上一页' class='page_pre'> <img src='/images/mirro_arr_left.jpg' width='19' height='19' border='0'></a>";
        
        //下一页显示
        $nextPage = ($currPageNum >= $pagesNum) ? " <img src='/images/mirro_arr_right.jpg' width='19' height='19' border='0'>" : "<a href='". $url . ($currPageNum+1) . $urlParamStr ."'  title='下一页' class='page_next'> <img src='/images/mirro_arr_right.jpg' width='19' height='19' border='0'></a>";
        
        //按页显示
        $listNums = "";
        for ($i=($currPageNum-3); $i<($currPageNum+6); $i++) {
            if ($i < 1 || $i > $pagesNum) continue;
            if ($i == $currPageNum) $listNums.= "&nbsp;<span class='page_cur'>".$i."</span>";
            else $listNums.= "&nbsp;<a href='". $url . $i . $urlParamStr ."' title='第". $i ."页' class='page_other'>". $i ."</a>";
        }
        
        $returnUrl = '<span class="page_text">'. $prePage ." ". $listNums ."&nbsp;". $nextPage;
        //$gotoForm = ' <span class="page_jump">跳到 <input type="text" class="page_enter" style="width:20px;" id="page_input" value="'. $currPageNum .'" /> 页 <input type="button" value="Go" class="page_submit" onclick="location.href=\''. $url .'\'+document.getElementById(\'page_input\').value+\''. $urlParamStr .'\'" />';
        
        return $returnUrl . $gotoForm;
    }

    /**
     * 分页显示5
     * 
     * @param int $allItemTotal 所有记录数量
     * @param int $currPageNum 当前页数量
     * @param int $pageSize  每页需要显示记录的数量
     * @param string $pageName  当前页面的地址, 如果为空则由系统自动获取,缺省为空
     * @param array $getParamList  页面中需要传递的URL参数数组, 数组中key代表变量民,value代表变量值
     * @return string  返回最后解析出分页HTML代码, 可以直接使用
     * @example 
     *   echo pageStyle1(50, 2, 10, 's.php', array('id'=>1, 'name'=>'user'));
     *
     *   输出：第2/50页 上一页 1 2 3 4 5 下一页  跳到 [  ] 页 [GO]
     */
       public static function split5($allItemTotal, $currPageNum, $pageSize, $pageName='',  $getParamList = array()){
        if ($allItemTotal == 0) return "";
    
        //页面名称
        if ($pageName==''){
            $url = $_SERVER['PHP_SELF']."page=";
        } else {
            $url = $pageName."page=";
        }
        
        //参数
        $urlParamStr = "";
        foreach ($getParamList as $key => $val) {
            $urlParamStr .= "&amp;". $key ."=". $val;
        }
        //计算总页数
        $pagesNum = ceil($allItemTotal/$pageSize);
        
        //上一页显示
        $prePage  = ($currPageNum <= 1) ? "上一页" : "<a href='". $url . ($currPageNum-1) . $urlParamStr ."'  title='上一页' class='page_pre'>上一页</a>";
        
        //下一页显示
        $nextPage = ($currPageNum >= $pagesNum) ? "下一页" : "<a href='". $url . ($currPageNum+1) . $urlParamStr ."'  title='下一页' class='page_next'>下一页</a>";
        
        /*按页显示
        $listNums = "<select name='page_select' id='page_select'>\n";
        for ($i=1; $i<=$pagesNum; $i++) {
            if ($i < 1 || $i > $pagesNum) continue;
            if ($i == $currPageNum) $listNums .= "<option selected=true>{$i}</option>\n";
            else $listNums .= "<option>{$i}</option>\n";
        }
        $listNums .= "</select>\n";*/
        $listNums .= "<input type=text size=5 name='page_select' />";
        
        $returnUrl =  $prePage .' '. $nextPage . ' 共有'.$pagesNum.'页  跳到 '.$listNums ."&nbsp;页 ";
        $script =<<<EOF
        <script type="text/javascript">
        function _pageSelect(url){
            var o = document.getElementById("page_select");
            var v = o.value;
            window.location.replace(url+v);
        }            
        </script>
            
EOF;
        $gotoForm = ' <a href="javascript:_pageSelect(\''.$url.'\');" onclick="//_pageSelect(\''.$url.'\')">GO</a>';
        
        return $script . $returnUrl . $gotoForm;
    }

}





