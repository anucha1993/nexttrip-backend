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

            <h2 class="intro-y text-lg font-medium mt-10">ข้อมูลวิดีโอที่เกี่ยวข้อง</h2>

            {{-- <div class="intro-y flex flex-col sm:flex-row items-center mt-2">
                <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                    <div class="w-full sm:w-auto mt-3 mr-2 sm:mt-0 sm:ml-auto md:ml-0">
                        <div class="w-56 relative text-slate-500">
                            <input type="text" class="form-control w-56 box pr-10 myLike" name="name" placeholder="ค้นหาหัวข้อ">
                            <i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-lucide="search"></i>
                        </div>
                    </div>
                    <div class="sm:flex items-center sm:mr-4">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">ประเทศ</label>
                        <select  name="country" class="tom-select w-full" data-placeholder="ค้นหาชื่อประเทศ">
                                <option value="" selected>เลือกข้อมูล</option>
                                @foreach($country as $co)
                                    <option value="{{$co->id}}">{{$co->country_name_th}}</option>
                                @endforeach
                        </select>
                    </div>
                    
                    <div class="sm:flex items-center sm:mr-4">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">เมือง</label>
                        <select  name="city" class="myLike tom-select " data-placeholder="ค้นหาชื่อเมือง">
                                <option value="" selected>เลือกข้อมูล</option>
                                @foreach($city as $ci)
                                <option value="{{$ci->id}}">{{$ci->city_name_th}}</option>
                                @endforeach
                        </select>
                    </div>
                </div>
                <div class="hidden md:block mx-auto text-slate-500"></div>
                    <div class="float-right">
                        <a href="{{ url("$segment/$folder/add") }}" class="btn btn-primary shadow-md mr-2">เพิ่มข้อมูล</a>
                    </div>
            </div> --}}

            
            <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                <div class="w-full sm:w-auto mt-3 mr-4 sm:mt-0 sm:ml-auto md:ml-0">
                    <div class="w-56 relative text-slate-500">
                        <input type="text" class="form-control w-56 box pr-10 myLike" name="name" placeholder="ค้นหาหัวข้อ">
                        <i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-lucide="search"></i>
                    </div>
                </div>
                <div class="w-full sm:w-auto mt-3 mr-4 sm:mt-0 sm:ml-auto md:ml-0">
                    <div class="w-56 relative text-slate-500">
                        {{-- <label class="form-label sm:w-20">ประเทศ</label> --}}
                        <select data-placeholder="เลือกข้อมูล" class="form-control box pr-10 select2 myLike" name="country"  >
                            <option value="0">ค้นหาประเทศ</option>
                            @foreach($country as $co)
                                <option value="{{$co->id}}">{{$co->country_name_th}}</option>
                            @endforeach
                        </select> 
                    </div>
                </div>
                <div class="w-full sm:w-auto mt-3 mr-2 sm:mt-0 sm:ml-auto md:ml-0">
                    <div class="w-56 relative text-slate-500">
                        {{-- <label class="form-label sm:w-20">เมือง</label> --}}
                        <select data-placeholder="เลือกข้อมูล" class="form-control box pr-10 select2 myLike "  name="city" >
                            <option value="0">ค้นหาเมือง</option>
                            @foreach($city as $ci)
                            <option value="{{$ci->id}}">{{$ci->city_name_th}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                 
                <div class="hidden md:block mx-auto text-slate-500"></div>
                <div class="float-right">
                    @if(@$menu_control->add == "on")
                    <a href="{{ url("$segment/$folder/add") }}" class="btn btn-primary shadow-md mr-2">เพิ่มข้อมูล</a>
                    @endif
                </div>
            </div>
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
                        {data: 'DT_RowIndex',    title :'<center>#</center>',    className: 'whitespace-nowrap w-10 text-center'}, // 0
                        {
                            data: 'img',
                            title: '<center>ภาพปก</center>',
                            className: 'items-center justify-center'
                        }, 
                        {
                            data: 'title',
                            title: '<center>หัวข้อ</center>',
                            className: 'whitespace-nowrap w-50 text-center'
                        }, // 2
                        {
                            data: 'category',
                            title: '<center>ประเทศ/เมือง</center>',
                            className: 'whitespace-nowrap w-30 text-center'
                        }, // 2
                        {
                            data: 'status',
                            title: 'สถานะ',
                            className: 'whitespace-nowrap w-20 text-center'
                        }, // 2
                        {
                            data: 'action',
                            title: '<center>จัดการ</center>',
                            className: 'whitespace-nowrap w-10 text-center',
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
