<!DOCTYPE html>
<html lang="en" class="light">
    <head>
        <meta charset="utf-8">
        <link href="{{asset("backend/dist/images/logo.svg")}}" rel="shortcut icon">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Midone admin is super flexible, powerful, clean & modern responsive tailwind admin template with unlimited possibilities.">
        <meta name="keywords" content="admin template, Midone Admin Template, dashboard template, flat admin template, responsive admin template, web app">
        <meta name="author" content="LEFT4CODE">
        <title>Nexttrip</title>
        <!-- BEGIN: CSS Assets-->
        <link rel="stylesheet" href="{{asset("backend/dist/css/app.css")}}" />
        <link rel="stylesheet" type="text/css" href="{{ asset('backend/libs/toastr/toastr.min.css')}}">
        <!-- END: CSS Assets-->
    </head>
    <style>
        .login .login__input {
            min-width: 550px !important;
        }
    </style>
    <!-- END: Head -->
    <body class="login">
        <div class="container sm:px-10">
            <div class="block xl:grid grid-cols-2 gap-4">
                <!-- BEGIN: Login Info -->
                <div class="hidden xl:flex flex-col min-h-screen">
                    <a href="" class="-intro-x flex items-center pt-5">
                        <img alt="Midone - HTML Admin Template" class="w-6" src="{{asset("backend/dist/images/logo.svg")}}">
                        <span class="text-white text-lg ml-3"> Nexttrip </span> 
                    </a>
                    <div class="my-auto">
                        <img alt="Midone - HTML Admin Template" class="-intro-x w-1/2 -mt-16" src="{{asset("backend/dist/images/illustration.svg")}}">
                        <div class="-intro-x text-white font-medium text-4xl leading-tight mt-10">
                           กรุณาลงชื่อเข้าใช้งานระบบของเรา
                        </div>
                        <div class="-intro-x mt-5 text-lg text-white text-opacity-70 dark:text-slate-400">จัดการข้อมูลของคุณโดยระบบหลังบ้าน Orange Technology</div>
                    </div>
                </div>
                <!-- END: Login Info -->
                <!-- BEGIN: Login Form -->
               
                   
                    <div class="h-screen xl:h-auto flex py-5 xl:py-0 my-10 xl:my-0">
                        <div class="my-auto mx-auto xl:ml-20 bg-white dark:bg-darkmode-600 xl:bg-transparent px-5 sm:px-8 py-8 xl:p-0 rounded-md shadow-md xl:shadow-none w-full sm:w-3/4 lg:w-2/4 xl:w-auto">
                            <form action="" method="post">
                            @csrf
                                <h2 class="intro-x font-bold text-2xl xl:text-3xl text-center xl:text-left">
                                    ลงชื่อเข้าใช้
                                </h2>
                                <div class="intro-x mt-2 text-slate-400 xl:hidden text-center">จัดการข้อมูลของคุณโดยระบบหลังบ้าน Orange Technology</div>
                                
                                <div class="intro-x mt-8 col-md-12">

                                    <div class="mb-2">
                                        @if (Session('error'))
                                        <div class="alert alert-danger show flex items-center mb-2" role="alert"> <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> {{ Session('error') }} </div>
                                        @endif
                                    </div>

                                    <input type="text" id="username" name="username" value="" class="intro-x login__input form-control py-3 px-4 block" placeholder="ชื่อผู้ใช้งาน">
                                    <input type="password" id="password" name="password" value="" class="intro-x login__input form-control py-3 px-4 block mt-4" placeholder="รหัสผ่าน">
                                  
                                </div>
                                {{-- <div class="intro-x flex text-slate-600 dark:text-slate-500 text-xs sm:text-sm mt-4">
                                    <div class="flex items-center mr-auto">
                                        <input id="remember-me" type="checkbox" class="form-check-input border mr-2">
                                        <label class="cursor-pointer select-none" for="remember-me">จดจำ</label>
                                    </div>
                                    <a href="javascript:void(0)" onclick="contact();">ลืมรหัสผ่าน</a> 
                                </div> --}}
                                <div class="intro-x mt-5 xl:mt-8 text-center xl:text-left">
                                    <button class="btn btn-primary py-3 px-4 w-full xl:w-32 xl:mr-3 align-top">ล็อคอิน</button>
                                </div>
                                {{-- <div class="intro-x mt-10 xl:mt-24 text-slate-600 dark:text-slate-500 text-center xl:text-left"> ในการเป็นสมาชิกระบบของเราแสดงว่าคุณยอมรับ <a class="text-primary dark:text-slate-200" href="javascript:void(0);">ข้อกำหนดและเงื่อนไข</a> และ <a class="text-primary dark:text-slate-200" href="javascript:void(0);">นโยบายความเป็นส่วนตัว</a> </div> --}}
                            </form>
                        </div>
                    </div>
                <!-- END: Login Form -->
            </div>
        </div>

        <!-- BEGIN: JS Assets-->
        <script src="{{asset('backend/libs/jquery/jquery.min.js')}}"></script>
        <script src="{{asset("backend/dist/js/app.js")}}"></script>
        <script src="{{asset('backend/libs/toastr/toastr.js')}}"></script>
        <!-- END: JS Assets-->

        <script>
            function contact() {
                // alert(1)
                toastr.error("ระบบยังไม่เปิดให้บริการ");
                return false;
            }
        </script>
    </body>
</html>