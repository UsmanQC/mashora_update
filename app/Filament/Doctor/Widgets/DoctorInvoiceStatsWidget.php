<?php

declare(strict_types=1);

namespace App\Filament\Doctor\Widgets;

use App\Filament\Doctor\Concerns\BelongsToDoctorPanel;
use App\Models\Invoice;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

final class DoctorInvoiceStatsWidget extends StatsOverviewWidget
{
    use BelongsToDoctorPanel;

    protected static ?int $sort = -20;

    /** @var int | array<string, ?int> | null */
    protected int|array|null $columns = [
        'default' => 2,
        '@lg' => 4,
    ];

    protected function getHeading(): ?string
    {
        return __('Invoices');
    }

    protected function getDescription(): ?string
    {
        return __('Counts by issue date (or created date if issue date is empty). Your share is shown below each total.');
    }

    protected function getStats(): array
    {
        $doctorId = (int) Filament::auth()->id();
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->copy()->endOfWeek();

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->copy()->endOfMonth();

        $todayAgg = $this->invoiceAggregate($doctorId, $today, $today);
        $yesterdayAgg = $this->invoiceAggregate($doctorId, $yesterday, $yesterday);
        $weekAgg = $this->invoiceAggregate($doctorId, $weekStart, $weekEnd);
        $monthAgg = $this->invoiceAggregate($doctorId, $monthStart, $monthEnd);

        return [
            Stat::make(__('Today'), (string) $todayAgg['count'])
                ->description($this->money((float) $todayAgg['doctor_share']))
                ->color('primary')
                ->icon(Heroicon::OutlinedDocumentText),
            Stat::make(__('Yesterday'), (string) $yesterdayAgg['count'])
                ->description($this->money((float) $yesterdayAgg['doctor_share']))
                ->color('gray')
                ->icon(Heroicon::OutlinedDocumentText),
            Stat::make(__('This week'), (string) $weekAgg['count'])
                ->description($this->money((float) $weekAgg['doctor_share']))
                ->color('success')
                ->icon(Heroicon::OutlinedDocumentText),
            Stat::make(__('This month'), (string) $monthAgg['count'])
                ->description($this->money((float) $monthAgg['doctor_share']))
                ->color('info')
                ->icon(Heroicon::OutlinedDocumentText),
        ];
    }

    /**
     * @return array{count: int, doctor_share: float|int|string|null}
     */
    private function invoiceAggregate(int $doctorId, Carbon $periodStart, Carbon $periodEnd): array
    {
        $startDate = $periodStart->toDateString();
        $endDate = $periodEnd->toDateString();
        $startDt = $periodStart->copy()->startOfDay();
        $endDt = $periodEnd->copy()->endOfDay();

        $base = Invoice::query()
            ->where('doctor_id', $doctorId)
            ->where(function (Builder $q) use ($startDate, $endDate, $startDt, $endDt): void {
                $q->whereBetween('issue_date', [$startDate, $endDate])
                    ->orWhere(function (Builder $q2) use ($startDt, $endDt): void {
                        $q2->whereNull('issue_date')
                            ->whereBetween('created_at', [$startDt, $endDt]);
                    });
            });

        return [
            'count' => $base->clone()->count(),
            'doctor_share' => $base->clone()->sum('doctor_share'),
        ];
    }

    private function money(float $amount): string
    {
        return Number::currency($amount, 'SAR');
    }
}
