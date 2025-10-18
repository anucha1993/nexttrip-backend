@php
$member_menu = \App\Models\Backend\User::find(Auth::Guard()->id());
$roles = \App\Models\Backend\RoleModel::find($member_menu->role);
$list_role = \App\Models\Backend\Role_listModel::where(['role_id' => @$roles->id])->get();

$array_role = [];

if ($list_role) {
    foreach ($list_role as $list) {
        if ($list->read == 'on') {
            array_push($array_role, $list->menu_id);
            $menu_check = \App\Models\Backend\MenuModel::find($list->menu_id);
            if (@$menu_check->_id != null) {
                array_push($array_role, $menu_check->_id);
            }
        }
    }
}
@endphp

<!-- BEGIN: Top Bar -->
<div class="border-b border-white/[0.08] -mt-10 md:-mt-5 -mx-3 sm:-mx-8 px-3 sm:px-8 pt-3 md:pt-0 mb-10">
    <div class="top-bar-boxed flex items-center">
        <!-- BEGIN: Logo -->
        <a href="{{url("webpanel")}}" class="-intro-x hidden md:flex">
            {{-- <img alt="Midone - HTML Admin Template" class="w-6" src="dist/images/logo.svg"> --}}
            <span class="text-white text-lg ml-3"> Nexttrip </span> 
        </a>
        <!-- END: Logo -->
        <!-- BEGIN: Breadcrumb -->
        <nav aria-label="breadcrumb" class="-intro-x h-full mr-auto">
            @if(@$navs)
            <ol class="breadcrumb breadcrumb-light">
                @foreach ($navs as $nav)
                    @if($nav['last'] == 0)
                        <li class="breadcrumb-item"><a href="{{$nav['url']}}">{{$nav['name']}}</a></li>
                    @else
                        <li class="breadcrumb-item active" aria-current="page"><a href="{{$nav['url']}}">{{$nav['name']}}</a></li>
                    @endif
                @endforeach
            </ol>
            @endif
        </nav>
        <!-- END: Breadcrumb -->
      
        <!-- BEGIN: Account Menu -->
        <div class="intro-x dropdown w-8 h-8">
            <div class="dropdown-toggle w-8 h-8 rounded-full overflow-hidden shadow-lg image-fit zoom-in scale-110" role="button" aria-expanded="false" data-tw-toggle="dropdown">
                <img alt="Midone - HTML Admin Template" src="{{asset('backend/dist/images/profile-13.jpg')}}">
            </div>
            @php $user = App\Models\Backend\User::find(Auth::guard()->id()); 
            $role = App\Models\Backend\RoleModel::where('id',$user->role)->first();
            @endphp
            <div class="dropdown-menu w-56">
                <ul class="dropdown-content bg-primary/80 before:block before:absolute before:bg-black before:inset-0 before:rounded-md before:z-[-1] text-white">
                    <li class="p-2">
                        <div class="font-medium">{{@$user->name}}</div>
                        <div class="text-xs text-white/60 mt-0.5 dark:text-slate-500">{{@$role->name}}</div>
                    </li>
                    <li>
                        <hr class="dropdown-divider border-white/[0.08]">
                    </li>
                    <li>
                        <a href="{{url("/webpanel/user/edit/$user->id")}}" class="dropdown-item hover:bg-white/5"> <i data-lucide="user" class="w-4 h-4 mr-2"></i> Profile </a>
                    </li>
                    {{-- <li>
                        <a href="{{url("/webpanel/user")}}" class="dropdown-item hover:bg-white/5"> <i data-lucide="edit" class="w-4 h-4 mr-2"></i> Add Account </a>
                    </li>
                    <li>
                        <a href="{{url("/webpanel/user/edit/$user->id")}}" class="dropdown-item hover:bg-white/5"> <i data-lucide="lock" class="w-4 h-4 mr-2"></i> Reset Password </a>
                    </li> --}}
                    <li>
                        <hr class="dropdown-divider border-white/[0.08]">
                    </li>
                    <li>
                        <a href="{{url("webpanel/logout")}}" class="dropdown-item hover:bg-white/5"> <i data-lucide="toggle-right" class="w-4 h-4 mr-2"></i> Logout </a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- END: Account Menu -->
    </div>
</div>
<!-- END: Top Bar -->
<!-- BEGIN: Top Menu -->
@php
    $linku = '';
    $link_url = Route::current()->uri();
    try {
        $linku = '/' . explode('/', @$link_url)[1];
    } catch (\Exception $e) {
    }
@endphp
<nav class="top-nav">
    @php
        $menu = \App\Models\Backend\MenuModel::where(['position' => 'main', 'status' => 'on'])
            ->orderBy('name','asc')
            ->get();
    @endphp
    <ul>
        <li>
            <a href="{{url("webpanel")}}" class="top-menu @if($linku == '') top-menu--active @endif">
                <div class="top-menu__icon"> <i data-lucide="home"></i> </div>
                <div class="top-menu__title"> Dashboard </div>
            </a>
        </li>

        @foreach ($menu as $i => $m)
            @php
                $second = \App\Models\Backend\MenuModel::where('_id', $m->id)
                    ->where('status', 'on')
                    ->orderBy('name','asc')
                    ->get();
            @endphp
            @if (in_array($m->id, $array_role))
               
                <li>
                    <a href="@if (count($second) > 0) javascript:void(0); @else webpanel{!! $m->url !!} @endif" class="top-menu @if($linku == $m->url) top-menu--active @endif">
                        <div class="top-menu__icon"> <i data-lucide="{!! $m->icon !!}"></i> </div>
                        <div class="top-menu__title">  {{ $m->name }} 
                            @if (count($second) > 0)
                                <i data-lucide="chevron-down" class="top-menu__sub-icon"></i> 
                            @endif
                        </div>
                    </a>
                    @if (count($second) > 0)
                    <ul class="top-menu__sub-open">
                        @foreach ($second as $i => $s)
                            @if (in_array($s->id, $array_role))
                            <li>
                                <a href="{{url("webpanel/$s->url")}}" class="top-menu">
                                    <div class="top-menu__icon"> <i data-lucide="{{ $s->icon }}"></i> </div>
                                    <div class="top-menu__title"> {{ $s->name }} </div>
                                </a>
                            </li>
                            @endif
                        @endforeach
                    </ul>
                    @endif
                </li>
            @endif
        @endforeach
    </ul>
</nav>