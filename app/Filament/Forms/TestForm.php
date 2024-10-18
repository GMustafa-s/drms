<?php

namespace App\Filament\Forms;

use Filament\Forms\Form;
use Filament\Forms;
use TomatoPHP\FilamentHelpers\Contracts\FormBuilder;

class TestForm extends FormBuilder
{
    public function form(Form $form): Form
    {
        return $form->schema([
            //
        ]);
    }
}
