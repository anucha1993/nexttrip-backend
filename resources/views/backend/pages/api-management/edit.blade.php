
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
            <h2 class="intro-y text-lg font-medium mt-10">แก้ไข API Provider</h2>
            <div class="grid grid-cols-12 gap-6 mt-5">
                <div class="intro-y col-span-12 lg:col-span-12">
                    <!-- BEGIN: Form Layout -->

                    <div class="intro-y box p-5">
                        <form id="edit-provider-form" method="POST" action="{{ route('api-management.update', $provider->id) }}">
                            @csrf
                            @method('PUT')

            <!-- หมวด 1: ข้อมูลพื้นฐาน -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                        <i class="lucide lucide-info w-5 h-5 mr-2 text-blue-600"></i>
                        1. ข้อมูลพื้นฐาน API Provider
                    </h2>
                    <p class="text-gray-600 text-sm mt-1">ข้อมูลเบื้องต้นของ API Provider</p>
                </div>
                <div class="p-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">ชื่อ Provider *</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $provider->name) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700 mb-2">รหัส Provider *</label>
                            <input type="text" id="code" name="code" value="{{ old('code', $provider->code) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   required readonly>
                            @error('code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">รหัสไม่สามารถแก้ไขได้หลังจากสร้างแล้ว</p>
                        </div>

                        <div class="md:col-span-2">
                            <label for="url" class="block text-sm font-medium text-gray-700 mb-2">API URL (หลัก) *</label>
                            <input type="url" id="url" name="url" value="{{ old('url', $provider->url) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   required>
                            @error('url')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">URL หลักสำหรับดึงข้อมูล tours</p>
                        </div>
                        
                        <!-- Multi-step API Configuration -->
                        <div class="md:col-span-2">
                            <div class="flex items-center mb-3">
                                <input id="requires_multi_step" name="requires_multi_step" type="checkbox" value="1" 
                                       {{ old('requires_multi_step', $provider->requires_multi_step) ? 'checked' : '' }}
                                       onchange="toggleMultiStepFields()"
                                       class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <label for="requires_multi_step" class="text-sm font-medium text-gray-700">
                                    API แบบหลายขั้นตอน (Multi-step API)
                                </label>
                            </div>
                            <p class="text-xs text-gray-500">เลือกถ้า API ต้องเรียกหลาย endpoint เพื่อดึงข้อมูล periods</p>
                        </div>
                        
                        <div id="multi-step-fields" class="md:col-span-2 {{ old('requires_multi_step', $provider->requires_multi_step) ? '' : 'hidden' }}">
                            <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="tour_detail_endpoint" class="block text-sm font-medium text-gray-700 mb-2">Tour Detail Endpoint</label>
                                        <input id="tour_detail_endpoint" name="tour_detail_endpoint" type="text" 
                                               value="{{ old('tour_detail_endpoint', $provider->tour_detail_endpoint) }}"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                               placeholder="/api/tours/{id}">
                                        <p class="mt-1 text-xs text-gray-500">ใช้ {id} สำหรับ parameter</p>
                                    </div>
                                    <div>
                                        <label for="period_endpoint" class="block text-sm font-medium text-gray-700 mb-2">Period Endpoint</label>
                                        <input id="period_endpoint" name="period_endpoint" type="text" 
                                               value="{{ old('period_endpoint', $provider->period_endpoint) }}"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                               placeholder="/api/tours/{id}/periods">
                                        <p class="mt-1 text-xs text-gray-500">ใช้ {id} สำหรับ parameter</p>
                                    </div>
                                </div>
                                @php
                                    $urlParameters = $provider->url_parameters;
                                    if (is_string($urlParameters)) {
                                        $urlParameters = json_decode($urlParameters, true) ?: [];
                                    }
                                    $urlParameters = is_array($urlParameters) ? $urlParameters : [];
                                @endphp
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="tour_detail_id_field" class="block text-sm font-medium text-gray-700 mb-2">Tour ID Field</label>
                                        <input id="tour_detail_id_field" name="url_parameters[tour_detail_id_field]" type="text" 
                                               value="{{ old('url_parameters.tour_detail_id_field', $urlParameters['tour_detail_id_field'] ?? '') }}"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                               placeholder="P_ID">
                                        <p class="mt-1 text-xs text-gray-500">ชื่อฟิลด์ที่เก็บ ID ของ tour</p>
                                    </div>
                                    <div>
                                        <label for="period_id_field" class="block text-sm font-medium text-gray-700 mb-2">Period ID Field</label>
                                        <input id="period_id_field" name="url_parameters[period_id_field]" type="text" 
                                               value="{{ old('url_parameters.period_id_field', $urlParameters['period_id_field'] ?? '') }}"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                               placeholder="P_ID">
                                        <p class="mt-1 text-xs text-gray-500">ชื่อฟิลด์ที่ใช้ในการเรียก period endpoint</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">คำอธิบาย</label>
                            <textarea id="description" name="description" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('description', $provider->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">สถานะ</label>
                            <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="active" {{ old('status', $provider->status) == 'active' ? 'selected' : '' }}>🟢 เปิดใช้งาน</option>
                                <option value="inactive" {{ old('status', $provider->status) == 'inactive' ? 'selected' : '' }}>🔴 ปิดใช้งาน</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- หมวด 2: HTTP Headers -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                                <i class="lucide lucide-key w-5 h-5 mr-2 text-green-600"></i>
                                2. HTTP Headers
                            </h2>
                            <p class="text-gray-600 text-sm mt-1">กำหนด Headers สำหรับการเรียก API</p>
                        </div>
                        <button type="button" onclick="addHeader()" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                            <i class="lucide lucide-plus w-4 h-4 mr-2"></i>
                            เพิ่ม Header
                        </button>
                    </div>
                </div>
                <div class="p-6">
                <div id="headers-container" class="space-y-3">
                    @php
                        // Always check for old input first (validation errors)
                        $headers = old('headers');
                        
                        // If no old input, get from provider
                        if (!$headers) {
                            if (is_string($provider->headers)) {
                                $headers = json_decode($provider->headers, true) ?: [];
                            } elseif (is_array($provider->headers)) {
                                $headers = $provider->headers;
                            } else {
                                $headers = [];
                            }
                        }
                        
                        // Ensure headers is array
                        if (!is_array($headers)) {
                            $headers = [];
                        }
                        
                        // Debug: log what we got
                        logger('Blade template headers: ' . json_encode($headers));
                    @endphp
                    @if($headers)
                        @php $headerIndex = 0; @endphp
                        @foreach($headers as $key => $value)
                        <div class="header-item flex space-x-3">
                            <input type="text" name="headers[{{ $headerIndex }}][key]" value="{{ $key }}" 
                                   placeholder="Header name" 
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <input type="text" name="headers[{{ $headerIndex }}][value]" value="{{ $value }}" 
                                   placeholder="Header value" 
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <button type="button" onclick="removeHeader(this)" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg">
                                <i class="lucide lucide-trash-2 w-4 h-4"></i>
                            </button>
                        </div>
                        @php $headerIndex++; @endphp
                        @endforeach
                    @endif
                </div>
                </div>
            </div>

            <!-- หมวด 3: การกำหนดค่า API -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                        <i class="lucide lucide-settings w-5 h-5 mr-2 text-purple-600"></i>
                        3. การกำหนดค่า API
                    </h2>
                    <p class="text-gray-600 text-sm mt-1">ตั้งค่าเพิ่มเติมสำหรับ API Provider</p>
                </div>
                <div class="p-6">
                    @php
                        $config = $provider->config;
                        if (is_string($config)) {
                            $config = json_decode($config, true) ?: [];
                        }
                        $config = is_array($config) ? $config : [];
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="wholesale_id" class="block text-sm font-medium text-gray-700 mb-2">Wholesale ID</label>
                        <input type="number" id="wholesale_id" name="config[wholesale_id]" 
                               value="{{ old('config.wholesale_id', $config['wholesale_id'] ?? '') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="group_id" class="block text-sm font-medium text-gray-700 mb-2">Group ID</label>
                        <input type="number" id="group_id" name="config[group_id]" 
                               value="{{ old('config.group_id', $config['group_id'] ?? '') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="image_width" class="block text-sm font-medium text-gray-700 mb-2">Image Resize Width</label>
                        <input type="number" id="image_width" name="config[image_resize][width]" 
                               value="{{ old('config.image_resize.width', ($config['image_resize']['width'] ?? 600)) }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="image_height" class="block text-sm font-medium text-gray-700 mb-2">Image Resize Height</label>
                        <input type="number" id="image_height" name="config[image_resize][height]" 
                               value="{{ old('config.image_resize.height', ($config['image_resize']['height'] ?? 600)) }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="md:col-span-2">
                        <label for="allowed_image_ext" class="block text-sm font-medium text-gray-700 mb-2">Allowed Image Extensions</label>
                        <input type="text" id="allowed_image_ext" name="config[allowed_image_ext_string]" 
                               value="{{ old('config.allowed_image_ext_string', implode(', ', ($config['allowed_image_ext'] ?? ['png', 'jpeg', 'jpg', 'webp']))) }}" 
                               placeholder="png, jpeg, jpg, webp" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Separate extensions with commas</p>
                    </div>

                    <div>
                        <label for="image_check_change" class="block text-sm font-medium text-gray-700 mb-2">Image Check Change Hours</label>
                        <input type="number" id="image_check_change" name="config[image_check_change]" 
                               value="{{ old('config.image_check_change', ($config['image_check_change'] ?? 2)) }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="country_filter" class="block text-sm font-medium text-gray-700 mb-2">Country Filter</label>
                        <input type="text" id="country_filter" name="config[country_filter]" 
                               value="{{ old('config.country_filter', ($config['country_filter'] ?? '')) }}" 
                               placeholder="e.g., Japan, Thailand" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    </div>
                </div>
            </div>

            <!-- หมวด 4: Field Mappings -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                            <i class="lucide lucide-shuffle w-5 h-5 mr-2 text-orange-600"></i>
                            4. Field Mappings
                        </h2>
                        <p class="text-gray-600 text-sm mt-1">กำหนดการแปลงข้อมูลจาก API เป็นฟิลด์ในฐานข้อมูล</p>
                    </div>
                </div>
                <div class="p-6">
                    <!-- Provider Configuration Info -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <h4 class="text-sm font-semibold text-yellow-800 mb-2">
                            <i class="lucide lucide-info w-4 h-4 inline mr-1"></i>
                            ข้อมูลจากการตั้งค่า Provider (ใช้ใน Tour ทุกรายการ)
                        </h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                            @php $config = is_string($provider->config) ? json_decode($provider->config, true) : $provider->config; @endphp
                            <div class="bg-white p-2 rounded border">
                                <span class="text-gray-600">Wholesale ID:</span>
                                <span class="font-semibold">{{ $config['wholesale_id'] ?? 'ไม่ได้ตั้งค่า' }}</span>
                            </div>
                            <div class="bg-white p-2 rounded border">
                                <span class="text-gray-600">Group ID:</span>
                                <span class="font-semibold">{{ $config['group_id'] ?? 'ไม่ได้ตั้งค่า' }}</span>
                            </div>
                            <div class="bg-white p-2 rounded border">
                                <span class="text-gray-600">API Type:</span>
                                <span class="font-semibold">{{ $provider->code }}</span>
                            </div>
                            <div class="bg-white p-2 rounded border">
                                <span class="text-gray-600">Data Type:</span>
                                <span class="font-semibold">package</span>
                            </div>
                        </div>
                    </div>

                    <!-- ส่วน Tour Fields -->
                    <div class="mb-8">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="text-lg font-semibold text-blue-900 flex items-center">
                                        <i class="lucide lucide-database w-5 h-5 mr-2"></i>
                                        Tour Fields Mapping
                                    </h3>
                                    <p class="text-blue-700 text-sm mt-1">
                                        แปลงข้อมูล Tour จาก API เป็นฟิลด์ในตาราง tb_tour<br>
                                        <span class="text-xs text-gray-600">
                                            <strong>API Field:</strong> ชื่อฟิลด์ใน API Response | 
                                            <strong>Static Value:</strong> ค่าคงที่ (api_type, data_type) | 
                                            <strong>หมายเหตุ:</strong> wholesale_id และ group_id ใช้ค่าจากการตั้งค่า Provider ด้านบน
                                        </span>
                                    </p>
                                </div>
                                <button type="button" onclick="addFieldMapping('tour')" class="btn btn-primary btn-sm">
                                    <i class="lucide lucide-plus w-4 h-4 mr-2"></i>
                                    เพิ่ม Tour Field
                                </button>
                            </div>
                        </div>

                        <!-- Tour Fields Table -->
                        <div class="overflow-x-auto">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th class="whitespace-nowrap">Local Field</th>
                                        <th class="whitespace-nowrap">API Field / Static Value</th>
                                        <th class="whitespace-nowrap">Data Type</th>
                                        <th class="whitespace-nowrap">Mapping Type</th>
                                        <th class="text-center whitespace-nowrap">Required</th>
                                        <th class="text-center whitespace-nowrap">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="tour-mappings-container">
                                    @php $tourMappingIndex = 0; @endphp
                                    @foreach(($provider->fieldMappings ?? collect())->where('field_type', 'tour') as $mapping)
                                    <tr class="field-mapping-item">
                                        <input type="hidden" name="field_mappings[{{ $tourMappingIndex }}][id]" value="{{ $mapping->id }}">
                                        <input type="hidden" name="field_mappings[{{ $tourMappingIndex }}][field_type]" value="tour">
                                        
                                        <td>
                                            <select name="field_mappings[{{ $tourMappingIndex }}][local_field]" class="form-select w-full" required>
                                                <option value="">Select field</option>
                                                @foreach(['api_id', 'code1', 'name', 'description', 'rating', 'num_day', 'image', 'pdf_file', 'country_name', 'airline_code', 'api_type', 'data_type', 'country_id', 'airline_id'] as $field)
                                                <option value="{{ $field }}" {{ $mapping->local_field == $field ? 'selected' : '' }}>{{ $field }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            @if(empty($mapping->api_field))
                                                @php
                                                    $transformationRules = is_string($mapping->transformation_rules) ? json_decode($mapping->transformation_rules, true) : $mapping->transformation_rules;
                                                    $staticValue = $transformationRules['static_value'] ?? '';
                                                @endphp
                                                <div class="flex flex-col space-y-2">
                                                    <div class="flex items-center space-x-2">
                                                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">Static Value</span>
                                                        <button type="button" onclick="toggleStaticValueEdit({{ $tourMappingIndex }})" class="text-xs text-blue-600 hover:text-blue-800">
                                                            <i class="lucide lucide-edit w-3 h-3"></i> แก้ไข
                                                        </button>
                                                    </div>
                                                    <div id="static-display-{{ $tourMappingIndex }}" class="font-mono text-sm text-green-700">{{ $staticValue }}</div>
                                                    <div id="static-edit-{{ $tourMappingIndex }}" class="hidden">
                                                        <input type="text" name="field_mappings[{{ $tourMappingIndex }}][static_value]" 
                                                               value="{{ $staticValue }}" class="form-control text-sm" 
                                                               placeholder="ใส่ค่า Static Value">
                                                        <div class="text-xs text-gray-500 mt-1">ตัวอย่าง: zego, package, JAPAN</div>
                                                    </div>
                                                    <input type="hidden" name="field_mappings[{{ $tourMappingIndex }}][api_field]" value="">
                                                </div>
                                            @else
                                                <input type="text" name="field_mappings[{{ $tourMappingIndex }}][api_field]" value="{{ $mapping->api_field }}" 
                                                       class="form-control">
                                            @endif
                                        </td>
                                        <td>
                                            <select name="field_mappings[{{ $tourMappingIndex }}][data_type]" class="form-select" required>
                                                @foreach(['string', 'integer', 'decimal', 'boolean', 'date', 'datetime', 'json'] as $type)
                                                <option value="{{ $type }}" {{ $mapping->data_type == $type ? 'selected' : '' }}>{{ $type }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            @if(empty($mapping->api_field))
                                                <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded">Static</span>
                                            @else
                                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">API Field</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <input type="hidden" name="field_mappings[{{ $tourMappingIndex }}][is_required]" value="0">
                                            <input type="checkbox" name="field_mappings[{{ $tourMappingIndex }}][is_required]" value="1" {{ $mapping->is_required ? 'checked' : '' }} 
                                                   class="form-check-input">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" onclick="removeFieldMapping(this)" class="btn btn-danger btn-sm">
                                                <i class="lucide lucide-trash-2 w-4 h-4"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @php $tourMappingIndex++; @endphp
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Period Fields -->
                <div>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-semibold text-green-900 flex items-center">
                                    <i class="lucide lucide-calendar w-5 h-5 mr-2"></i>
                                    Period Fields Mapping
                                </h3>
                                <p class="text-green-700 text-sm mt-1">
                                    แปลงข้อมูล Period จาก API เป็นฟิลด์ในตาราง tb_period<br>
                                    <span class="text-xs text-gray-600">
                                        <strong>API Field:</strong> ชื่อฟิลด์ใน API Response | 
                                        <strong>Static Value:</strong> ค่าคงที่ที่กำหนดไว้ล่วงหน้า
                                    </span>
                                </p>
                            </div>
                            <button type="button" onclick="addFieldMapping('period')" class="btn btn-success btn-sm">
                                <i class="lucide lucide-plus w-4 h-4 mr-2"></i>
                                เพิ่ม Period Field
                            </button>
                        </div>
                    </div>

                    <!-- Period Fields Table -->
                    <div class="overflow-x-auto">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap">Local Field</th>
                                    <th class="whitespace-nowrap">API Field / Static Value</th>
                                    <th class="whitespace-nowrap">Data Type</th>
                                    <th class="whitespace-nowrap">Mapping Type</th>
                                    <th class="text-center whitespace-nowrap">Required</th>
                                    <th class="text-center whitespace-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody id="period-mappings-container">
                                @php $periodMappingIndex = $tourMappingIndex; @endphp
                                @foreach(($provider->fieldMappings ?? collect())->where('field_type', 'period') as $mapping)
                                <tr class="field-mapping-item">
                                    <input type="hidden" name="field_mappings[{{ $periodMappingIndex }}][id]" value="{{ $mapping->id }}">
                                    <input type="hidden" name="field_mappings[{{ $periodMappingIndex }}][field_type]" value="period">
                                    
                                    <td>
                                        <select name="field_mappings[{{ $periodMappingIndex }}][local_field]" class="form-select w-full" required>
                                            <option value="">Select fields</option>
                                            @foreach([
                                                'tour_id', 'period_api_id', 'period_code', 'group_date', 'start_date', 'end_date', 
                                                'price1', 'special_price1', 'old_price1', 'price2', 'special_price2', 'old_price2',
                                                'price3', 'special_price3', 'old_price3', 'price4', 'special_price4', 'old_price4',
                                                'day', 'night', 'group', 'count', 'promotion_id', 'pro_start_date', 'pro_end_date',
                                                'status_display', 'status_period', 'api_type'
                                            ] as $field)
                                            <option value="{{ $field }}" {{ $mapping->local_field == $field ? 'selected' : '' }}>{{ $field }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        @if(empty($mapping->api_field))
                                            @php
                                                $transformationRules = is_string($mapping->transformation_rules) ? json_decode($mapping->transformation_rules, true) : $mapping->transformation_rules;
                                                $staticValue = $transformationRules['static_value'] ?? '';
                                            @endphp
                                            <div class="flex flex-col space-y-2">
                                                <div class="flex items-center space-x-2">
                                                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">Static Value</span>
                                                    <button type="button" onclick="toggleStaticValueEdit({{ $periodMappingIndex }})" class="text-xs text-blue-600 hover:text-blue-800">
                                                        <i class="lucide lucide-edit w-3 h-3"></i> แก้ไข
                                                    </button>
                                                </div>
                                                <div id="static-display-{{ $periodMappingIndex }}" class="font-mono text-sm text-green-700">{{ $staticValue }}</div>
                                                <div id="static-edit-{{ $periodMappingIndex }}" class="hidden">
                                                    <input type="text" name="field_mappings[{{ $periodMappingIndex }}][static_value]" 
                                                           value="{{ $staticValue }}" class="form-control text-sm" 
                                                           placeholder="ใส่ค่า Static Value">
                                                    <div class="text-xs text-gray-500 mt-1">ตัวอย่าง: package, available</div>
                                                </div>
                                                <input type="hidden" name="field_mappings[{{ $periodMappingIndex }}][api_field]" value="">
                                            </div>
                                        @else
                                            <input type="text" name="field_mappings[{{ $periodMappingIndex }}][api_field]" value="{{ $mapping->api_field }}" 
                                                   class="form-control">
                                        @endif
                                    </td>
                                    <td>
                                        <select name="field_mappings[{{ $periodMappingIndex }}][data_type]" class="form-select" required>
                                            @foreach(['string', 'integer', 'decimal', 'boolean', 'date', 'datetime', 'json'] as $type)
                                            <option value="{{ $type }}" {{ $mapping->data_type == $type ? 'selected' : '' }}>{{ $type }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        @if(empty($mapping->api_field))
                                            <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded">Static</span>
                                        @else
                                            <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">API Field</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <input type="hidden" name="field_mappings[{{ $periodMappingIndex }}][is_required]" value="0">
                                        <input type="checkbox" name="field_mappings[{{ $periodMappingIndex }}][is_required]" value="1" {{ $mapping->is_required ? 'checked' : '' }} 
                                               class="form-check-input">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" onclick="removeFieldMapping(this)" class="btn btn-danger btn-sm">
                                            <i class="lucide lucide-trash-2 w-4 h-4"></i>
                                        </button>
                                    </td>
                                </tr>
                                @php $periodMappingIndex++; @endphp
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- หมวด 5: กฎการจัดการโปรโมชั่น -->
            <div class="intro-y box p-5 mt-5">
                <div class="border border-slate-200/60 dark:border-darkmode-400 rounded-md p-5">
                    <div class="font-medium text-base text-slate-800 dark:text-slate-500 mb-3">
                        <i class="lucide lucide-percent w-4 h-4 mr-2"></i>
                        Promotion Rules Management
                    </div>
                    <p class="text-slate-500 mb-5">กำหนดกฎการจัดการโปรโมชั่นตามเงื่อนไขต่างๆ</p>
                    
                    <div class="flex flex-wrap items-center col-span-12 sm:flex-nowrap pb-4">
                        <button type="button" onclick="addPromotionRule()" class="btn btn-primary w-24 mr-2 mb-2 sm:mb-0">
                            <i class="lucide lucide-plus w-4 h-4 mr-2"></i>
                            เพิ่มกฎ
                        </button>
                    </div>
                    
                    <div class="overflow-auto lg:overflow-visible">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap">Rule Name</th>
                                    <th class="whitespace-nowrap">Condition Field</th>
                                    <th class="whitespace-nowrap">Operator</th>
                                    <th class="whitespace-nowrap">Value</th>
                                    <th class="whitespace-nowrap">Promotion Type</th>
                                    <th class="whitespace-nowrap">Promotion1</th>
                                    <th class="whitespace-nowrap">Promotion2</th>
                                    <th class="whitespace-nowrap">Priority</th>
                                    <th class="text-center whitespace-nowrap">Active</th>
                                    <th class="text-center whitespace-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody id="promotion-rules-container">
                                @php $promotionRuleIndex = 0; @endphp
                                @foreach($provider->promotionRules ?? [] as $rule)
                                <tr class="promotion-rule-item">
                                    <input type="hidden" name="promotion_rules[{{ $promotionRuleIndex }}][id]" value="{{ $rule->id }}">
                                    
                                    <td>
                                        <input type="text" name="promotion_rules[{{ $promotionRuleIndex }}][rule_name]" 
                                               value="{{ $rule->rule_name }}" 
                                               class="form-control" 
                                               placeholder="Fire Sale Rule" required>
                                    </td>
                                    <td>
                                        <select name="promotion_rules[{{ $promotionRuleIndex }}][condition_field]" class="form-select" required>
                                            <option value="">Select field</option>
                                            <option value="discount_percentage" {{ $rule->condition_field == 'discount_percentage' ? 'selected' : '' }}>Discount %</option>
                                            <option value="special_price1" {{ $rule->condition_field == 'special_price1' ? 'selected' : '' }}>Special Price</option>
                                            <option value="promotion_price" {{ $rule->condition_field == 'promotion_price' ? 'selected' : '' }}>Promotion Price</option>
                                            <option value="net_price" {{ $rule->condition_field == 'net_price' ? 'selected' : '' }}>Net Price</option>
                                            <option value="original_price" {{ $rule->condition_field == 'original_price' ? 'selected' : '' }}>Original Price</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="promotion_rules[{{ $promotionRuleIndex }}][condition_operator]" class="form-select" required>
                                            @foreach(['>=', '>', '<=', '<', '=', '!='] as $operator)
                                            <option value="{{ $operator }}" {{ $rule->condition_operator == $operator ? 'selected' : '' }}>{{ $operator }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="promotion_rules[{{ $promotionRuleIndex }}][condition_value]" 
                                               value="{{ $rule->condition_value }}" 
                                               class="form-control" 
                                               placeholder="30.00" required>
                                    </td>
                                    <td>
                                        <select name="promotion_rules[{{ $promotionRuleIndex }}][promotion_type]" class="form-select" required>
                                            <option value="fire_sale" {{ $rule->promotion_type == 'fire_sale' ? 'selected' : '' }}>Fire Sale</option>
                                            <option value="normal" {{ $rule->promotion_type == 'normal' ? 'selected' : '' }}>Normal Promo</option>
                                            <option value="none" {{ $rule->promotion_type == 'none' ? 'selected' : '' }}>No Promotion</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="promotion_rules[{{ $promotionRuleIndex }}][promotion1_value]" class="form-select" required>
                                            <option value="Y" {{ $rule->promotion1_value == 'Y' ? 'selected' : '' }}>Y</option>
                                            <option value="N" {{ $rule->promotion1_value == 'N' ? 'selected' : '' }}>N</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="promotion_rules[{{ $promotionRuleIndex }}][promotion2_value]" class="form-select" required>
                                            <option value="Y" {{ $rule->promotion2_value == 'Y' ? 'selected' : '' }}>Y</option>
                                            <option value="N" {{ $rule->promotion2_value == 'N' ? 'selected' : '' }}>N</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="promotion_rules[{{ $promotionRuleIndex }}][priority]" 
                                               value="{{ $rule->priority }}" 
                                               class="form-control" 
                                               min="1" required>
                                    </td>
                                    <td class="text-center">
                                        <input type="hidden" name="promotion_rules[{{ $promotionRuleIndex }}][is_active]" value="0">
                                        <input type="checkbox" name="promotion_rules[{{ $promotionRuleIndex }}][is_active]" value="1" {{ $rule->is_active ? 'checked' : '' }} 
                                               class="form-check-input">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" onclick="removePromotionRule(this)" class="btn btn-danger btn-sm">
                                            <i class="lucide lucide-trash-2 w-4 h-4"></i>
                                        </button>
                                    </td>
                                </tr>
                                @php $promotionRuleIndex++; @endphp
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- หมวด 6: เงื่อนไขการกรองข้อมูล -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                                <i class="lucide lucide-filter w-5 h-5 mr-2 text-red-600"></i>
                                5. เงื่อนไขการกรองข้อมูล
                            </h2>
                            <p class="text-gray-600 text-sm mt-1">กำหนดเงื่อนไขสำหรับการกรองข้อมูลจาก API</p>
                        </div>
                        <button type="button" onclick="addCondition()" class="btn btn-primary">
                            <i class="lucide lucide-plus w-4 h-4 mr-1"></i>
                            เพิ่ม Condition
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <!-- Conditions Table -->
                    <div class="overflow-x-auto">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap">Condition Type</th>
                                    <th class="whitespace-nowrap">Field Name</th>
                                    <th class="whitespace-nowrap">Operator</th>
                                    <th class="whitespace-nowrap">Value</th>
                                    <th class="whitespace-nowrap">Action Type</th>
                                    <th class="whitespace-nowrap">Priority</th>
                                    <th class="text-center whitespace-nowrap">Active</th>
                                    <th class="text-center whitespace-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody id="conditions-container">
                                @php $conditionIndex = 0; @endphp
                                @foreach($provider->conditions ?? [] as $condition)
                                <tr class="condition-item">
                                    <input type="hidden" name="conditions[{{ $conditionIndex }}][id]" value="{{ $condition->id }}">
                                    
                                    <td>
                                        <select name="conditions[{{ $conditionIndex }}][condition_type]" class="form-select" required>
                                            <option value="">Select Type</option>
                                            <option value="country_mapping" {{ ($condition->condition_type ?? '') == 'country_mapping' ? 'selected' : '' }}>Country Mapping</option>
                                            <option value="airline_mapping" {{ ($condition->condition_type ?? '') == 'airline_mapping' ? 'selected' : '' }}>Airline Mapping</option>
                                            <option value="image_processing" {{ ($condition->condition_type ?? '') == 'image_processing' ? 'selected' : '' }}>Image Processing</option>
                                            <option value="pdf_processing" {{ ($condition->condition_type ?? '') == 'pdf_processing' ? 'selected' : '' }}>PDF Processing</option>
                                            <option value="pdf_link_storage" {{ ($condition->condition_type ?? '') == 'pdf_link_storage' ? 'selected' : '' }}>PDF Link Storage</option>
                                            <option value="price_calculation" {{ ($condition->condition_type ?? '') == 'price_calculation' ? 'selected' : '' }}>Price Calculation</option>
                                            <option value="price_group_assignment" {{ ($condition->condition_type ?? '') == 'price_group_assignment' ? 'selected' : '' }}>Price Group Assignment</option>
                                            <option value="period_status_assignment" {{ ($condition->condition_type ?? '') == 'period_status_assignment' ? 'selected' : '' }}>Period Status Assignment</option>
                                            <option value="period_status_mapping" {{ ($condition->condition_type ?? '') == 'period_status_mapping' ? 'selected' : '' }}>Period Status Mapping</option>
                                            <option value="availability_status_mapping" {{ ($condition->condition_type ?? '') == 'availability_status_mapping' ? 'selected' : '' }}>Availability Status Mapping</option>
                                            <option value="content_length_validation" {{ ($condition->condition_type ?? '') == 'content_length_validation' ? 'selected' : '' }}>Content Length Validation</option>
                                            <option value="rate_limiting_handler" {{ ($condition->condition_type ?? '') == 'rate_limiting_handler' ? 'selected' : '' }}>Rate Limiting Handler</option>
                                            <option value="nested_api_calls" {{ ($condition->condition_type ?? '') == 'nested_api_calls' ? 'selected' : '' }}>Nested API Calls</option>
                                            <option value="fixed_value_assignment" {{ ($condition->condition_type ?? '') == 'fixed_value_assignment' ? 'selected' : '' }}>Fixed Value Assignment</option>
                                            <option value="data_update_check" {{ ($condition->condition_type ?? '') == 'data_update_check' ? 'selected' : '' }}>Data Update Check</option>
                                            <option value="field_transformation" {{ ($condition->condition_type ?? '') == 'field_transformation' ? 'selected' : '' }}>Field Transformation</option>
                                            <option value="data_validation" {{ ($condition->condition_type ?? '') == 'data_validation' ? 'selected' : '' }}>Data Validation</option>
                                            <option value="text_processing" {{ ($condition->condition_type ?? '') == 'text_processing' ? 'selected' : '' }}>Text Processing</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="conditions[{{ $conditionIndex }}][field_name]" value="{{ $condition->field_name }}" 
                                               class="form-control" placeholder="CountryName, AirlineCode, URLImage, etc." required>
                                    </td>
                                    <td>
                                        <select name="conditions[{{ $conditionIndex }}][operator]" class="form-select" required>
                                            @foreach(['EXISTS' => 'มีค่า', 'NOT EXISTS' => 'ไม่มีค่า', '=' => 'เท่ากับ', '!=' => 'ไม่เท่ากับ', 'LIKE' => 'มีข้อความ', 'NOT LIKE' => 'ไม่มีข้อความ'] as $op => $label)
                                            <option value="{{ $op }}" {{ ($condition->operator ?? '') == $op ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="conditions[{{ $conditionIndex }}][value]" value="{{ $condition->value ?? '' }}" 
                                               class="form-control" placeholder="Thailand, TG, etc.">
                                    </td>
                                    <td>
                                        <select name="conditions[{{ $conditionIndex }}][action_type]" class="form-select" required>
                                            <option value="">Select Action</option>
                                            <option value="lookup_database" {{ ($condition->action_type ?? '') == 'lookup_database' ? 'selected' : '' }}>ค้นหาจากฐานข้อมูล</option>
                                            <option value="download_image" {{ ($condition->action_type ?? '') == 'download_image' ? 'selected' : '' }}>ดาวน์โหลดรูปภาพ</option>
                                            <option value="download_file" {{ ($condition->action_type ?? '') == 'download_file' ? 'selected' : '' }}>ดาวน์โหลดไฟล์</option>
                                            <option value="store_link" {{ ($condition->action_type ?? '') == 'store_link' ? 'selected' : '' }}>เก็บเป็นลิงก์</option>
                                            <option value="set_value" {{ ($condition->action_type ?? '') == 'set_value' ? 'selected' : '' }}>กำหนดค่า</option>
                                            <option value="assign_fixed_value" {{ ($condition->action_type ?? '') == 'assign_fixed_value' ? 'selected' : '' }}>กำหนดค่าคงที่</option>
                                            <option value="transform_value" {{ ($condition->action_type ?? '') == 'transform_value' ? 'selected' : '' }}>แปลงค่า</option>
                                            <option value="calculate_percentage" {{ ($condition->action_type ?? '') == 'calculate_percentage' ? 'selected' : '' }}>คำนวณเปอร์เซ็นต์</option>
                                            <option value="map_status_value" {{ ($condition->action_type ?? '') == 'map_status_value' ? 'selected' : '' }}>แปลงสถานะ</option>
                                            <option value="validate_content" {{ ($condition->action_type ?? '') == 'validate_content' ? 'selected' : '' }}>ตรวจสอบเนื้อหา</option>
                                            <option value="handle_rate_limit" {{ ($condition->action_type ?? '') == 'handle_rate_limit' ? 'selected' : '' }}>จัดการ Rate Limit</option>
                                            <option value="skip_record" {{ ($condition->action_type ?? '') == 'skip_record' ? 'selected' : '' }}>ข้ามการบันทึก</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="conditions[{{ $conditionIndex }}][priority]" value="{{ $condition->priority ?? 1 }}" 
                                               class="form-control" min="1" max="100">
                                    </td>
                                    <td class="text-center">
                                        <input type="hidden" name="conditions[{{ $conditionIndex }}][is_active]" value="0">
                                        <input type="checkbox" name="conditions[{{ $conditionIndex }}][is_active]" value="1" {{ $condition->is_active ? 'checked' : '' }} 
                                               class="form-check-input">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" onclick="removeCondition(this)" class="btn btn-danger btn-sm">
                                            <i class="lucide lucide-trash-2 w-4 h-4"></i>
                                        </button>
                                    </td>
                                </tr>
                                @php $conditionIndex++; @endphp
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- หมวด 7: การกำหนดเวลาดึงข้อมูลอัตโนมัติ -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                                <i class="lucide lucide-clock w-5 h-5 mr-2 text-orange-600"></i>
                                7. การกำหนดเวลาดึงข้อมูลอัตโนมัติ
                            </h2>
                            <p class="text-gray-600 text-sm mt-1">ตั้งค่าการซิงค์ข้อมูลอัตโนมัติตามเวลาที่กำหนด</p>
                        </div>
                        <button type="button" onclick="addSchedule()" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                            <i class="lucide lucide-plus w-4 h-4 mr-2"></i>
                            เพิ่มตารางเวลา
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    
                    @if($provider->schedules && $provider->schedules->count() > 0)
                        <div class="space-y-4">
                            @foreach($provider->schedules as $schedule)
                            <div class="border border-gray-200 rounded-lg p-4 schedule-item">
                                <div class="flex justify-between items-start mb-4">
                                    <h4 class="text-lg font-medium text-gray-900">{{ $schedule->name }}</h4>
                                    <div class="flex space-x-2">
                                        <button type="button" onclick="editSchedule({{ $schedule->id }})" class="text-blue-600 hover:text-blue-800 text-sm">
                                            <i class="lucide lucide-edit w-4 h-4"></i> แก้ไข
                                        </button>
                                        <button type="button" onclick="deleteSchedule({{ $schedule->id }})" class="text-red-600 hover:text-red-800 text-sm">
                                            <i class="lucide lucide-trash-2 w-4 h-4"></i> ลบ
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">ความถี่</label>
                                        <p class="text-gray-900">{{ $schedule->frequency_text }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">คำอธิบาย</label>
                                        <p class="text-gray-900">{{ $schedule->schedule_description }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">สถานะ</label>
                                        <div class="flex items-center space-x-2">
                                            {!! $schedule->status_badge !!}
                                            <span class="text-sm text-gray-600">
                                                @if($schedule->next_run_at)
                                                    รันต่อไป: {{ $schedule->next_run_at->format('d/m/Y H:i') }}
                                                @else
                                                    ยังไม่กำหนดเวลา
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                @if($schedule->last_error)
                                <div class="mt-4 p-3 bg-red-50 border-l-4 border-red-400 rounded">
                                    <p class="text-sm text-red-700">
                                        <strong>Error ล่าสุด:</strong> {{ $schedule->last_error }}
                                    </p>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="lucide lucide-calendar-x w-16 h-16 text-gray-400 mx-auto mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">ยังไม่มีตารางเวลา</h3>
                            <p class="text-gray-500 mb-4">กำหนดตารางเวลาการดึงข้อมูลอัตโนมัติ</p>
                            <button type="button" onclick="addSchedule()" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="lucide lucide-plus w-4 h-4 mr-2"></i>
                                เพิ่มตารางเวลาแรก
                            </button>
                        </div>
                    @endif
                </div>
            </div>

                            <div class="text-right mt-5">
                                <button type="button" class="btn btn-outline-secondary w-24 mr-1">ยกเลิก</button>
                                <button type="submit" onclick="debugFormData()" class="btn btn-primary w-24">บันทึก</button>
                            </div>
            

        </form>
    </div>
</div>

<script>
@php
    $headersData = is_string($provider->headers) ? json_decode($provider->headers, true) : $provider->headers;
    $headersData = is_array($headersData) ? $headersData : [];
@endphp
let headerIndex = {{ count(old('headers', $headersData)) }};
let mappingIndex = {{ is_object($provider->fieldMappings) ? $provider->fieldMappings->count() : 0 }};
let conditionIndex = {{ is_object($provider->conditions) ? $provider->conditions->count() : 0 }};
let promotionRuleIndex = {{ is_object($provider->promotionRules) ? $provider->promotionRules->count() : 0 }};

// Header management
function addHeader() {
    const container = document.getElementById('headers-container');
    const div = document.createElement('div');
    div.className = 'header-item flex space-x-3';
    div.innerHTML = `
        <input type="text" name="headers[${headerIndex}][key]" placeholder="Header name" 
               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        <input type="text" name="headers[${headerIndex}][value]" placeholder="Header value" 
               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        <button type="button" onclick="removeHeader(this)" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg">
            <i class="lucide lucide-trash-2 w-4 h-4"></i>
        </button>
    `;
    container.appendChild(div);
    headerIndex++;
}

function removeHeader(button) {
    button.closest('.header-item').remove();
}

// Debug form data before submit
function debugFormData() {
    const form = document.getElementById('edit-provider-form');
    const formData = new FormData(form);
    
    console.log('=== FORM DEBUG START ===');
    
    // Check all form data
    console.log('All headers form entries:');
    for (let [key, value] of formData.entries()) {
        if (key.includes('headers')) {
            console.log(`FormData: ${key} = "${value}"`);
        }
    }
    
    // Also check headers inputs in DOM
    const headerInputs = document.querySelectorAll('input[name^="headers["]');
    console.log('Header inputs found in DOM:', headerInputs.length);
    headerInputs.forEach((input, index) => {
        console.log(`DOM Input ${index}: ${input.name} = "${input.value}" (type: ${input.type})`);
    });
    
    // Check headers container
    const container = document.getElementById('headers-container');
    console.log('Headers container children count:', container ? container.children.length : 'Container not found');
    
    if (container) {
        Array.from(container.children).forEach((child, index) => {
            const keyInput = child.querySelector('input[name$="[key]"]');
            const valueInput = child.querySelector('input[name$="[value]"]');
            console.log(`Container Item ${index}:`);
            console.log(`  Key: ${keyInput ? keyInput.value : 'No key input'}`);
            console.log(`  Value: ${valueInput ? valueInput.value : 'No value input'}`);
        });
    }
    
    console.log('=== FORM DEBUG END ===');
    return true; // Allow form to submit
}

// Field mapping management
function addFieldMapping(type) {
    const container = document.getElementById(type + '-mappings-container');
    const row = document.createElement('tr');
    row.className = 'field-mapping-item';
    
    const localFields = type === 'tour' 
        ? ['api_id', 'code1', 'name', 'description', 'rating', 'num_day', 'image', 'pdf_file', 'country_name', 'airline_code', 'api_type', 'data_type', 'country_id', 'airline_id']
        : ['tour_id', 'period_api_id', 'period_code', 'group_date', 'start_date', 'end_date', 'price1', 'special_price1', 'old_price1', 'price2', 'special_price2', 'old_price2', 'price3', 'special_price3', 'old_price3', 'price4', 'special_price4', 'old_price4', 'day', 'night', 'group', 'count', 'promotion_id', 'pro_start_date', 'pro_end_date', 'status_display', 'status_period', 'api_type'];
        
    const localFieldOptions = localFields.map(field => `<option value="${field}">${field}</option>`).join('');
    const dataTypeOptions = ['string', 'integer', 'decimal', 'boolean', 'date', 'datetime', 'json']
        .map(dataType => `<option value="${dataType}">${dataType}</option>`).join('');

    row.innerHTML = `
        <input type="hidden" name="field_mappings[${mappingIndex}][field_type]" value="${type}">
        <td>
            <select name="field_mappings[${mappingIndex}][local_field]" class="form-select w-full" required>
                <option value="">Select field</option>
                ${localFieldOptions}
            </select>
        </td>
        <td>
            <div class="flex space-x-2">
                <input type="text" name="field_mappings[${mappingIndex}][api_field]" class="form-control flex-1" placeholder="API field name">
                <input type="text" name="field_mappings[${mappingIndex}][static_value]" class="form-control flex-1" placeholder="หรือใส่ค่า Static Value">
            </div>
            <div class="text-xs text-gray-500 mt-1">ใส่ API field หรือ Static value (เลือกอันใดอันหนึ่ง)</div>
        </td>
        <td>
            <select name="field_mappings[${mappingIndex}][data_type]" class="form-select" required>
                ${dataTypeOptions}
            </select>
        </td>
        <td>
            <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">New Field</span>
        </td>
        <td class="text-center">
            <input type="hidden" name="field_mappings[${mappingIndex}][is_required]" value="0">
            <input type="checkbox" name="field_mappings[${mappingIndex}][is_required]" value="1" class="form-check-input">
        </td>
        <td class="text-center">
            <button type="button" onclick="removeFieldMapping(this)" class="btn btn-danger btn-sm">
                <i class="lucide lucide-trash-2 w-4 h-4"></i>
            </button>
        </td>
    `;
    container.appendChild(row);
    mappingIndex++;
}

function removeFieldMapping(button) {
    button.closest('.field-mapping-item').remove();
}

// Condition management
function addCondition() {
    const container = document.getElementById('conditions-container');
    const row = document.createElement('tr');
    row.className = 'condition-item';

    row.innerHTML = `
        <td>
            <select name="conditions[${conditionIndex}][condition_type]" class="form-select" required>
                <option value="">Select Type</option>
                <option value="country_mapping">Country Mapping</option>
                <option value="airline_mapping">Airline Mapping</option>
                <option value="image_processing">Image Processing</option>
                <option value="pdf_processing">PDF Processing</option>
                <option value="pdf_link_storage">PDF Link Storage</option>
                <option value="price_calculation">Price Calculation</option>
                <option value="price_group_assignment">Price Group Assignment</option>
                <option value="period_status_assignment">Period Status Assignment</option>
                <option value="period_status_mapping">Period Status Mapping</option>
                <option value="availability_status_mapping">Availability Status Mapping</option>
                <option value="content_length_validation">Content Length Validation</option>
                <option value="rate_limiting_handler">Rate Limiting Handler</option>
                <option value="nested_api_calls">Nested API Calls</option>
                <option value="fixed_value_assignment">Fixed Value Assignment</option>
                <option value="data_update_check">Data Update Check</option>
                <option value="field_transformation">Field Transformation</option>
                <option value="data_validation">Data Validation</option>
                <option value="text_processing">Text Processing</option>
            </select>
        </td>
        <td>
            <input type="text" name="conditions[${conditionIndex}][field_name]" 
                   class="form-control" 
                   placeholder="CountryName, AirlineCode, URLImage" required>
        </td>
        <td>
            <select name="conditions[${conditionIndex}][operator]" class="form-select" required>
                <option value="EXISTS">มีค่า</option>
                <option value="NOT EXISTS">ไม่มีค่า</option>
                <option value="=">=เท่ากับ</option>
                <option value="!=">ไม่เท่ากับ</option>
                <option value="LIKE">มีข้อความ</option>
                <option value="NOT LIKE">ไม่มีข้อความ</option>
            </select>
        </td>
        <td>
            <input type="text" name="conditions[${conditionIndex}][value]" 
                   class="form-control" 
                   placeholder="Thailand, TG, etc.">
        </td>
        <td>
            <select name="conditions[${conditionIndex}][action_type]" class="form-select" required>
                <option value="">Select Action</option>
                <option value="lookup_database">ค้นหาจากฐานข้อมูล</option>
                <option value="download_image">ดาวน์โหลดรูปภาพ</option>
                <option value="download_file">ดาวน์โหลดไฟล์</option>
                <option value="store_link">เก็บเป็นลิงก์</option>
                <option value="set_value">กำหนดค่า</option>
                <option value="assign_fixed_value">กำหนดค่าคงที่</option>
                <option value="transform_value">แปลงค่า</option>
                <option value="calculate_percentage">คำนวณเปอร์เซ็นต์</option>
                <option value="map_status_value">แปลงสถานะ</option>
                <option value="validate_content">ตรวจสอบเนื้อหา</option>
                <option value="handle_rate_limit">จัดการ Rate Limit</option>
                <option value="skip_record">ข้ามการบันทึก</option>
            </select>
        </td>
        <td>
            <input type="number" name="conditions[${conditionIndex}][priority]" 
                   value="1" class="form-control" min="1" max="100">
        </td>
        <td class="text-center">
            <input type="hidden" name="conditions[${conditionIndex}][is_active]" value="0">
            <input type="checkbox" name="conditions[${conditionIndex}][is_active]" value="1" checked class="form-check-input">
        </td>
        <td class="text-center">
            <button type="button" onclick="removeCondition(this)" class="btn btn-danger btn-sm">
                <i class="lucide lucide-trash-2 w-4 h-4"></i>
            </button>
        </td>
    `;
    container.appendChild(row);
    conditionIndex++;
}

function removeCondition(button) {
    button.closest('.condition-item').remove();
}

// Promotion Rules management

function addPromotionRule() {
    const container = document.getElementById('promotion-rules-container');
    const row = document.createElement('tr');
    row.className = 'promotion-rule-item';
    row.innerHTML = `
        <td>
            <input type="text" name="promotion_rules[${promotionRuleIndex}][rule_name]" 
                   placeholder="Fire Sale Rule" 
                   class="form-control" required>
        </td>
        <td>
            <select name="promotion_rules[${promotionRuleIndex}][condition_field]" class="form-select" required>
                <option value="">Select field</option>
                <option value="discount_percentage">Discount %</option>
                <option value="special_price1">Special Price</option>
                <option value="promotion_price">Promotion Price</option>
                <option value="net_price">Net Price</option>
                <option value="original_price">Original Price</option>
            </select>
        </td>
        <td>
            <select name="promotion_rules[${promotionRuleIndex}][condition_operator]" class="form-select" required>
                <option value=">=">=</option>
                <option value=">">></option>
                <option value="<="<=</option>
                <option value="<"><</option>
                <option value="=">=</option>
                <option value="!=">!=</option>
            </select>
        </td>
        <td>
            <input type="number" step="0.01" name="promotion_rules[${promotionRuleIndex}][condition_value]" 
                   placeholder="30.00" 
                   class="form-control" required>
        </td>
        <td>
            <select name="promotion_rules[${promotionRuleIndex}][promotion_type]" class="form-select" required>
                <option value="fire_sale">Fire Sale</option>
                <option value="normal">Normal Promo</option>
                <option value="none">No Promotion</option>
            </select>
        </td>
        <td>
            <select name="promotion_rules[${promotionRuleIndex}][promotion1_value]" class="form-select" required>
                <option value="Y">Y</option>
                <option value="N" selected>N</option>
            </select>
        </td>
        <td>
            <select name="promotion_rules[${promotionRuleIndex}][promotion2_value]" class="form-select" required>
                <option value="Y">Y</option>
                <option value="N" selected>N</option>
            </select>
        </td>
        <td>
            <input type="number" name="promotion_rules[${promotionRuleIndex}][priority]" 
                   value="1" 
                   class="form-control" 
                   min="1" required>
        </td>
        <td class="text-center">
            <input type="hidden" name="promotion_rules[${promotionRuleIndex}][is_active]" value="0">
            <input type="checkbox" name="promotion_rules[${promotionRuleIndex}][is_active]" value="1" checked class="form-check-input">
        </td>
        <td class="text-center">
            <button type="button" onclick="removePromotionRule(this)" class="btn btn-danger btn-sm">
                <i class="lucide lucide-trash-2 w-4 h-4"></i>
            </button>
        </td>
    `;
    container.appendChild(row);
    promotionRuleIndex++;
}

function removePromotionRule(button) {
    button.closest('.promotion-rule-item').remove();
}

// Toggle การแก้ไข Static Value
function toggleStaticValueEdit(index) {
    const displayDiv = document.getElementById(`static-display-${index}`);
    const editDiv = document.getElementById(`static-edit-${index}`);
    
    if (displayDiv && editDiv) {
        if (editDiv.classList.contains('hidden')) {
            // แสดงโหมดแก้ไข
            displayDiv.classList.add('hidden');
            editDiv.classList.remove('hidden');
        } else {
            // แสดงโหมดการแสดงผล
            const input = editDiv.querySelector('input[name*="static_value"]');
            if (input) {
                displayDiv.textContent = input.value || 'ไม่ได้กำหนด';
            }
            displayDiv.classList.remove('hidden');
            editDiv.classList.add('hidden');
        }
    }
}

// อัพเดตข้อมูลแสดงผลเมื่อมีการเปลี่ยนแปลง
function updateStaticValueDisplay(index) {
    const input = document.querySelector(`input[name*="field_mappings[${index}][static_value]"]`);
    const display = document.getElementById(`static-display-${index}`);
    
    if (input && display) {
        display.textContent = input.value || 'ไม่ได้กำหนด';
    }
}
</script>

                        </form>
                    </div>
                    <!-- END: Form Layout -->
                </div>
            </div>
            <!-- END: Content -->
        </div>
        <!-- END: Content -->
    </div>
    
    <!-- Schedule Modal -->
    <div id="scheduleModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full m-4 max-h-[90vh] overflow-y-auto">
            <form id="scheduleForm" action="" method="POST">
                @csrf
                <input type="hidden" id="scheduleMethod" name="_method" value="POST">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 id="scheduleModalTitle" class="text-lg font-semibold text-gray-900">เพิ่มตารางเวลาใหม่</h3>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-4 space-y-6">
                    
                    <!-- ชื่อตารางเวลา -->
                    <div>
                        <label for="schedule_name" class="block text-sm font-medium text-gray-700 mb-2">ชื่อตารางเวลา *</label>
                        <input type="text" id="schedule_name" name="name" placeholder="เช่น: ซิงค์รายวัน, อัปเดตเช้า" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               required>
                    </div>

                    <!-- ความถี่ -->
                    <div>
                        <label for="schedule_frequency" class="block text-sm font-medium text-gray-700 mb-2">ความถี่ *</label>
                        <select id="schedule_frequency" name="frequency" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                onchange="toggleScheduleOptions()" required>
                            <option value="">เลือกความถี่</option>
                            <option value="hourly">ทุกชั่วโมง</option>
                            <option value="daily">ทุกวัน</option>
                            <option value="weekly">ทุกสัปดาห์</option>
                            <option value="monthly">ทุกเดือน</option>
                            <option value="custom">กำหนดเอง (Cron)</option>
                        </select>
                    </div>

                    <!-- Hourly Options -->
                    <div id="hourly_options" class="hidden">
                        <label for="interval_minutes" class="block text-sm font-medium text-gray-700 mb-2">ช่วงเวลา (นาที)</label>
                        <select id="interval_minutes" name="interval_minutes" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="15">ทุก 15 นาที</option>
                            <option value="30">ทุก 30 นาที</option>
                            <option value="60" selected>ทุก 1 ชั่วโมง</option>
                            <option value="120">ทุก 2 ชั่วโมง</option>
                            <option value="180">ทุก 3 ชั่วโมง</option>
                            <option value="360">ทุก 6 ชั่วโมง</option>
                        </select>
                    </div>

                    <!-- Daily/Weekly/Monthly Time -->
                    <div id="time_options" class="hidden">
                        <label for="run_time" class="block text-sm font-medium text-gray-700 mb-2">เวลาที่รัน</label>
                        <input type="time" id="run_time" name="run_time" value="09:00"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Weekly Options -->
                    <div id="weekly_options" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">วันที่รัน *</label>
                        <div class="grid grid-cols-7 gap-2">
                            @php $dayNames = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์']; @endphp
                            @foreach($dayNames as $index => $dayName)
                            <label class="flex items-center space-x-1">
                                <input type="checkbox" name="days_of_week[]" value="{{ $index }}" 
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-xs">{{ $dayName }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Monthly Options -->
                    <div id="monthly_options" class="hidden">
                        <label for="day_of_month" class="block text-sm font-medium text-gray-700 mb-2">วันที่ของเดือน</label>
                        <select id="day_of_month" name="day_of_month" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @for($i = 1; $i <= 31; $i++)
                            <option value="{{ $i }}" {{ $i == 1 ? 'selected' : '' }}>วันที่ {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <!-- Custom Cron Options -->
                    <div id="custom_options" class="hidden">
                        <label for="cron_expression" class="block text-sm font-medium text-gray-700 mb-2">Cron Expression</label>
                        <input type="text" id="cron_expression" name="cron_expression" 
                               placeholder="0 9 * * 1-5" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">รูปแบบ: minute hour day month day_of_week (เช่น: 0 9 * * 1-5 = 09:00 วันจันทร์-ศุกร์)</p>
                    </div>

                    <!-- ข้อมูลเพิ่มเติม -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="sync_limit" class="block text-sm font-medium text-gray-700 mb-2">จำกัดจำนวนข้อมูล</label>
                            <input type="number" id="sync_limit" name="sync_limit" 
                                   placeholder="เช่น: 100 (เว้นว่างถ้าไม่จำกัด)" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="is_active" class="block text-sm font-medium text-gray-700 mb-2">สถานะ</label>
                            <select id="is_active" name="is_active" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="1">🟢 เปิดใช้งาน</option>
                                <option value="0">🔴 ปิดใช้งาน</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeScheduleModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        ยกเลิก
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        บันทึก
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // เพิ่มในส่วนของตัวแปร JavaScript ที่มีอยู่
        let scheduleIndex = 0;

        // Scheduler Functions
        function addSchedule() {
            document.getElementById('scheduleModalTitle').textContent = 'เพิ่มตารางเวลาใหม่';
            document.getElementById('scheduleForm').action = `/webpanel/api-management/{{ $provider->id }}/schedules`;
            document.getElementById('scheduleMethod').value = 'POST';
            resetScheduleForm();
            document.getElementById('scheduleModal').classList.remove('hidden');
        }

        function editSchedule(scheduleId) {
            document.getElementById('scheduleModalTitle').textContent = 'แก้ไขตารางเวลา';
            document.getElementById('scheduleForm').action = `/webpanel/api-management/{{ $provider->id }}/schedules/${scheduleId}`;
            document.getElementById('scheduleMethod').value = 'PUT';
            
            // Load schedule data via AJAX
            fetch(`/webpanel/api-management/{{ $provider->id }}/schedules/${scheduleId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const schedule = data.schedule;
                        document.getElementById('schedule_name').value = schedule.name;
                        document.getElementById('schedule_frequency').value = schedule.frequency;
                        
                        if (schedule.run_time) {
                            document.getElementById('run_time').value = schedule.run_time.substring(0, 5);
                        }
                        if (schedule.interval_minutes) {
                            document.getElementById('interval_minutes').value = schedule.interval_minutes;
                        }
                        if (schedule.day_of_month) {
                            document.getElementById('day_of_month').value = schedule.day_of_month;
                        }
                        if (schedule.cron_expression) {
                            document.getElementById('cron_expression').value = schedule.cron_expression;
                        }
                        if (schedule.sync_limit) {
                            document.getElementById('sync_limit').value = schedule.sync_limit;
                        }
                        document.getElementById('is_active').value = schedule.is_active ? '1' : '0';
                        
                        // Set days of week for weekly schedule
                        if (schedule.days_of_week && schedule.days_of_week.length > 0) {
                            schedule.days_of_week.forEach(day => {
                                const checkbox = document.querySelector(`input[name="days_of_week[]"][value="${day}"]`);
                                if (checkbox) checkbox.checked = true;
                            });
                        }
                        
                        toggleScheduleOptions();
                    }
                })
                .catch(error => {
                    console.error('Error loading schedule:', error);
                    alert('เกิดข้อผิดพลาดในการโหลดข้อมูลตารางเวลา');
                });
            
            document.getElementById('scheduleModal').classList.remove('hidden');
        }

        function deleteSchedule(scheduleId) {
            if (confirm('คุณแน่ใจหรือไม่ว่าต้องการลบตารางเวลานี้?')) {
                fetch(`/webpanel/api-management/{{ $provider->id }}/schedules/${scheduleId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload(); // Reload to show updated schedule list
                    } else {
                        alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถลบตารางเวลาได้'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('เกิดข้อผิดพลาดในการลบตารางเวลา');
                });
            }
        }

        function closeScheduleModal() {
            document.getElementById('scheduleModal').classList.add('hidden');
            resetScheduleForm();
        }

        function resetScheduleForm() {
            document.getElementById('scheduleForm').reset();
            document.getElementById('schedule_frequency').value = '';
            document.querySelectorAll('input[name="days_of_week[]"]').forEach(cb => cb.checked = false);
            toggleScheduleOptions();
        }

        function toggleScheduleOptions() {
            const frequency = document.getElementById('schedule_frequency').value;
            
            // Hide all options first
            document.getElementById('hourly_options').classList.add('hidden');
            document.getElementById('time_options').classList.add('hidden');
            document.getElementById('weekly_options').classList.add('hidden');
            document.getElementById('monthly_options').classList.add('hidden');
            document.getElementById('custom_options').classList.add('hidden');
            
            // Show relevant options
            switch (frequency) {
                case 'hourly':
                    document.getElementById('hourly_options').classList.remove('hidden');
                    break;
                case 'daily':
                    document.getElementById('time_options').classList.remove('hidden');
                    break;
                case 'weekly':
                    document.getElementById('time_options').classList.remove('hidden');
                    document.getElementById('weekly_options').classList.remove('hidden');
                    break;
                case 'monthly':
                    document.getElementById('time_options').classList.remove('hidden');
                    document.getElementById('monthly_options').classList.remove('hidden');
                    break;
                case 'custom':
                    document.getElementById('custom_options').classList.remove('hidden');
                    break;
            }
        }

        // Handle schedule form submission
        document.getElementById('scheduleForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const url = this.action;
            const method = document.getElementById('scheduleMethod').value;
            
            fetch(url, {
                method: method,
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeScheduleModal();
                    location.reload(); // Reload to show updated schedule list
                } else {
                    alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถบันทึกตารางเวลาได้'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('เกิดข้อผิดพลาดในการบันทึกตารางเวลา');
            });
        });

        // Close modal when clicking outside
        document.getElementById('scheduleModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeScheduleModal();
            }
        });
        
        // Toggle multi-step API fields
        function toggleMultiStepFields() {
            const checkbox = document.getElementById('requires_multi_step');
            const fields = document.getElementById('multi-step-fields');
            
            if (checkbox.checked) {
                fields.classList.remove('hidden');
            } else {
                fields.classList.add('hidden');
                // Clear values when disabled
                fields.querySelectorAll('input').forEach(input => {
                    if (input.type === 'text') {
                        input.value = '';
                    }
                });
            }
        }
    </script>

    <!-- BEGIN: JS Assets-->
    @include("backend.layout.script")
    <!-- END: JS Assets-->
</body>
</html>