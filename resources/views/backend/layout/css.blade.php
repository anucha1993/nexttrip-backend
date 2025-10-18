<base href="{{ url('/th') }}">
<meta name="csrf-token" content="{{ csrf_token() }}" />
<meta charset="utf-8">
<link href="{{asset("backend/dist/images/logo.svg")}}" rel="shortcut icon">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="author" content="">
<title>Backend System</title>
<link rel="stylesheet" href="https://unpkg.com/flowbite@1.4.7/dist/flowbite.min.css" />
<link rel="stylesheet" type="text/css" href="{{ asset('backend/libs/toastr/toastr.min.css')}}">
<link rel="stylesheet" type="text/css" href="{{ asset('backend/dist/css/app.css')}}">
{{-- <link rel="stylesheet" type="text/css" href="{{ asset('backend/dist/css/_app.css')}}"> --}}
<link rel="stylesheet" type="text/css" href="{{ asset('backend/libs/fontawesome/icons.min.css')}}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link href="{{ asset('backend/libs/select2/css/select2.min.css')}}" rel="stylesheet" type="text/css" />

<!-- Sweet Alert-->
<link href="{{ asset('backend/libs/sweetalert2/sweetalert2.min.css')}}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.12.1/datatables.min.css"/>



<style>
    .swal2-container {
        z-index: 99999 !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 0.2rem;
        border: 1px solid #dee2e6;
        background: #ffffff;
        /* color: #fff !important; */
    }
    /* hover ปุ่มเปลี่ยนหน้า*/
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #003EA3 !important;
        color: #fff !important;
        border: 1px solid #ffffff !important;
    }
    /* หน้าที่เลือกแล้ว hover */
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #003EA3 !important;
        color:#fff !important;
        border: 1px solid #ffffff !important;
    }
    /* หน้าที่เลือก */
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #003EA3 !important;
        color:#fff !important;

    }
    table.dataTable.no-footer {
        border-bottom: none !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover{
        background: #003EA3 !important;
        color:#fff !important;
    }

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
        border: 1px solid #2C2727 !important;
        /* border: 1px solid rgb(226, 232, 240) !important; */
    }
    
</style>