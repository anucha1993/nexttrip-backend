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

<nav class="side-nav">
    <a href="{{url("/webpanel/slide")}}" class="intro-x flex items-center pl-5 pt-4">
        <img alt="Midone - HTML Admin Template" class="w-6" src="{{ asset('backend/dist/images/logo.svg') }}">
        <span class="hidden xl:block text-white text-lg ml-3"> Nexttrip </span>
    </a>
    <div class="side-nav__devider my-6"></div>
    <ul>
        <li>
            {{-- <a href="side-menu-light-calendar.html" class="side-menu side-menu--active"> --}}
            {{-- <a href="{{url("/webpanel")}}" class="side-menu">
                <div class="side-menu__icon"> <i data-lucide="home"></i> </div>
                <div class="side-menu__title"> Dashboard </div>
            </a> --}}
        </li>
        
        @php
            $menu = \App\Models\Backend\MenuModel::where(['position' => 'main', 'status' => 'on'])
                ->orderBy('sort','asc')
                ->get();
        @endphp

        @foreach ($menu as $i => $m)
            @php
                $second = \App\Models\Backend\MenuModel::where('_id', $m->id)
                    ->where('status', 'on')
                    ->orderBy('sort','asc')
                    ->get();
            @endphp
            @if (in_array($m->id, $array_role))
                @php 
                    $linku = "";
                    $link_url = Route::current()->uri(); 
                    try
                    {
                        $linku = '/'.explode("/",@$link_url)[1];
                    }
                    catch (\Exception $e){
                        
                    }
                    
                    
                @endphp
                <li>
                    <a href="@if (count($second) > 0) javascript:void(0); @else webpanel{!! $m->url !!} @endif" class="side-menu @if($linku == $m->url) side-menu--active @endif ">
                        <div class="side-menu__icon"> <i data-lucide="{!! $m->icon !!}"></i> </div>
                        <div class="side-menu__title"> {{ $m->name }} </div>
                        @if (count($second) > 0)
                            <div class="side-menu__sub-icon "> <i data-lucide="chevron-down"></i> </div>
                        @endif
                    </a>
                    @if (count($second) > 0)
                    <ul class="">
                        @foreach ($second as $i => $s)
                            @if (in_array($s->id, $array_role))
                            <li>
                                <a href="{{url("webpanel/$s->url")}}" class="side-menu">
                                    <div class="side-menu__icon"> <i data-lucide="{{ $s->icon }}"></i> </div>
                                    <div class="side-menu__title"> {{ $s->name }} </div>
                                </a>
                            </li>
                            @endif
                        @endforeach
                    </ul>
                    @endif
                </li>
            @endif
        @endforeach

        <li class="side-nav__devider my-6"></li>

        <!-- Developer Menu -->
        {{-- <li>
            <a href="{{url("webpanel/menu")}}" class="side-menu side-menu">
                <div class="side-menu__icon"> <i data-lucide="home"></i> </div>
                <div class="side-menu__title"> Setting Menu </div>
            </a>
        </li> --}}

        @if(@$member_menu->role == 1)
        <li>
            <a href="javascript:;" class="side-menu">
                <div class="side-menu__icon"> <i data-lucide="settings"></i> </div>
                <div class="side-menu__title">
                    System Management
                    <div class="side-menu__sub-icon "> <i data-lucide="chevron-down"></i> </div>
                </div>
            </a>
            <ul class="">
                <li>
                    <a href="{{url("webpanel/api-management")}}" class="side-menu {{ request()->is('webpanel/api-management*') ? 'side-menu--active' : '' }}">
                        <div class="side-menu__icon"> <i data-lucide="link"></i> </div>
                        <div class="side-menu__title"> API Management </div>
                    </a>
                </li>
            </ul>
        </li>

        <li>
            <a href="javascript:;" class="side-menu">
                <div class="side-menu__icon"> <i data-lucide="user"></i> </div>
                <div class="side-menu__title">
                    Users Backend
                    <div class="side-menu__sub-icon "> <i data-lucide="chevron-down"></i> </div>
                </div>
            </a>
            <ul class="">
                <li>
                    <a href="{{url("webpanel/role")}}" class="side-menu">
                        <div class="side-menu__icon"> <i data-lucide="activity"></i> </div>
                        <div class="side-menu__title"> Permission </div>
                    </a>
                </li>
                <li>
                    <a href="{{url("webpanel/user")}}" class="side-menu">
                        <div class="side-menu__icon"> <i data-lucide="activity"></i> </div>
                        <div class="side-menu__title"> Account </div>
                    </a>
                </li>
            </ul>
        </li>
        @endif
        <!-- End Menu -->


    </ul>
</nav>
