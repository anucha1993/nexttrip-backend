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
                                    <div class="col-span-12 lg:col-span-4">
                                        <h6 class="mb-3">โลโก้</h6>
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
                                <?php   $tag_select = json_decode($row->tag,true); 
                                        @$type_select = \App\Models\Backend\TypeCustomerModel::find($row->type_id);
                                        $country_select = json_decode($row->country_id,true);
                                ?>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label"><span class="text-danger">*</span>ประเทศ</label>
                                        <select  id="country_id" class="tom-select w-full" multiple name="country_id[]" >
                                            @foreach ($country as $c)
                                                <option  @if(in_array($c->id,$country_select)) selected @endif value="{{$c->id}}">{{$c->country_name_th}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-span-12 lg:col-span-6">
                                        <label for="crud-form-1" class="form-label"><span class="text-danger">*</span>ประเภท</label>
                                        <select data-placeholder="Select Suggest" class="form-control"  name="type_id" id="type_id">
                                            <option value="" selected hidden>กรุณาเลือกข้อมูล</option>
                                            @foreach($type as $t)
                                            <option @if($type_select->id == $t->id) selected @endif value="{{$t->id}}">{{$t->type}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-8">
                                        <label for="crud-form-1" class="form-label">ชื่อลูกค้า/บริษัท</label>
                                        <input type="text" id="company" name="company" class="form-control" value="{{$row->company}}">
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
                                        <textarea id="detail" name="detail" class="tiny">{{$row->detail}}</textarea>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">แฮชแท็ก</label>
                                        {{-- <select data-placeholder="กรุณาเลือกข้อมูล" class="tom-select w-full" multiple name="tag[]" >
                                            @foreach($tag as $t)
                                            <option @if(in_array($t->id,$tag_select)) selected @endif  value="{{$t->id}}">{{$t->tag}}</option>
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
            <!-- END: Content -->
            <h2 class="intro-y text-lg font-medium mt-10">แก้ไขอัลบั้มรูป</h2>
            <br>
            <div class="intro-y box p-5 place-content-center">
                <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                    <div class="hidden md:block mx-auto text-slate-500"></div>
                </div>
                <div style="background-color: #F5F5F5;padding:15px;border-radius:5px;">
                    <table id="datatable" class="table table-report"></table>
                </div>
                <!-- DATA TABLE -->
            </div>
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
            var id = "{{$id}}";
            var fullUrl = "{{url('webpanel/customer-info')}}";
            var oTable;
            var _token = '{{csrf_token()}}';
            $(function () {
                oTable = $('#datatable').DataTable({
                    searching: false,
                    ordering: false,
                    lengthChange: false,
                    pageLength: 10,
                    processing: true,
                    serverSide: true,
                    "language": {
                        "lengthMenu": "แสดง _MENU_ แถว",
                        "zeroRecords": "ไม่พบข้อมูล",
                        "info": "แสดงหน้า _PAGE_ จาก _PAGES_ หน้า",
                        "search": "ค้นหา",
                        "infoEmpty": "",
                        "infoFiltered": "",
                        "paginate": {
                            "first": "หน้าแรก",
                            "previous": "ย้อนกลับ",
                            "next": "ถัดไป",
                            "last": "หน้าสุดท้าย"
                        },
                        'processing': "กำลังโหลดข้อมูล",
                    },
                    iDisplayLength: 25,
                    ajax: {
                        url: fullUrl+"/"+id+"/datatable-gallery?_token="+_token,
                        data: function (d) {
                            d.Like = {};
                            $('.myLike').each(function () {
                                if ($.trim($(this).val()) && $.trim($(this).val()) != '0') {
                                    d.Like[$(this).attr('name')] = $.trim($(this).val());
                                }
                            });
                            oData = d;
                            
                        },
                       
                        method: 'POST'
                    },
                    columns: [
                        // {data: 'DT_RowIndex',    title :'#',    className: 'whitespace-nowrap w-10 text-center'}, // 0
                        {
                            data: 'img',
                            title: '<center>รูปภาพ</center>',
                            className: 'items-center justify-center'
                        }, //1
                        {
                            data: 'created_at',
                            title: '<center>วันที่สร้าง</center>',
                            className: 'whitespace-nowrap w-60 text-center'
                        }, // 3
                        {
                            data: 'action',
                            title: '<center>จัดการ</center>',
                            className: 'whitespace-nowrap w-60 text-center',
                        }, // 4
                    ],
                    rowCallback: function (nRow, aData, dataIndex) {

                    }
                });
                $('.myWhere,.myLike,.myCustom,#onlyTrashed').on('change', function (e) {
                    // console.log($(this).val())
                    oTable.draw();
                });
            });

            function deleteItem(ids) {
                const id = [ids];
                if (id.length > 0) {
                    destroy(id)
                }
            }

            function destroy(id) {
                Swal.fire({
                    title: "ลบข้อมูล",
                    text: "คุณต้องการลบข้อมูลใช่หรือไม่?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return fetch(fullUrl + '/destroy-gallery/' + id)
                            .then(response => response.json())
                            .then(data => location.reload())
                            .catch(error => {
                                Swal.showValidationMessage(`Request failed: ${error}`)
                            })
                    }
                });
            }
        </script>
        <!-- END: JS Assets-->
</body>

</html>
