<?php
namespace {%namespace%};

use App\Http\Controllers\Controller;
use {%modelspace%};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class {%className%}Controller extends Controller
{

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '{%className%}';

    protected $fields = {%fields%};

    protected $middle_table = [
        // [
        //     'model'       => 'App\Models\Article',//模型地址
        //     'controller'  => 'App\Admin\Controllers\Article',//控制器地址
        //     'foreign_key' => 'article_id'//外键
        // ]
    ];
    /**
     * 
     * 单条记录---GET
     * */
    public function one_(Request $request){
        $id = intval($request->get('id'));
        if(empty($id)){
            return Response::json(['code' => 500, 'msg' => '参数不存在!']);
        }

        $res = {%modelName%}::where('id',$id)->first();

        $data = [
            'code' => 200,
            'msg'   => 'ok...',          
            'data'  => $res,
        ];
        return Response::json($data);

    }

    /***
     * 列表--lists--GET
     */
    public function lists(Request $request){

        $status = intval($request->get('status'));
        $where = [['id','>',0]];

        /*查询条件*/
        {%searchList%}

        $res = {%modelName%}::where($where)->orderBy('id','desc')->paginate($request->get('limit',15));
        $data = [
            'code' => 200,
            'msg'   => 'success',
            'count' => $res->total(),
            'data'  => $res->items(),
        ];
        return Response::json($data);
    }
    /***
     * POST
     * 添加--add_
     */
    public function add_(Request $request){

        try {

            $model = new {%modelName%}();

            {%Filedverify%}
            
            foreach ($request->post() as $key => $val) {
                
                if(in_array($key,$this->fields)){
                    $model->$key   = $val;
                }
            }

            $ret = $model->save();
            if($ret){
                return Response::json(['code' => 200, 'msg' => '添加成功']);
            }else{
                return Response::json(['code' => 500, 'msg' => '添加失败']);
            }
            
        } catch (\Exception $exception) {
            return Response::json(['code' => 500, 'msg' => '添加失败:'.$exception->getMessage()]);
        }



    }
    /***
     * POST
     * 修改--edit
     */
    public function edit_(Request $request){

        $id = $request->post('id');

        if(empty(intval($id))){
            return Response::json(['code' => 500, 'msg' => '参数不存在!']);
        }

        try {
            $model = {%modelName%}::findOrFail($id);
            if(empty($model)){
                return Response::json(['code' => 500, 'msg' => '数据不存在!']);
            }

            foreach ($request->post() as $key => $val) {
                if($key == 'id'){continue;}
                $model->$key   = $val;
            }
            $ret = $model->save();

            if($ret){
                return Response::json(['code' => 200, 'msg' => '更新成功']);
            }else{
                return Response::json(['code' => 500, 'msg' => '更新失败']);
            }
        } catch (\Exception $exception) {
            return Response::json(['code' => 500, 'msg' => '更新失败:'.$exception->getMessage()]);
        }

    }
    /***
     * POST
     * 删除--delete_
     */
    public function delete_(Request $request,$newids=[]){

        $ids = $request->all('ids');
        if(!empty($newids)){
            $ids = $newids;
        }
        if(!is_array($ids)){
            $ids = [intval($ids)];
        }
        if(empty($ids)){
            return Response::json(['code' => 500, 'msg' => '参数不存在!']);
        }
        
        $ret = {%modelName%}::whereIn('id',$ids)->delete();
        /***是否存在关联表 */

        if(!empty($this->middle_table)){
            
            foreach ($this->middle_table as $val) {
                $model = new $val['model'];
                $controller = new $val['controller'];
                $middle_ids = $model->whereIn($val['foreign_key'],$ids)->get('id');
                if(!empty($middle_ids)){
                    $middle_ids = $middle_ids->toArray();
                    $newids = array_column($middle_ids,'id');
                    $controller->delete_($request,$newids);
                }    
                unset($model);
                unset($controller);
            }
        }
        
        if($ret){
            return Response::json(['code' => 200, 'msg' => '删除成功']);
        }else{
            return Response::json(['code' => 500, 'msg' => '删除失败']);
        }
    }


}