<?php

namespace App\Filament\Pages;

use App\Services\BackupService;
use App\Services\ReportExportService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * System management page for backups and reports.
 */
class SystemManagement extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-8-tooth';

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'System Management';

    protected static ?string $title = 'System Management';

    protected static ?int $navigationSort = 20;

    protected string $view = 'filament.pages.system-management';

    public ?array $reportData = [];

    public function mount(): void
    {
        $this->reportData = [
            'start_date' => now()->subMonth()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createBackup')
                ->label('Create Backup')
                ->icon('heroicon-o-archive-box-arrow-down')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Create System Backup')
                ->modalDescription('This will create a full backup of your database. This may take a few minutes.')
                ->action(function () {
                    $backupService = new BackupService;
                    $result = $backupService->createDatabaseBackup();

                    if ($result['success']) {
                        Notification::make()
                            ->title('Backup Created')
                            ->body("Backup saved: {$result['filename']}")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Backup Failed')
                            ->body($result['error'])
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('exportReport')
                ->label('Export Report')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->form([
                    Section::make('Report Period')
                        ->schema([
                            DatePicker::make('start_date')
                                ->label('Start Date')
                                ->required()
                                ->default(now()->subMonth()),
                            DatePicker::make('end_date')
                                ->label('End Date')
                                ->required()
                                ->default(now()),
                        ])
                        ->columns(2),
                ])
                ->action(function (array $data) {
                    $reportService = new ReportExportService;
                    $startDate = \Carbon\Carbon::parse($data['start_date']);
                    $endDate = \Carbon\Carbon::parse($data['end_date']);

                    $report = $reportService->getFullReport($startDate, $endDate);
                    $csv = $reportService->exportToCsv($report, 'report.csv');

                    $filename = 'report_'.now()->format('Y-m-d_H-i-s').'.csv';
                    Storage::put("reports/{$filename}", $csv);

                    Notification::make()
                        ->title('Report Generated')
                        ->body("Report saved: {$filename}")
                        ->success()
                        ->send();
                }),
        ];
    }

    public function reportForm(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Generate Report')
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->default(now()->subMonth()),
                        DatePicker::make('end_date')
                            ->label('End Date')
                            ->default(now()),
                    ])
                    ->columns(2),
            ])
            ->statePath('reportData');
    }

    public function getBackups(): array
    {
        $backupService = new BackupService;

        return $backupService->listBackups();
    }

    public function deleteBackup(string $path): void
    {
        $backupService = new BackupService;

        if ($backupService->deleteBackup($path)) {
            Notification::make()
                ->title('Backup Deleted')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Delete Failed')
                ->danger()
                ->send();
        }
    }

    public function downloadBackup(string $path): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return Storage::download($path);
    }

    protected function getForms(): array
    {
        return [
            'reportForm',
        ];
    }
}
