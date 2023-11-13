<?php

namespace Afsakar\FilamentTranslateAction\Actions;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Actions\Action;
use Afsakar\FilamentTranslateAction\Helpers\GoogleTranslate;

class TranslatableAction
{
    public static function make(): void
    {
        Field::macro('translatable', function () {
            return $this->hintAction(
                function (Field $component) {
                    return Action::make('google_translate')
                        ->icon('heroicon-o-language')
                        ->label(__('filament-translate-action::filament-translate-action.modal_title'))
                        ->form([
                            Select::make('source')
                                ->label(__('filament-translate-action::filament-translate-action.source'))
                                ->options(fn() => getLangs())
                                ->searchable()
                                ->default((string)config('app.locale')),
                            Select::make('target')
                                ->label(__('filament-translate-action::filament-translate-action.target'))
                                ->options(fn() => getLangs())
                                ->searchable()
                                ->default((string)config('app.locale')),
                        ])
                        ->modalSubmitActionLabel(__('filament-translate-action::filament-translate-action.translate'))
                        ->action(function (array $data) use ($component) {
                            $googleTranslate = new GoogleTranslate();

                            $source = $data['source'] ?: (string) config('app.locale');

                            $googleTranslate = $googleTranslate->translate($source, $data['target'], $component->getState());

                            try {
                                $component->state($googleTranslate);
                                Notification::make()
                                    ->title(__('filament-translate-action::filament-translate-action.success_title'))
                                    ->body(__('filament-translate-action::filament-translate-action.success_message'))
                                    ->success()
                                    ->send();
                            } catch (\Exception $exception) {
                                Notification::make()
                                    ->title(__('filament-translate-action::filament-translate-action.error_title'))
                                    ->body(__('filament-translate-action::filament-translate-action.error_message'))
                                    ->danger()
                                    ->send();
                                return $component->getState();
                            }
                        });
                }
            );
        });
    }
}
