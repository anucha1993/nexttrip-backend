<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Backend\ApiProviderModel;
use App\Models\Backend\ApiFieldMappingModel;

return new class extends Migration
{
    public function up()
    {
        echo "🚀 Adding Super Holiday API field mappings...\n";
        
        $superholiday = ApiProviderModel::where('code', 'superbholiday')->first();
        
        if (!$superholiday) {
            echo "❌ Super Holiday provider not found!\n";
            return;
        }
        
        // Tour field mappings from hardcode
        $tourMappings = [
            [
                'field_type' => 'tour',
                'local_field' => 'api_id',
                'api_field' => 'mainid',
                'data_type' => 'string',
                'transformation_rules' => []
            ],
            [
                'field_type' => 'tour',
                'local_field' => 'code1',
                'api_field' => 'maincode',
                'data_type' => 'string',
                'transformation_rules' => []
            ],
            [
                'field_type' => 'tour',
                'local_field' => 'name',
                'api_field' => 'title',
                'data_type' => 'string',
                'transformation_rules' => []
            ],
            [
                'field_type' => 'tour',
                'local_field' => 'country_id',
                'api_field' => 'Country',
                'data_type' => 'string',
                'transformation_rules' => [
                    [
                        'type' => 'model_lookup',
                        'model' => 'CountryModel',
                        'lookup_field' => 'country_name_th',
                        'lookup_type' => 'like',
                        'return_field' => 'id',
                        'json_encode' => true,
                        'description' => 'Lookup country by Thai name and return as JSON array'
                    ]
                ]
            ],
            [
                'field_type' => 'tour',
                'local_field' => 'airline_id',
                'api_field' => 'aey',
                'data_type' => 'string',
                'transformation_rules' => [
                    [
                        'type' => 'extract_code',
                        'pattern' => '/\((.*?)\)/',
                        'description' => 'Extract airline code from format "Name (CODE)" then lookup in TravelTypeModel'
                    ]
                ]
            ],
            [
                'field_type' => 'tour',
                'local_field' => 'image',
                'api_field' => 'banner',
                'data_type' => 'string',
                'transformation_rules' => [
                    [
                        'type' => 'download_image',
                        'resize' => '600x600',
                        'path' => 'upload/tour/superbholidayapi/',
                        'description' => 'Download and resize image to 600x600'
                    ]
                ]
            ],
            [
                'field_type' => 'tour',
                'local_field' => 'pdf_file',
                'api_field' => 'pdf',
                'data_type' => 'string',
                'transformation_rules' => [
                    [
                        'type' => 'download_pdf',
                        'path' => 'upload/tour/pdf_file/superbholidayapi/',
                        'description' => 'Download PDF file with version check'
                    ]
                ]
            ],
            [
                'field_type' => 'tour',
                'local_field' => 'data_type',
                'api_field' => '',
                'data_type' => 'integer',
                'transformation_rules' => [
                    [
                        'type' => 'static_value',
                        'value' => 2,
                        'description' => 'Static value: 2 (API data type)'
                    ]
                ]
            ],
            [
                'field_type' => 'tour',
                'local_field' => 'api_type',
                'api_field' => '',
                'data_type' => 'string',
                'transformation_rules' => [
                    [
                        'type' => 'static_value',
                        'value' => 'superbholiday',
                        'description' => 'Static value: superbholiday'
                    ]
                ]
            ],
            [
                'field_type' => 'tour',
                'local_field' => 'group_id',
                'api_field' => '',
                'data_type' => 'integer',
                'transformation_rules' => [
                    [
                        'type' => 'static_value',
                        'value' => 3,
                        'description' => 'Static value: 3'
                    ]
                ]
            ],
            [
                'field_type' => 'tour',
                'local_field' => 'wholesale_id',
                'api_field' => '',
                'data_type' => 'integer',
                'transformation_rules' => [
                    [
                        'type' => 'static_value',
                        'value' => 22,
                        'description' => 'Static value: 22 (Super Holiday wholesale ID)'
                    ]
                ]
            ]
        ];
        
        // Period field mappings from hardcode
        $periodMappings = [
            [
                'field_type' => 'period',
                'local_field' => 'period_code',
                'api_field' => 'pid',
                'data_type' => 'string',
                'transformation_rules' => []
            ],
            [
                'field_type' => 'period',
                'local_field' => 'start_date',
                'api_field' => 'Date',
                'data_type' => 'date',
                'transformation_rules' => []
            ],
            [
                'field_type' => 'period',
                'local_field' => 'end_date',
                'api_field' => 'ENDDate',
                'data_type' => 'date',
                'transformation_rules' => []
            ],
            [
                'field_type' => 'period',
                'local_field' => 'group_date',
                'api_field' => 'Date',
                'data_type' => 'string',
                'transformation_rules' => [
                    [
                        'type' => 'date_format',
                        'format' => 'mY',
                        'description' => 'Convert date to mY format (e.g., 0125 for Jan 2025)'
                    ]
                ]
            ],
            [
                'field_type' => 'period',
                'local_field' => 'day',
                'api_field' => 'day',
                'data_type' => 'integer',
                'transformation_rules' => []
            ],
            [
                'field_type' => 'period',
                'local_field' => 'night',
                'api_field' => 'night',
                'data_type' => 'integer',
                'transformation_rules' => []
            ],
            [
                'field_type' => 'period',
                'local_field' => 'group',
                'api_field' => 'Size',
                'data_type' => 'integer',
                'transformation_rules' => []
            ],
            [
                'field_type' => 'period',
                'local_field' => 'count',
                'api_field' => 'AVBL',
                'data_type' => 'integer',
                'transformation_rules' => []
            ],
            [
                'field_type' => 'period',
                'local_field' => 'price1',
                'api_field' => 'Adult',
                'data_type' => 'decimal',
                'transformation_rules' => []
            ],
            [
                'field_type' => 'period',
                'local_field' => 'price2',
                'api_field' => 'Single',
                'data_type' => 'decimal',
                'transformation_rules' => []
            ],
            [
                'field_type' => 'period',
                'local_field' => 'price3',
                'api_field' => 'Chd+B',
                'data_type' => 'decimal',
                'transformation_rules' => []
            ],
            [
                'field_type' => 'period',
                'local_field' => 'price4',
                'api_field' => 'ChdNB',
                'data_type' => 'decimal',
                'transformation_rules' => []
            ],
            [
                'field_type' => 'period',
                'local_field' => 'status_period',
                'api_field' => 'AVBL',
                'data_type' => 'integer',
                'transformation_rules' => [
                    [
                        'type' => 'conditional',
                        'condition' => 'value > 0',
                        'true_value' => 1,
                        'false_value' => 3,
                        'description' => 'Set to 1 if AVBL > 0, otherwise 3'
                    ]
                ]
            ],
            [
                'field_type' => 'period',
                'local_field' => 'status_display',
                'api_field' => '',
                'data_type' => 'string',
                'transformation_rules' => [
                    [
                        'type' => 'static_value',
                        'value' => 'on',
                        'description' => 'Static value: on'
                    ]
                ]
            ],
            [
                'field_type' => 'period',
                'local_field' => 'api_type',
                'api_field' => '',
                'data_type' => 'string',
                'transformation_rules' => [
                    [
                        'type' => 'static_value',
                        'value' => 'superbholiday',
                        'description' => 'Static value: superbholiday'
                    ]
                ]
            ]
        ];
        
        $allMappings = array_merge($tourMappings, $periodMappings);
        
        foreach ($allMappings as $mapping) {
            ApiFieldMappingModel::create([
                'api_provider_id' => $superholiday->id,
                'field_type' => $mapping['field_type'],
                'local_field' => $mapping['local_field'],
                'api_field' => $mapping['api_field'],
                'data_type' => $mapping['data_type'],
                'transformation_rules' => $mapping['transformation_rules']
            ]);
        }
        
        echo "✅ Created " . count($allMappings) . " field mappings for Super Holiday API\n";
        echo "   - Tour fields: " . count($tourMappings) . "\n";
        echo "   - Period fields: " . count($periodMappings) . "\n";
    }

    public function down()
    {
        $superholiday = ApiProviderModel::where('code', 'superbholiday')->first();
        
        if ($superholiday) {
            ApiFieldMappingModel::where('api_provider_id', $superholiday->id)->delete();
            echo "🔄 Removed all Super Holiday API field mappings\n";
        }
    }
};