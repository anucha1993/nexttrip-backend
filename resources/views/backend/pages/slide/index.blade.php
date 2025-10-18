<!DOCTYPE html>
<html lang="en" class="light">
<!-- BEGIN: Head -->

<head>

    <!-- BEGIN: CSS Assets-->
    @include("backend.layout.css")
    <!-- END: CSS Assets-->
    <style>
        /* select2 */
        .select2-selection__rendered {
            line-height: 33px !important;
        }
        /*ความยาว*/
        .select2-container .select2-selection--single {
            height: 37px !important;
            margin-top: 0.5px !important;
        }
        /*ลูกศร*/
        .select2-selection__arrow {
            height: 35px !important;
        }
        /*สีกรอบ*/
        .select2-container--default .select2-selection--single {
            /* border: 1px solid #2C2727 !important; */
            border: 1px solid rgb(226, 232, 240) !important;
        }
    </style>
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

            <h2 class="intro-y text-lg font-medium mt-10">แบนเนอร์สไลด์</h2>
            
            <form method="post" action="{{url('webpanel/slide/edit-status')}}" enctype="multipart/form-data">
                @csrf
                <div class="intro-y col-span-12 flex flex-wrap items-center mt-2">
                    <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                        <div class="w-full sm:w-auto mt-2 mr-2 sm:mt-0 sm:ml-auto md:ml-0 mb-2 sm-mb-1" >
                            <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">ระยะเวลาเฟดรูปภาพ <span class="text-danger">(หน่วยเป็นวินาที)</span></label>
                            <input type="number" value="{{$row->time_slide}}" min="1" max="9999" name="time" class="form-control w-56 box  sm:w-20">
                        </div>
                        <div class="w-full sm:w-auto mt-2 mr-2 sm:mt-0 sm:ml-auto md:ml-0 mb-2 sm-mb-1">
                            <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">สถานะการสไลด์รูปภาพ</label>
                            <select name="slide" class="form-select w-full sm:w-20">
                                <option value="on" @if($row->status_slide == 'on') selected @endif>เปิด</option>
                                <option value="off" @if($row->status_slide == 'off') selected @endif>ปิด</option>
                            </select>
                        </div>
                    </div>
                    <div class="w-full sm:w-auto mt-2 mr-2 sm:mt-0 sm:ml-auto md:ml-0 mb-2 sm-mb-1">
                        <button type="submit" class="btn btn-success">บันทึกข้อมูล</button>
                    </div>
                    <div class="hidden md:block mx-auto text-slate-500"></div>
                    <div class="float-right">
                        @if(@$menu_control->add == "on")
                        <a href="{{ url("$segment/$folder/add") }}" class="btn btn-primary shadow-md mr-2">เพิ่มข้อมูล</a>
                        @endif
                    </div>
                </div>
            </form>
            <br>
            <div class="intro-y box p-5 place-content-center">
                <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
                    <div class="hidden md:block mx-auto text-slate-500"></div>
                </div>
                <div class="overflow-x-auto" style="background-color: #F5F5F5;padding:15px;border-radius:5px;">
                    <table id="datatable" class="table table-report"></table>
                </div>
            </div>
                
        <!-- BEGIN: JS Assets-->
        @include("backend.layout.script")
        <script>
            var fullUrl = window.location.origin + window.location.pathname;
            var oTable;

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
                        url: fullUrl + "/datatable",
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
                            data: 'status',
                            title: 'สถานะการแสดงผล',
                            className: 'whitespace-nowrap w-20 text-center'
                        }, // 2
                        {
                            data: 'created_at',
                            title: '<center>วันที่สร้าง</center>',
                            className: 'whitespace-nowrap w-30 text-center'
                        }, // 3
                        {
                            data: 'action',
                            title: '<center>จัดการ</center>',
                            className: 'whitespace-nowrap w-20 text-center',
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
                        return fetch(fullUrl + '/destroy/' + id)
                            .then(response => response.json())
                            .then(data => location.reload())
                            .catch(error => {
                                Swal.showValidationMessage(`Request failed: ${error}`)
                            })
                    }
                });
            }
            function status(ids) {
                const $this = $(this),
                    id = ids;
                $.ajax({
                    type: 'get',
                    url: fullUrl + '/status/' + id,
                    success: function(res) {
                        if (res == false) {
                            $(this).prop('checked', false)
                        }
                    }
                });
            }
        </script>
        <!-- END: JS Assets-->
</body>

</html>
