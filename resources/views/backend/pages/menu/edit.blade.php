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
   @include('backend.layout.mobile-menu')
   <!-- END: Mobile Menu -->
   <div class="flex">
       <!-- BEGIN: Side Menu -->
       @include('backend.layout.side-menu')
       <!-- END: Side Menu -->



       <div class="content">
           <!-- BEGIN: Top Bar -->
           @include('backend.layout.topbar')
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
                                    <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label">ตำแหน่ง</label>
                                        <select class="form-control w-full" name="position" id="position">
                                            <option value="" hidden>กรุณาเลือก</option>
                                            <option value="main" @if($row->position == "main") selected @endif>เมนูหลัก</option>
                                            <option value="secondary" @if($row->position == "secondary") selected @endif>เมนูย่อย</option>
                                        </select>
                                    </div>
                                    <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label">เป็นเมนูย่อยของเมนู :</label>
                                        <select class="form-control  w-full" name="_id" id="_id" @if($row->position == "main") disabled selected @endif>
                                            <option value="" hidden>กรุณาเลือก</option>
                                            @if(@$main)
                                            @foreach($main as $i => $c)
                                            <option value="{{$c->id}}" @if($c->id == $row->_id) selected @endif>{{$c->name}}</option>
                                            @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">Icon : <a href="https://feathericons.com" target="_blank">Views icons feather</a></label>
                                        <div class="input-group">
                                            <div id="input-group-3" class="input-group-text">Icon</div>
                                            <input type="text" class="form-control" id="icon" name="icon" value="{{@$row->icon}}" placeholder="กรุณากรอกชื่อ Icon ตามลิงค์ด้านบน" aria-describedby="input-group-3">
                                        </div>
                                    </div>
                                    
                                </div>

                                <div class="grid grid-cols-12 gap-6 mt-5 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">ชื่อเมนู</label>
                                        <input type="text" id="name" name="name" class="form-control w-full" value="{{@$row->name}}" placeholder="Input text">
                                    </div>
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">ลิงค์เมนู</label>
                                        <input type="text" id="url" name="url" class="form-control w-full" value="{{@$row->url}}" placeholder="Input text">
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
                <!-- END: Content -->


            </div>
            
        </div>
       
        <!-- BEGIN: JS Assets-->
        @include("backend.layout.script")

        <script>
        function check_add() {
            var position = $('#position').val();
            var name = $('#name').val();
            var url = $('#url').val();
            if (position == "") {
                toastr.error('กรุณาเลือกตำแหน่งเมนู');
                return false;
            }
            if (name == "") {
                toastr.error('กรุณากรอกชื่อเมนู');
                return false;
            }
            if (url == "") {
                toastr.error('กรุณากรอกลิงค์เข้าเมนู');
                return false;
            }
        }
        $('#position').on('change', function() {
            var position = $('#position').val();
            if(position == "secondary")
            {
                $('#_id').removeAttr('disabled', false);
                // $('#_id').prop('selectedIndex', 0).prop('disabled', false);
            }
            else
            {
                $('#_id').attr('disabled', true);
                $('#_id').val(null);
            }


            // if ($('option:selected', this).val() == 'secondary') {
            //     $('#_id').prop('selectedIndex', 0).prop('disabled', false)
            // } else {
            //     $('#_id').prop('disabled', true)
            // }
        })
        </script>
        <!-- END: JS Assets-->
    </body>
</html>