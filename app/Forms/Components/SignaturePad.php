<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class SignaturePad extends Field
{
    protected string $view = 'forms.components.signature-pad';

    // Propriété pour stocker la hauteur
    protected string $height = '200px';

    /**
     * Méthode pour définir la hauteur depuis le formulaire
     * Ex: ->height('300px')
     */
    public function height(string $height): static
    {
        $this->height = $height;
        return $this;
    }

    /**
     * Méthode pour récupérer la hauteur dans la vue Blade
     */
    public function getHeight(): string
    {
        return $this->height;
    }
}