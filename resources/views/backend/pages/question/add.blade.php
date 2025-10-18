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
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label"><span class="text-danger">*</span>หัวข้อหลัก</label>
                                        <input type="text" name="title" id="title" class="form-control">
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6 mt-3 mb-3">
                                    <div class="col-span-12 lg:col-span-12">
                                        <label for="crud-form-1" class="form-label">หัวข้อย่อยและรายละเอียด <button class="btn btn-primary btn-block" id="add-row" type="button"style="margin: auto;" colspan="2" onclick="add_column()"><i class="fa fa-plus-circle" ></i> เพิ่มแถว</button></label>
                                        <table class="table table-striped table-bordered" >
                                            <thead class="table-light">
                                                <tr role="row">
                                                    <th  class="vertid text-center sorting_asc" style="width: 30%">หัวข้อย่อย</th>
                                                    <th  class="vertid text-center" style="width: 30%">รายละเอียด</th>
                                                    <th  class="vertid text-center" style="width: 3%">จัดการ</th>
                                                </tr>
                                            </thead>
                                            <tbody id="row_data">
                                                <tr id="del1">
                                                    <td>
                                                        <input class="form-control" id="topic1" name="detail_topic[]" type="text" >
                                                    </td>
                                                    <td>
                                                        <textarea class="form-control" id="detail1" name="detail[]" rows="3"></textarea>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-danger del-btn btn-block" type="button" style="margin:auto;" onclick="del_row(1)">ลบแถว</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
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
        var count_row = 2;
            function add_column(){  
                add = '<tr id="del'+count_row+'" >'+
                    '<td><input class="form-control" name="detail_topic[]" id="topic'+count_row+'" type="text" ></td>'+
                    '<td><textarea class="form-control" name="detail[]" id="detail'+count_row+'" rows="3"></textarea></td>'+
                    '<td><button class="btn btn-danger del-btn btn-block" type="button" style="margin:auto;" onclick="del_row('+count_row+')">ลบแถว</button>'+
                    '</td>'+
                    '</tr>';   
                $('#row_data').append(add);
                count_row++;  
            }
            function del_row(d){
                let text = "ยืนยันการลบข้อมูล";
                if (confirm(text) == true) {
                    var de = document.getElementById('del'+d);
                    de.parentNode.removeChild(de);
                } 
            }
            function check_add() {
                var title = $('#title').val();
                if (title == "") {
                    toastr.error('กรุณากรอกข้อมูลให้ครบถ้วนก่อนบันทึกรายการ');
                    return false;
                }
            }
        </script>
        <!-- END: JS Assets-->
</body>

</html>
