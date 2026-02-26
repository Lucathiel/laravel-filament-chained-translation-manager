<?php

namespace Statikbe\FilamentTranslationManager\Http\Livewire;

use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Statikbe\LaravelChainedTranslator\ChainedTranslationManager;

class TranslationEditForm extends Component
{
    public const EVENT_TRANSLATIONS_SAVED = 'translationsSaved';

    public string $group = '';
    public string $translationKey = '';
    public string $originalTranslationKey = '';

    public array $translations = [];
    public array $initialTranslations = [];
    public array $locales = [];

    public function mount(?string $group = null,?string $key = null,?string $translationKey = null,?string $translation_key = null): void 
    {
        $this->group = $group ?? request()->query('group') ?? '';
    
        $resolvedKey =
            $translationKey
            ?? $translation_key
            ?? $key
            ?? request()->query('translationKey')
            ?? request()->query('translation_key')
            ?? request()->query('key')
            ?? '';
    
        if ($resolvedKey === '') {
            throw new \RuntimeException('Translation key is missing (empty). Check Livewire mount params mapping.');
        }
    
        $this->translationKey = $resolvedKey;
        $this->originalTranslationKey = $resolvedKey;
    }

    public function save(string $locale): void
    {
        $chainedTranslationManager = app(ChainedTranslationManager::class);

        if (($this->translations[$locale] ?? null) === ($this->initialTranslations[$locale] ?? null)) {
            return;
        }

        $chainedTranslationManager->save(
            $locale,
            $this->group,
            $this->translationKey,
            $this->translations[$locale]
        );

        $this->dispatch(self::EVENT_TRANSLATIONS_SAVED, $this->group, $this->translationKey, $this->translations, $this->initialTranslations);

        $this->initialTranslations = $this->translations;

        Notification::make()
            ->success()
            ->title(trans('filament-translation-manager::messages.saved_translation'))
            ->send();
    }

    public function cancel(): void
    {
        $this->translations = $this->initialTranslations;
    }

    public function render(): View
    {
        return view('filament-translation-manager::livewire.translation-edit-form');
    }
}
