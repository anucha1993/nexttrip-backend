<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ApiScheduleModel extends Model
{
    use HasFactory;

    protected $table = 'tb_api_schedules';

    protected $fillable = [
        'api_provider_id',
        'name',
        'frequency',
        'run_time',
        'interval_minutes',
        'days_of_week',
        'day_of_month',
        'cron_expression',
        'sync_limit',
        'is_active',
        'last_run_at',
        'next_run_at',
        'last_status',
        'last_error',
        'options'
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'options' => 'array',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function apiProvider()
    {
        return $this->belongsTo(ApiProviderModel::class, 'api_provider_id');
    }

    public function syncLogs()
    {
        return $this->hasMany(ApiSyncLogModel::class, 'api_provider_id', 'api_provider_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeReadyToRun($query)
    {
        return $query->active()
            ->where(function($q) {
                $q->whereNull('next_run_at')
                  ->orWhere('next_run_at', '<=', now());
            });
    }

    // Methods
    public function calculateNextRunTime()
    {
        $now = Carbon::now();
        $nextRun = null;

        switch ($this->frequency) {
            case 'hourly':
                $interval = $this->interval_minutes ?: 60;
                $nextRun = $now->addMinutes($interval);
                break;

            case 'daily':
                $nextRun = Carbon::tomorrow()->setTimeFromTimeString($this->run_time ?: '00:00:00');
                // ถ้าเวลาที่กำหนดยังไม่ถึงวันนี้ ให้รันวันนี้
                $todayAtTime = Carbon::today()->setTimeFromTimeString($this->run_time ?: '00:00:00');
                if ($todayAtTime->gt($now)) {
                    $nextRun = $todayAtTime;
                }
                break;

            case 'weekly':
                $daysOfWeek = $this->days_of_week ?: [1]; // Default Monday
                $runTime = $this->run_time ?: '00:00:00';
                
                $nextRun = null;
                foreach ($daysOfWeek as $dayOfWeek) {
                    $candidate = Carbon::now()->next($dayOfWeek)->setTimeFromTimeString($runTime);
                    
                    // ตรวจสอบว่าวันนี้เป็นวันที่กำหนดหรือไม่
                    $todayCandidate = Carbon::today()->setTimeFromTimeString($runTime);
                    if ($now->dayOfWeek == $dayOfWeek && $todayCandidate->gt($now)) {
                        $candidate = $todayCandidate;
                    }
                    
                    if (!$nextRun || $candidate->lt($nextRun)) {
                        $nextRun = $candidate;
                    }
                }
                break;

            case 'monthly':
                $dayOfMonth = $this->day_of_month ?: 1;
                $runTime = $this->run_time ?: '00:00:00';
                
                $nextRun = Carbon::now()->day($dayOfMonth)->setTimeFromTimeString($runTime);
                
                // ถ้าวันที่กำหนดในเดือนนี้ผ่านไปแล้ว ให้ไปเดือนหน้า
                if ($nextRun->lt($now)) {
                    $nextRun = $nextRun->addMonth();
                }
                break;

            case 'custom':
                if ($this->cron_expression) {
                    $nextRun = $now->addHour(); // Fallback
                }
                break;

            default:
                $nextRun = $now->addHour(); // Default fallback
        }

        return $nextRun;
    }

    public function updateNextRunTime()
    {
        $this->next_run_at = $this->calculateNextRunTime();
        $this->save();
        return $this->next_run_at;
    }

    public function markAsRunning()
    {
        $this->update([
            'last_status' => 'running',
            'last_run_at' => now(),
            'last_error' => null
        ]);
    }

    public function markAsSuccess()
    {
        $this->update([
            'last_status' => 'success',
            'last_error' => null
        ]);
        $this->updateNextRunTime();
    }

    public function markAsFailed($error)
    {
        $this->update([
            'last_status' => 'failed',
            'last_error' => $error
        ]);
        $this->updateNextRunTime();
    }

    // Helper methods
    public function getFrequencyTextAttribute()
    {
        $texts = [
            'hourly' => 'ทุกชั่วโมง',
            'daily' => 'ทุกวัน',
            'weekly' => 'ทุกสัปดาห์',
            'monthly' => 'ทุกเดือน',
            'custom' => 'กำหนดเอง'
        ];

        return $texts[$this->frequency] ?? $this->frequency;
    }

    public function getScheduleDescriptionAttribute()
    {
        switch ($this->frequency) {
            case 'hourly':
                return "ทุก {$this->interval_minutes} นาที";
                
            case 'daily':
                return "ทุกวันเวลา {$this->run_time}";
                
            case 'weekly':
                $days = collect($this->days_of_week)->map(function($day) {
                    $dayNames = ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'];
                    return $dayNames[$day] ?? $day;
                })->join(', ');
                return "ทุก {$days} เวลา {$this->run_time}";
                
            case 'monthly':
                return "ทุกวันที่ {$this->day_of_month} ของเดือน เวลา {$this->run_time}";
                
            case 'custom':
                return "Cron: {$this->cron_expression}";
                
            default:
                return $this->frequency;
        }
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'success' => '<span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">สำเร็จ</span>',
            'failed' => '<span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">ล้มเหลว</span>',
            'running' => '<span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">กำลังรัน</span>',
        ];

        return $badges[$this->last_status] ?? '<span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">ยังไม่รัน</span>';
    }
}