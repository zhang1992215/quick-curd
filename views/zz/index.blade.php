<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <title>开始使用</title>
<!-- CSS only -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
</head>
<body>
<div id="app">

<div>

<div class="row g-3 align-items-center">
  <div class="col-auto">
    <label for="namespace" class="col-form-label">命名空间</label>
  </div>
  <div class="col-auto">
    <input type="text" id="namespace" v-model="namespace" class="form-control" aria-describedby="passwordHelpInline">
  </div>
  <div class="col-auto">
    <span id="passwordHelpInline" class="form-text">
      生成文件存放点.
    </span>
  </div>
</div>
<br/>
<div class="row g-3 align-items-center">
  <div class="col-auto">
    <label for="modelspace" class="col-form-label">模型路径</label>
  </div>
  <div class="col-auto">
    <input type="text" id="modelspace" v-model="modelspace" class="form-control" aria-describedby="passwordHelpInline2">
  </div>
  <div class="col-auto">
    <span id="passwordHelpInline2" class="form-text">
      模型存放点.
    </span>
  </div>
</div>
<br/>
<div class="row g-3 align-items-center">
  <div class="col-auto">
    <label for="routespace" class="col-form-label">路由路径</label>
  </div>
  <div class="col-auto">
    <input type="text" id="routespace" v-model="routespace" class="form-control" aria-describedby="passwordHelpInline3">
  </div>
  <div class="col-auto">
    <span id="passwordHelpInline3" class="form-text">
      路由文件存放点.需要存在替换位置标记：/*{%routes%}*/
    </span>
  </div>
</div>
<br/>
</div>




<div>
<div>
<select class="form-select form-select-lg mb-3" aria-label=".form-select-lg" v-model="tableselect" v-on:change="selectchange($event.target.value)">
  <option selected value="">chouse your table</option>
@foreach($row as $val)
<option value="{{$val->TABLE_NAME}}">{{$val->TABLE_NAME}} @if($val->TABLE_COMMENT != "") ----{{$val->TABLE_COMMENT}}  @endif</option>      
@endforeach




</select>
<div id="emailHelp" class="form-text">在这里选择需要创建的表.</div>
</div>

</div>


<div >
  
<form id="searchForm">
<table class="table">
<input type="hidden" name="table"  :value="tableselect">
  <thead>
    <tr>

      <th scope="col">字段名</th>
      <th scope="col">是否必填</th>
      
      <th scope="col">是否排序</th>
      <!-- <th scope="col">是否忽略</th> -->
      <th scope="col">查询条件</th>
    </tr>
  </thead>
  <tbody >
    <tr v-for="(val,index) in columns">
    <td>@{{val.Field}}<div id="emailHelp" class="form-text">@{{val.Comment}}.</div></td>
      <td>
        <input type="hidden" :value="val.Comment" :name="`field[`+val.Field+`][comment]`"/>
        
        <div class="form-check form-switch">
          <input class="form-check-input" :name="`field[`+val.Field+`][isrequired]`" type="checkbox" checked role="switch">
        </div>
      </td>
      <td>
        <div class="form-check form-switch">
          <input class="form-check-input" :name="`field[`+val.Field+`][issort]`" type="checkbox"  role="switch">
        </div>
      </td>
      <!-- <td>
        <div class="form-check form-switch">
          <input class="form-check-input" :name="`field[`+val.Field+`][isignore]`" type="checkbox" checked role="switch">
        </div>

      </td> -->
      <td>
      <div class="form-check">
        <input class="form-check-input" type="radio" :name="`field[`+val.Field+`][condition]`" value="" checked  :id="val.Field+`null`">
        <label class="form-check-label" :for="val.Field+`null`">
          无查询
        </label>
        </div>
        <div class="form-check">
        <input class="form-check-input" type="radio" :name="`field[`+val.Field+`][condition]`" value="equal" :id="val.Field+`equal`">
        <label class="form-check-label" :for="val.Field+`equal`">
          相等
        </label>
        </div>

        <div class="form-check">
        <input class="form-check-input" type="radio" :name="`field[`+val.Field+`][condition]`" value="like" :id="val.Field+`like`">
        <label class="form-check-label" :for="val.Field+`like`">
          模糊查询
        </label>
        </div>

        <div class="form-check">
        <input class="form-check-input" type="radio" :name="`field[`+val.Field+`][condition]`" value="between" :id="val.Field+`between`">
        <label class="form-check-label" :for="val.Field+`between`">
          区间
        </label>
        </div>


      </td>
    </tr>
    <!-- <tr>
      
      <td>Jacob</td>
      <td>Thornton</td>
      <td>@fat</td>
    </tr>
    <tr>
      
      <td colspan="2">Larry the Bird</td>
<td>

<div class="form-floating mb-3">
  <input type="email" class="form-control" id="floatingInput" placeholder="name@example.com">
  <label for="floatingInput">Email address</label>
</div>

</td>
    </tr> -->
  </tbody>
</table>
<button type="button" v-on:click="submittable()" class="btn btn-primary">Submit</button>
</form>
</div>






</div>

<script src="https://unpkg.com/vue@2.6/dist/vue.min.js"></script>
<!-- <script src="https://unpkg.com/vant@2.12/lib/vant.min.js"></script> -->
<script src="https://cdn.bootcdn.net/ajax/libs/vue-resource/1.5.3/vue-resource.js"></script>
<script>
  // 在 #app 标签下渲染一个按钮组件
  // import VueResource from 'vue-resource'
  new Vue({
    el: '#app',
    template: ``,
    data:function(){
      return {
        tableselect:'',
        namespace:'',
        modelspace:'',
        routespace:'',
        columns:[],
      }
    },
    methods:{
      selectchange($e){
        console.log($e);
        if($e ==""){
          this.columns = [];
          return ;
        }
        /**发送ajax */
        this.$http.get('http://192.168.11.110:9091/api/zz/getcloumn?table='+$e).then(function(res){
            console.log(res.body);  
            if(res.body.code != 200){
              alert(res.body.msg);
              return;
            }  
            this.columns = res.body.data;
            
        },function(){
  
            alert('请求失败');
        });


      },submittable(){
        let searchForm=document.getElementById("searchForm");
        let formData = new FormData(searchForm);
        formData.append('namespace',this.namespace);
        formData.append('modelspace',this.modelspace);
        formData.append('routespace',this.routespace);


        this.$http.post('http://192.168.11.110:9091/api/zz/create2',formData).then(function(res){
              alert(res.body.msg);
              if(res.body.code != 200){
                
                return;
              }  
              
              
          },function(){

              alert('请求失败');
          });




      }
    }
  });


</script>
</body>
</html>


