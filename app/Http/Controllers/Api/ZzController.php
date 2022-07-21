<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ZzController extends Controller
{

    /***
     * 资源生成
     */

    public function index(){
        $row = Db::select("select * from information_schema.tables where table_schema='".config('database.connections.mysql.database')."' ");
        return view('zz.index',compact('row'));
    }    
/**
 * 获取表字段
 */
    public function getcloumn(Request $request){
        $table = $request->get('table','');
        $row = Db::select("show full columns from ".$table);
        
        
        return json_encode(['code'=>200,'msg'=>'success','data'=>$row],302);
    }


    public function create(Request $request){
        $data = $request->post();
/**
 * 测试数据
 * App\Admin\Controllers
 * App\Models\WebConfig
 * app\Admin\routes.php
 */
        // dd($data);
        $table = $data['table'];
        $namespace = $data['namespace'];
        $modelspace = $data['modelspace'];
        $router_file = str_ireplace('\\\\','\\',base_path().'\\'.$data['routespace']);
        
        $tpl = file_get_contents(base_path().'/'.'controller.tpl');

        $field_list = [];
        $auto_database_auth = true;

        $ignore_list = ['id','created_at','updated_at','deleted_at'];
        $verify_list = [];
        $search_list = [];
        foreach ($data['field'] as $k => $val) {
            $field_list[$k] = $val['comment'];
            /**查询条件 */
            if(isset($val['condition']) && $val['condition'] != 'null'){
                $search_list[$k] = $val['condition'];                
            }

            if(in_array($k,$ignore_list)){
                continue;
            }
            if(isset($val['isrequired']) && $val['isrequired'] == 'on'){
                $verify_list[$k] = $val['comment'];                
            }

        }
        $field_list_arraystr = $this->array_to_strarray(array_keys($field_list));

       

        $tpl = str_replace('{%fields%}', $field_list_arraystr, $tpl);
        $Filedverify = "";
        if(!empty($verify_list)){
            
            foreach ($verify_list as $vk => $vv) {
                $template = 'if(empty($request->'.$vk.')){ return Response::json(["code"=>500,"msg"=>"'.$vv.'不能为空!"]);}'."\n\r";                
                $Filedverify .= $template;
            }

        }
        /***替换命名空间 */        
        $tpl = str_replace('{%namespace%}', $namespace, $tpl);
        /***替换模型空间 --和模型名称*/
        $modelspace = str_ireplace('/','\\',$modelspace);
        $modelName = substr($modelspace,strripos($modelspace,'\\')+1);
        $tpl = str_replace(['{%modelspace%}','{%modelName%}'], [$modelspace,$modelName], $tpl);

        /***替换验证字段 */
        $tpl = str_replace('{%Filedverify%}', $Filedverify, $tpl);
        /**替换控制器名称 */
        $controller_name = $this->getcontrollerNmae($table);
        $tpl = str_replace('{%className%}', $controller_name, $tpl);
        /**替换查询条件 */
        $search_result = $this->search_list_code($search_list);
        $tpl = str_replace('{%searchList%}', $search_result, $tpl);
        
        $file_path = app_path().'/Admin/Controllers/'.$controller_name.'Controller.php';
        if(is_file($file_path)){
            echo json_encode(['code'=>400,'msg'=>'文件已存在！请先删除文件','data'=>$file_path]);
            die;
        }
        /**
         * 路由替换
         */
        /*{%routes%}*/
        if(is_file($router_file)){
            $routes_data = file_get_contents($router_file);
            $routes_str = '';
            $routes_methods = ['add_','delete_','edit_','lists','one_'];
            $routes_str = $this->getroutes($routes_methods,$controller_name,$namespace);

            $routes_data = str_replace("/*{%routes%}*/", "/*{%routes%}*/\r\n".$routes_str, $routes_data);
            file_put_contents($router_file,$routes_data);
        }
        
        // dump($routes_data);
        
        file_put_contents($file_path,$tpl);

        
       
        echo json_encode(['code'=>200,'msg'=>'成功生成文件','data'=>$file_path]);
        die;

    }   



    /***
     * 获取搜索字段代码
     */
    public function search_list_code($field_list){
        $result = "";
        foreach ($field_list as $key => $va) {
            $code = '';
            switch ($va) {
                case 'equal':
                    $code = '
                    if(isset($request->'.$key.') && $request->'.$key.' !== ""){
                        $where[] = ["'.$key.'","=",$request->'.$key.'];
                    }'."\r\n";
                    
                    break;
                case 'like':
                    $code = '
                    if(isset($request->'.$key.') && $request->'.$key.' !== ""){
                        $where[] = ["'.$key.'","like","%".$request->'.$key.'."%"];
                    }'."\r\n";
                    break;                
                case 'between':
                    $code = '
                    if(isset($request->'.$key.') && $request->'.$key.' !== ""){
                        $where[] = [function($query)use($request){ $query->whereBetween("'.$key.'",explode(",",$request->'.$key.'));}];
                    }'."\r\n";    

                    break;
                default:
                    # code...
                    break;
            }
            $result.=$code;
        }
        return $result;

    }

    /**
     * 把数组转为字符串格式数组
     */
    public function array_to_strarray($arr){
        if(!is_array($arr) || empty($arr)){
            return "";
        }
        
        $arr = var_export($arr,true);

        return $arr;

    }
    /**
     * 把表名转为驼峰
     */
    public function getcontrollerNmae($table=""){
        $table = explode('_',$table);
        $table = array_map(function($v){
            return ucfirst($v);
        },$table);
        return implode('',$table);
    }

    public function capitalc($name){
        $temp_array = array();
        for($i=0;$i<strlen($name);$i++){
            $ascii_code = ord($name[$i]);
            if($ascii_code >= 65 && $ascii_code <= 90){
            if($i == 0){
            $temp_array[] = chr($ascii_code + 32);
            }else{
            $temp_array[] = '_'.chr($ascii_code + 32);
            }
            }else{
            $temp_array[] = $name[$i];
            }
        }
        return implode('',$temp_array);
    }



    /**
     * 获取路由
     */
    public function getroutes($routes_methods,$controller_name,$namespace,$middleware=""){
        $ordname = $this->capitalc($controller_name);
        $midstr = "";
        
        $methods_str = '';
        
        
        if(!empty($middleware) && is_array($middleware)){
            $midstr = ',middleware=>[';
            foreach ($middleware as $key => $v) {
                $midstr .= $v.',';
            }
            $midstr = rtrim($midstr,',').']';
        }

        $router_str = "Route::group(['namespace' => '{$namespace}' {$midstr} ], function (Router \$router) {";

        foreach ($routes_methods as $val) {
            $methods_str .= "\r\n//方法'.$val.'\r\n".'$router->'.(($val=='lists'|| $val=='one_')?'get':'post').'("'.$ordname.'/'.$val.'", "'.$controller_name.'Controller@'.$val.'")->name("'.$ordname.'.'.$val.'");';
        }
        $router_str .= $methods_str;
        $router_str .= "\r\n});";
        return $router_str;
        
    }

}
