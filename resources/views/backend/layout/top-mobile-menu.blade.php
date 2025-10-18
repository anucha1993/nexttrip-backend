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

<div class="mobile-menu md:hidden">
    <div class="mobile-menu-bar">
        <a href="{{url("webpanel")}}" class="flex mr-auto">
            <img alt="Midone - HTML Admin Template" class="w-6" src="{{asset("backend/dist/images/logo.svg")}}">
            <span class="text-white text-lg ml-3"> Nexttrip </span> &nbsp;
        </a>
        <a href="javascript:void(0);" id="mobile-menu-toggler"> <i data-lucide="bar-chart-2" class="w-8 h-8 text-white transform -rotate-90"></i> </a>
    </div>
    <ul class="border-t border-white/[0.08] py-5 hidden">
        <li>
            <a href="{{url("webpanel")}}" class="menu">
                <div class="menu__icon"> <i data-lucide="home"></i> </div>
                <div class="menu__title"> Dashboard </div>
            </a>
        </li>

        @php $menu = \App\Models\Backend\MenuModel::where(['position' => 'main', 'status' => 'on'])->orderBy('sort')->get(); @endphp
        @foreach ($menu as $i => $m)
            @php
                $second = \App\Models\Backend\MenuModel::where('_id', $m->id)
                    ->where('status', 'on')
                    ->orderBy('sort')
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
                    <a href="@if (count($second) > 0) javascript:void(0); @else webpanel{!! $m->url !!} @endif" class="menu @if($linku == $m->url) menu--active @endif">
                        <div class="menu__icon"> <i data-lucide="{!! $m->icon !!}"></i> </div>
                        <div class="menu__title"> 
                            {{ $m->name }} 
                            @if (count($second) > 0)
                                <i data-lucide="chevron-down" class="menu__sub-icon transform rotate-180"></i>
                            @endif
                        </div>
                    </a>
                    @if (count($second) > 0)
                    <ul class="">
                        @foreach ($second as $i => $s)
                            @if (in_array($s->id, $array_role))
                            <li>
                                <a href="{{url("webpanel/$s->url")}}" class="menu">
                                    <div class="menu__icon"> <i data-lucide="{{ $s->icon }}"></i> </div>
                                    <div class="menu__title"> {{ $s->name }} </div>
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
</div>