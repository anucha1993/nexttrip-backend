<!DOCTYPE html>
<html lang="en" class="light">
    <!-- BEGIN: Head -->
    <head>
        <!-- BEGIN: CSS Assets-->
        @include("backend.layout.css")
        <!-- END: CSS Assets-->
    </head>
    <!-- END: Head -->
    <body class="py-5">
        <!-- BEGIN: Mobile Menu -->
        @include("backend.layout.mobile-menu")
        <!-- END: Mobile Menu -->
        <div class="flex">
            <!-- BEGIN: Side Menu -->
            @include("backend.layout.side-menu")
            <!-- END: Side Menu -->


            
            <div class="content">
                <!-- BEGIN: Top Bar -->
                @include("backend.layout.topbar")
                <!-- END: Top Bar -->


                <!-- BEGIN: Content -->
                <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
                    <h2 class="text-lg font-medium mr-auto">
                        ฟอร์มข้อมูล
                    </h2>
                </div>

                <form id="menuForm" method="post" action="" onsubmit="return check_add();">
                @csrf
                    <div class="grid grid-cols-12 gap-6 mt-5">
                        <div class="intro-y col-span-12 lg:col-span-6">
                            <!-- BEGIN: Form Layout -->
                            <div class="intro-y box p-5">
                                
                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">ชื่อบทบาท</label>
                                        <input type="text" id="name" name="name" value="{{@$row->name}}" class="form-control w-full" placeholder="ชื่อบทบาท">
                                    </div>
                                </div>

                            
                                <div class="text-right mt-5">
                                    <a class="btn btn-outline-secondary w-24 mr-1" href="{{url("$segment/$folder")}}">ยกเลิก</a>
                                    <button type="submit" class="btn btn-primary w-24">บันทึกข้อมูล</button>
                                </div>
                            </div>
                            <!-- END: Form Layout -->
                        </div>
                    </div>
                </form>


                <form id="menuForm" method="post" action="{{url("$segment/$folder/menu/$row->id")}}" onsubmit="return check_add_menu();">
                @csrf
                    <div class="grid grid-cols-12 gap-6 mt-5">
                        <div class="intro-y col-span-12 lg:col-span-6">
                            <!-- BEGIN: Form Layout -->
                            <div class="intro-y box p-5">
                                <div class="card-header"><b>จัดการสิทธิ์เมนู</b></div>
                                
                                @if(@$menus)
                                @foreach(@$menus as $m)
                                    @php
                                    $second = \App\Models\Backend\MenuModel::where('_id',$m->id)->where('status','on')->get();
                                    $role_main = \App\Models\Backend\Role_listModel::where(['role_id'=>$row->id, 'menu_id'=>$m->id])->first();
                                    @endphp
                                    <!-- BEGIN: FORM Menu -->
                                    <div class="ml-5 grid grid-cols-12 gap-6 mt-5 mb-3">
                                        <div class="col-span-12 lg:col-span-12">
                                            <b>{{$m->name}}</b>
                                            @if(count(@$second))
                                            @foreach(@$second as $i => $s)
                                                @php
                                                $role_sub = \App\Models\Backend\Role_listModel::where(['role_id'=>$row->id, 'menu_id'=>$s->id])->first();
                                                @endphp
                                                <!-- BEGIN Sub: FORM Menu -->
                                                <input name="menu_id[]" value="{{$s->id}}" hidden>

                                                <div class="ml-4 grid grid-cols-12 gap-6 mt-5 mb-3">
                                                    <div class="col-span-12 lg:col-span-12">
                                                        {{$s->name}}
                                                    </div>
                                                </div>
                                                
                                                <div class="ml-5 col-span-12 grid grid-cols-12 gap-3">
                                                    <div class="ml-5 col-span-12 sm:col-span-3 2xl:col-span-3 intro-y"><input type="checkbox" @if(@$role_sub->read == "on") checked @endif name="read_{{$s->id}}"> อ่าน</div>
                                                    <div class="ml-5 col-span-12 sm:col-span-3 2xl:col-span-3 intro-y"><input type="checkbox" @if(@$role_sub->add == "on") checked @endif name="add_{{$s->id}}"> เพิ่ม</div>
                                                    <div class="ml-5 col-span-12 sm:col-span-3 2xl:col-span-3 intro-y"><input type="checkbox" @if(@$role_sub->edit == "on") checked @endif name="edit_{{$s->id}}"> แก้ไข</div>
                                                    <div class="ml-5 col-span-12 sm:col-span-3 2xl:col-span-3 intro-y"><input type="checkbox" @if(@$role_sub->delete == "on") checked @endif name="delete_{{$s->id}}"> ลบ</div>
                                                </div>
                                                <!-- END Sub: FORM Menu -->
                                            @endforeach
                                            @elseif(count(@$second) == null)
                                                <!-- BEGIN Sub IF new: FORM Menu -->
                                                <input name="menu_id[]" value="{{$m->id}}" hidden>
                                                <div class="ml-5 col-span-12 grid grid-cols-12 gap-3">
                                                    <div class="ml-5 col-span-12 sm:col-span-3 2xl:col-span-3 intro-y"><input type="checkbox" @if(@$role_main->read == "on") checked @endif name="read_{{$m->id}}"> อ่าน</div>
                                                    <div class="ml-5 col-span-12 sm:col-span-3 2xl:col-span-3 intro-y"><input type="checkbox" @if(@$role_main->add == "on") checked @endif name="add_{{$m->id}}"> เพิ่ม</div>
                                                    <div class="ml-5 col-span-12 sm:col-span-3 2xl:col-span-3 intro-y"><input type="checkbox" @if(@$role_main->edit == "on") checked @endif name="edit_{{$m->id}}"> แก้ไข</div>
                                                    <div class="ml-5 col-span-12 sm:col-span-3 2xl:col-span-3 intro-y"><input type="checkbox" @if(@$role_main->delete == "on") checked @endif name="delete_{{$m->id}}"> ลบ</div>
                                                </div>
                                                <!-- END Sub: FORM Menu -->
                                            @endif
                                        </div>
                                    </div>
    

                                @endforeach
                                @endif


                                

                                <div class="text-right mt-5">
                                    <a class="btn btn-outline-secondary w-24 mr-1" href="{{url("$segment/$folder")}}">ยกเลิก</a>
                                    <button type="submit" class="btn btn-primary w-24">บันทึกข้อมูล</button>
                                </div>
                            </div>
                            <!-- END: Form Layout -->
                        </div>
                    </div>
                </form>
                <!-- END: Content -->


            </div>
            
        </div>
       
        <!-- BEGIN: JS Assets-->
        @include("backend.layout.script")

        <script>
        function check_add() {
            var name = $('#name').val();
            if (name == "") {
                toastr.error('กรุณากรอกชื่อ');
                return false;
            }
            
        }
      
        </script>
        <!-- END: JS Assets-->
    </body>
</html>