<?php

namespace App\Filament\Pages;

use App\Services\SonarrRadarrService;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

/**
 * Admin settings page for Sonarr/Radarr integration.
 */
class IntegrationSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string | \UnitEnum | null $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Integrations';

    protected static ?string $title = 'Integration Settings';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.integration-settings';

    public ?array $sonarrData = [];

    public ?array $radarrData = [];

    public bool $sonarrConfigured = false;

    public bool $radarrConfigured = false;

    public function mount(): void
    {
        $service = new SonarrRadarrService;
        $this->sonarrConfigured = $service->isSonarrConfigured();
        $this->radarrConfigured = $service->isRadarrConfigured();

        $this->sonarrData = [
            'url' => config('services.sonarr.url', ''),
            'api_key' => config('services.sonarr.api_key', ''),
        ];

        $this->radarrData = [
            'url' => config('services.radarr.url', ''),
            'api_key' => config('services.radarr.api_key', ''),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importSonarr')
                ->label('Import from Sonarr')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Import TV Series from Sonarr')
                ->modalDescription('This will import all TV series from Sonarr. Existing series will be skipped.')
                ->action(function () {
                    $service = new SonarrRadarrService;
                    $result = $service->importSonarrSeries();

                    if ($result['imported'] > 0 || $result['skipped'] > 0) {
                        Notification::make()
                            ->title('Sonarr Import Complete')
                            ->body("Imported: {$result['imported']}, Skipped: {$result['skipped']}")
                            ->success()
                            ->send();
                    }

                    if (! empty($result['errors'])) {
                        Notification::make()
                            ->title('Import Errors')
                            ->body(implode("\n", array_slice($result['errors'], 0, 5)))
                            ->warning()
                            ->send();
                    }
                }),
            Action::make('importRadarr')
                ->label('Import from Radarr')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Import Movies from Radarr')
                ->modalDescription('This will import all movies from Radarr. Existing movies will be skipped.')
                ->action(function () {
                    $service = new SonarrRadarrService;
                    $result = $service->importRadarrMovies();

                    if ($result['imported'] > 0 || $result['skipped'] > 0) {
                        Notification::make()
                            ->title('Radarr Import Complete')
                            ->body("Imported: {$result['imported']}, Skipped: {$result['skipped']}")
                            ->success()
                            ->send();
                    }

                    if (! empty($result['errors'])) {
                        Notification::make()
                            ->title('Import Errors')
                            ->body(implode("\n", array_slice($result['errors'], 0, 5)))
                            ->warning()
                            ->send();
                    }
                }),
        ];
    }

    public function sonarrForm(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Sonarr Configuration')
                    ->description('Configure Sonarr API connection for TV series import')
                    ->schema([
                        TextInput::make('url')
                            ->label('Sonarr URL')
                            ->placeholder('http://localhost:8989')
                            ->helperText('The base URL of your Sonarr instance'),
                        TextInput::make('api_key')
                            ->label('API Key')
                            ->password()
                            ->revealable()
                            ->placeholder('Your Sonarr API key')
                            ->helperText('Found in Sonarr → Settings → General → Security'),
                        Placeholder::make('env_note')
                            ->content(new HtmlString(
                                '<div class="text-sm text-gray-500 dark:text-gray-400 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">'.
                                '<strong>Note:</strong> These settings are read from environment variables. '.
                                'Set <code>SONARR_URL</code> and <code>SONARR_API_KEY</code> in your <code>.env</code> file.'.
                                '</div>'
                            )),
                    ])
                    ->columns(2),
            ])
            ->statePath('sonarrData');
    }

    public function radarrForm(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Radarr Configuration')
                    ->description('Configure Radarr API connection for movie import')
                    ->schema([
                        TextInput::make('url')
                            ->label('Radarr URL')
                            ->placeholder('http://localhost:7878')
                            ->helperText('The base URL of your Radarr instance'),
                        TextInput::make('api_key')
                            ->label('API Key')
                            ->password()
                            ->revealable()
                            ->placeholder('Your Radarr API key')
                            ->helperText('Found in Radarr → Settings → General → Security'),
                        Placeholder::make('env_note')
                            ->content(new HtmlString(
                                '<div class="text-sm text-gray-500 dark:text-gray-400 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">'.
                                '<strong>Note:</strong> These settings are read from environment variables. '.
                                'Set <code>RADARR_URL</code> and <code>RADARR_API_KEY</code> in your <code>.env</code> file.'.
                                '</div>'
                            )),
                    ])
                    ->columns(2),
            ])
            ->statePath('radarrData');
    }

    public function testSonarrConnection(): void
    {
        $service = new SonarrRadarrService;
        $result = $service->testSonarrConnection();

        if ($result['success']) {
            Notification::make()
                ->title('Sonarr Connection Successful')
                ->body("Connected to Sonarr v{$result['version']}")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Sonarr Connection Failed')
                ->body($result['message'])
                ->danger()
                ->send();
        }
    }

    public function testRadarrConnection(): void
    {
        $service = new SonarrRadarrService;
        $result = $service->testRadarrConnection();

        if ($result['success']) {
            Notification::make()
                ->title('Radarr Connection Successful')
                ->body("Connected to Radarr v{$result['version']}")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Radarr Connection Failed')
                ->body($result['message'])
                ->danger()
                ->send();
        }
    }

    public function clearCache(): void
    {
        $service = new SonarrRadarrService;
        $service->clearCache();

        Notification::make()
            ->title('Cache Cleared')
            ->body('Sonarr and Radarr cache has been cleared.')
            ->success()
            ->send();
    }

    protected function getForms(): array
    {
        return [
            'sonarrForm',
            'radarrForm',
        ];
    }
}
