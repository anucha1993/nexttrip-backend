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

            <form id="menuForm" method="post" action="" enctype="multipart/form-data" onsubmit="return check_add();">
                @csrf
                <div class="grid grid-cols-12 gap-6 mt-5">
                    <div class="col-span-12 lg:col-span-6 2xl:col-span-12">
                        <div class="intro-y col-span-12 lg:col-span-6">
                            <!-- BEGIN: Form Layout -->
                            <div class="intro-y box p-5 place-content-center">
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-4">
                                        <h6 class="mb-3">โลโก้ </h6>
                                        <img src="@if(@$row->logo == null) {{url("noimage.jpg")}} @else {{asset($row->logo)}} @endif"
                                            class="img-thumbnail" id="preview-logo">
                                    </div>
                                    <div class="col-span-12 lg:col-span-12">
                                        <small class="help-block">* รองรับไฟล์ <strong class="text-danger">(jpg, jpeg,
                                                png,svg)</strong> เท่านั้น</small>
                                        <div class="col-span-6 lg:col-span-6">
                                            <input type="file"
                                                class="custom-file-input block w-80 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                name="logo" id="logo_img">
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label"><span class="text-danger">*</span>ประเทศ</label>
                                        <select  id="country_id" class="tom-select w-full" multiple name="country_id[]" >
                                            <option value="" selected hidden>กรุณาเลือกข้อมูล</option>
                                            @foreach ($country as $c)
                                                <option value="{{$c->id}}">{{$c->country_name_th}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label"><span class="text-danger">*</span>ประเภท</label>
                                        <select data-placeholder="Select Suggest" class="form-control"  name="type_id" id="type_id">
                                            <option value="" selected hidden>กรุณาเลือกข้อมูล</option>
                                            @foreach($type as $t)
                                            <option value="{{$t->id}}">{{$t->type}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-8">
                                        <label for="crud-form-1" class="form-label">ชื่อลูกค้า/บริษัท</label>
                                        <input type="text" id="company" name="company" class="form-control">
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <h6 class="mb-3"><span class="text-danger">*</span>รูปภาพปก <span class="text-danger"> ขนาด 1116*602</span></h6>
                                        <img src="@if(@$row->img_cover == null) {{url("noimage.jpg")}} @else {{asset($row->img_cover)}} @endif"
                                            class="img-thumbnail" id="preview">
                                    </div>
                                    <div class="col-span-12 lg:col-span-12">
                                        <small class="help-block">* รองรับไฟล์ <strong class="text-danger">(jpg, jpeg,
                                                png)</strong> เท่านั้น</small>
                                        <div class="col-span-6 lg:col-span-6">
                                            <input type="file"
                                                class="custom-file-input block w-80 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                name="image" id="image">
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">รายละเอียด</label>
                                        <textarea id="detail" name="detail" class="tiny"></textarea>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">แฮชแท็ก</label>
                                        {{-- <select data-placeholder="กรุณาเลือกข้อมูล" class="tom-select w-full" multiple name="tag[]" >
                                            @foreach($tag as $t)
                                            <option value="{{$t->id}}">{{$t->tag}}</option>
                                            @endforeach
                                        </select> --}}
                                        <input type="text" list="browsers"  id="browser" onchange="TagValue()" class="form-control">
                                        <datalist id="browsers" >
                                            @foreach($tag as $t)
                                            <option >{{$t->tag}}</option>
                                            @endforeach
                                        </datalist>
                                        <br><br>
                                        <div id="hashtag"></div>
                                    </div>
                                </div>
                                <br>
                                <hr>
                                <div class="text-left mt-5">
                                    <div class="col-span-6 lg:col-span-6">
                                    <h3 class="mb-3"><b>อัลบั้มรูปภาพ</b></h3>
                                    <div id="preview-gallery" class="grid grid-cols-12 gap-6 mt-5"></div><br>
                                        <input type="file"
                                            class="custom-file-input w-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                            name="img[]" id="image-gallery" multiple onchange="readGallery()">
                                            <button type="button" class="btn btn-danger" onclick="removeImg()">Reset</button>
                                        <br>
                                        <small class="help-block">* รองรับไฟล์ <strong class="text-danger">(jpg, jpeg, png)</strong> เท่านั้น</small>
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
            <?php echo "var data = $data_tag;"; ?>
            var arr = new Array(); 
        </script>
        @include("backend.layout.tag") 
        <script>
            function readGallery() 
                {
                    const target = $('#preview-gallery');
                    target.innerHTML = null;
                    var total_file=document.getElementById("image-gallery").files.length;
                    // target.find('.new-pre').remove();
                    for(var i=0;i<total_file;i++)
                    {
                        target.append("<div class='col-span-2 lg:col-span-2 preview-item'><div class='img-thumbnail'><div class='img-preview'><img class='img-fluid' src='"+URL.createObjectURL(event.target.files[i])+"'/></div><div class='caption' style='margin-top:5px;'><i class='fas fa-upload'></i></div></div></div>");
                    }
                }
            function removeImg(){
                document.getElementById('preview-gallery').innerHTML = null;
                document.getElementById('image-gallery').value = null;
            }
            
            $("#logo_img").on('change', function () {
                var $this = $(this)
                const input = $this[0];
                const fileName = $this.val().split("\\").pop();
                $this.siblings(".custom-file-label").addClass("selected").html(fileName)
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $('#preview-logo').attr('src', e.target.result).fadeIn('fast');
                    }
                    reader.readAsDataURL(input.files[0]);
                }
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
            
            function check_add() {
                var image = $('#image').val();
                var type_id = $('#type_id').val();
                var country_id = $('#country_id').val();
                if (image == "" || type_id == "" || country_id == "") {
                    toastr.error('กรุณากรอกข้อมูลให้ครบถ้วนก่อนบันทึกรายการ');
                    return false;
                }
            }
        </script>
        <!-- END: JS Assets-->
</body>

</html>
