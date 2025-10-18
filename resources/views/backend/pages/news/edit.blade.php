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
                    <div class="col-span-12 lg:col-span-6 2xl:col-span-12">
                        <div class="intro-y col-span-12 lg:col-span-6">
                            <!-- BEGIN: Form Layout -->
                            <div class="intro-y box p-5 place-content-center">
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <h6 class="mb-3"><span class="text-danger">*</span>รูปภาพปก <span class="text-danger">ขนาด 736*494 </span></h6>
                                        <img src="@if(@$row->img == null) {{url("noimage.jpg")}} @else {{asset($row->img)}} @endif"
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
                                <?php  $tag_select = json_decode($row->tag,true); 
                                        $type_select = \App\Models\Backend\TypeNewModel::find($row->type_id);
                                ?>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label"><span class="text-danger">*</span>ประเภทข่าว</label>
                                        <select name="type_id" id="type_id" class="form-control" >
                                            <option value="" selected hidden>กรุณาเลือกข้อมูล</option>
                                            @foreach ($type as $typ)
                                                <option @if($type_select->id == $typ->id) selected @endif value="{{$typ->id}}">{{$typ->type}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label"><span class="text-danger">*</span>หัวข้อข่าว</label>
                                        <input type="text" id="title" name="title" class="form-control" value="{{$row->title}}">
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">รายละเอียด</label>
                                        <textarea  name="detail" class="tiny">{!! $row->detail !!}</textarea>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">แฮชแท็ก</label>
                                        {{-- <select data-placeholder="Select Suggest" class="tom-select w-full" multiple name="tag[]" >
                                            @foreach($tag as $t)
                                            <option @if(in_array($t->id,$tag_select)) selected @endif value="{{$t->id}}">{{$t->tag}}</option>
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
        
        @include("backend.layout.tag")
        <script>
            <?php echo "var data = $data_tag;"; 
                  echo "var arr = $t_select;"; 
            ?>
            SaveTag();
        </script>
        <script>
           
           
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
