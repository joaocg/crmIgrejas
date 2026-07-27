<?php

namespace Tests\Feature\I18n;

use Tests\TestCase;

class TranslationCatalogTest extends TestCase
{
    public function test_core_translation_files_exist(): void
    {
        foreach (['pt_BR', 'en'] as $locale) {
            foreach (['auth', 'navigation', 'dashboard', 'forms'] as $domain) {
                $this->assertFileExists(lang_path("{$locale}/{$domain}.php"));
            }
        }

        $this->assertFileExists(resource_path('js/i18n/locales/pt-BR.json'));
        $this->assertFileExists(resource_path('js/i18n/locales/en.json'));
    }

    public function test_pt_br_translations_are_available(): void
    {
        app()->setLocale('pt_BR');

        $this->assertSame('Entrar', __('auth.login'));
        $this->assertSame('Painel', __('navigation.dashboard'));
        $this->assertSame('Português (Brasil)', __('forms.locale.pt_br'));
        $this->assertSame('This string is only defined in English.', __('auth.only_in_english'));
    }
}
