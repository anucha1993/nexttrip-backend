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


        <!-- BEGIN: Content -->
        <div class="content">
            <!-- BEGIN: Top Bar -->
            @include("backend.layout.topbar")
            <!-- END: Top Bar -->

            <!-- BEGIN: Content -->
            <h2 class="intro-y text-lg font-medium mt-10">ฟอร์มข้อมูล</h2>

            <form id="menuForm" method="post" action="" enctype="multipart/form-data" >
                @csrf
                <div class="grid grid-cols-12 gap-6 mt-5">
                    <div class="col-span-12 lg:col-span-6 2xl:col-span-8">
                        <div class="intro-y col-span-12 lg:col-span-6">
                            <!-- BEGIN: Form Layout -->
                            <div class="intro-y box p-5 place-content-center">
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">ประเภท</label>
                                        <input type="text" id="type" name="type" class="form-control" value="{{$row->type}}">
                                    </div>
                                </div>
                                <div class="text-center mt-5">
                                    <button type="submit" class="btn btn-primary w-24">บันทึกข้อมูล</button>
                                    <a class="btn btn-outline-danger w-24 mr-1" href="{{ url("$segment/$folder") }}" >ยกเลิก</a>
                                </div>
                            </div>
                            <!-- END: Form Layout -->
                        </div>
                    </div>
                </div>
            </form>
        </div>
        

        <!-- BEGIN: JS Assets-->
        @include("backend.layout.script")
       
        <script>
            // sub type
            var main = document.getElementById('main_type_id').value;
            var sub = type_sub.filter(x => x.main_id == main);
            var options = "<option value="+sub_data.id+" selected hidden>"+sub_data.sub_type_th+"</option>";
            for(x =0 ; x < sub.length ; x++) {
                    options = options+"<option value = '"+sub[x].id+"'>"+sub[x].sub_type_th+"</option>" ;
                }
            document.getElementById("sub_type_id").innerHTML = options;
            // type
           
            var sub_value = document.getElementById('main_type_id').value;
            var find_id = type_data.filter(x => x.sub_id == sub_value);
            var options = "<option value="+sub_type_id.id+" selected hidden>"+sub_type_id.type_th+"</option>";
            for(x =0 ; x < find_id.length ; x++) {
                    options = options+"<option value = '"+find_id[x].id+"'>"+find_id[x].type_th+"</option>" ;
                }
            document.getElementById("type_id").innerHTML = options;

            function select_sub(mid){
                    var data = type_sub.filter(x => x.main_id == mid);
                    var option = '<option value="" selected hidden>กรุณาเลือกข้อมูล</option>';
                        for(x =0 ; x < data.length ; x++) {
                            option = option+"<option value = '"+data[x].id+"'>"+data[x].sub_type_th+"</option>" ;
                        }
                    document.getElementById("sub_type_id").innerHTML = option;
                    if(data.length == 0){
                        document.getElementById("type_id").innerHTML = '<option value="" selected hidden>กรุณาเลือกข้อมูล</option>';
                    }
            }
            function select_type(sid){
             var data = type_data.filter(x => x.sub_id == sid)
             var option = '<option value="" selected hidden>กรุณาเลือกข้อมูล</option>';
                for(x =0 ; x < data.length ; x++) {
                    option = option+"<option value = '"+data[x].id+"'>"+data[x].type_th+"</option>" ;
                }
                document.getElementById("type_id").innerHTML = option;

            }
            CKEDITOR.config.allowedContent = true;
            CKEDITOR.replace('description_en', {
                filebrowserUploadMethod: 'form',
            });
            CKEDITOR.replace('description_th', {
                filebrowserUploadMethod: 'form',
            });
            CKEDITOR.replace('detail_th', {
                filebrowserUploadMethod: 'form',
            });
            CKEDITOR.replace('detail_en', {
                filebrowserUploadMethod: 'form',
            });
            $("#image").on('change', function () {
                var $this = $(this)
                const input = $this[0];
                const fileName = $this.val().split("\\").pop();
                $this.siblings(".custom-file-label").addClass("selected").html(fileName)
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $('#preview').attr('src', e.target.result).fadeIn('fast');
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            });

        </script>
        <!-- END: JS Assets-->
</body>

</html>
