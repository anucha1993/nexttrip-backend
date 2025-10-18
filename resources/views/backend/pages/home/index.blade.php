<!DOCTYPE html>
<html lang="en" class="light">
<!-- BEGIN: Head -->

<head>

    <!-- BEGIN: CSS Assets-->
    @include('backend.layout.css')
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
        <div class="content">
           
        </div>
        <!-- END: Content -->
    </div>

    <!-- BEGIN: JS Assets-->
    @include('backend.layout.script')

    <!-- END: JS Assets-->
</body>

</html>
