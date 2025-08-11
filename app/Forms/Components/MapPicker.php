<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;
use Filament\Forms\Get;
use Filament\Forms\Set;

class MapPicker extends Field
{
    protected string $view = 'forms.components.map-picker';

    protected float $defaultLatitude = -6.175110;
    protected float $defaultLongitude = 106.865036;
    protected int $zoom = 10;
    protected string $height = '400px';

    public function defaultLatitude(float $latitude): static
    {
        $this->defaultLatitude = $latitude;
        return $this;
    }

    public function defaultLongitude(float $longitude): static
    {
        $this->defaultLongitude = $longitude;
        return $this;
    }

    public function zoom(int $zoom): static
    {
        $this->zoom = $zoom;
        return $this;
    }

    public function height(string $height): static
    {
        $this->height = $height;
        return $this;
    }

    public function getDefaultLatitude(): float
    {
        return $this->defaultLatitude;
    }

    public function getDefaultLongitude(): float
    {
        return $this->defaultLongitude;
    }

    public function getZoom(): int
    {
        return $this->zoom;
    }

    public function getHeight(): string
    {
        return $this->height;
    }

    public function getState(): mixed
    {
        return null; // This component doesn't store its own state
    }

    // The current values will be handled in the Blade view
}
