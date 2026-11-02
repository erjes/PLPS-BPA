<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Tracking row for an asynchronous Excel export.
 */
class ExportHistory extends Model
{
    public const STATUS_QUEUED = 'queued';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'admin_id',
        'filename',
        'path',
        'disk',
        'format',
        'status',
        'total_rows',
        'processed_rows',
        'file_size',
        'filters',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'admin_id' => 'integer',
        'total_rows' => 'integer',
        'processed_rows' => 'integer',
        'file_size' => 'integer',
        'filters' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id_admin');
    }

    /**
     * Exports an admin may see: everything he created himself plus the
     * exports triggered from his current browser session (guest visitors
     * can use the dashboard/export too).
     *
     * @param  Builder  $query
     * @param  int|null  $adminId
     * @param  array  $sessionIds
     * @return Builder
     */
    public function scopeVisibleTo(Builder $query, ?int $adminId, array $sessionIds = []): Builder
    {
        if (!$adminId) {
            return $query->whereIn('id', $sessionIds ?: [0]);
        }

        return $query->where(function (Builder $query) use ($adminId, $sessionIds) {
            $query->where('admin_id', $adminId);

            if (!empty($sessionIds)) {
                $query->orWhereIn('id', $sessionIds);
            }
        });
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isPending(): bool
    {
        return in_array($this->status, [self::STATUS_QUEUED, self::STATUS_PROCESSING], true);
    }

    public function markAsProcessing(int $processedRows = 0): void
    {
        $this->forceFill([
            'status' => self::STATUS_PROCESSING,
            'processed_rows' => max($this->processed_rows, $processedRows),
            'started_at' => $this->started_at ?: now(),
        ])->save();
    }

    public function markAsCompleted(int $totalRows = 0, ?int $fileSize = null): void
    {
        $this->forceFill([
            'status' => self::STATUS_COMPLETED,
            'total_rows' => $totalRows,
            'processed_rows' => $totalRows,
            'file_size' => $fileSize ?? $this->file_size,
            'error_message' => null,
            'started_at' => $this->started_at ?: now(),
            'completed_at' => now(),
        ])->save();
    }

    public function markAsFailed(?string $message = null): void
    {
        $this->forceFill([
            'status' => self::STATUS_FAILED,
            'error_message' => mb_substr((string) $message, 0, 1000),
            'completed_at' => now(),
        ])->save();
    }

    public function progressPercent(): int
    {
        if ($this->isCompleted()) {
            return 100;
        }

        if ($this->total_rows <= 0) {
            return $this->status === self::STATUS_PROCESSING ? 10 : 0;
        }

        return (int) min(99, floor($this->processed_rows / $this->total_rows * 100));
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_QUEUED => 'Menunggu antrean',
            self::STATUS_PROCESSING => 'Sedang diproses',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_FAILED => 'Gagal',
            default => ucfirst((string) $this->status),
        };
    }

    /**
     * Badge colours, aligned with the dashboard palette.
     */
    public function statusStyle(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'background:#dcfce7;color:#15803d',
            self::STATUS_FAILED => 'background:#fee2e2;color:#b91c1c',
            self::STATUS_PROCESSING => 'background:#fef3c7;color:#b45309',
            default => 'background:#e2e8f0;color:#475569',
        };
    }

    public function humanFileSize(): string
    {
        $bytes = (int) $this->file_size;

        if ($bytes <= 0) {
            return '-';
        }

        foreach (['B', 'KB', 'MB', 'GB'] as $unit) {
            if ($bytes < 1024) {
                return round($bytes, 1) . ' ' . $unit;
            }

            $bytes /= 1024;
        }

        return round($bytes, 1) . ' TB';
    }

    public function fileExists(): bool
    {
        return $this->isCompleted()
            && $this->path
            && Storage::disk($this->disk)->exists($this->path);
    }
}
