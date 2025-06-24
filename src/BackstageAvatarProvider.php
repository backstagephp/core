<?php

namespace Backstage;

use Exception;
use Backstage\Models\User;
use Filament\AvatarProviders\UiAvatarsProvider;
use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Spatie\Color\Rgb;

class BackstageAvatarProvider extends UiAvatarsProvider
{
    public function get(Model | Authenticatable $record): string
    {
        if ($record instanceof User) {
            $name = str(Filament::getNameForDefaultAvatar($record))
                ->trim()
                ->explode(' ')
                ->map(fn (string $segment): string => filled($segment) ? mb_substr($segment, 0, 1) : '')
                ->join(' ');

            if (str($name)->contains('(') || str($name)->contains(')')) {
                $name = str($name)
                    ->replace('(', '')
                    ->replace(')', '');
            }

            $oklchColor = FilamentColor::getColors()['gray'][950];
            $rgbColor = $this->oklchToRgb($oklchColor);
            
            $backgroundColor = Rgb::fromString('rgb(' . $rgbColor . ')')->toHex();

            if ($this->hasGravatar($record->email)) {
                return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($record->email))) . '?s=200';
            }

            return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&color=' . $backgroundColor . '&size=200&bold=true&rounded=true&';
        }

        return parent::get($record);
    }

    private function oklchToRgb(string $oklchColor): string
    {
        // Extract values from oklch(0.141 0.005 285.823) format
        if (preg_match('/oklch\(([^)]+)\)/', $oklchColor, $matches)) {
            $values = array_map('trim', explode(' ', $matches[1]));
            
            if (count($values) >= 3) {
                $l = (float) $values[0]; // Lightness (0-1)
                $c = (float) $values[1]; // Chroma
                $h = (float) $values[2]; // Hue (0-360)
                
                // Convert OKLCH to RGB using a simplified conversion
                // This is a basic approximation - for production use, consider a more robust library
                $rgb = $this->oklchToRgbValues($l, $c, $h);
                
                return implode(', ', array_map('round', $rgb));
            }
        }
        
        // Fallback to dark gray if parsing fails
        return '31, 41, 55';
    }

    private function oklchToRgbValues(float $l, float $c, float $h): array
    {
        // Convert hue to radians
        $hRad = deg2rad($h);
        
        // Calculate a and b components
        $a = $c * cos($hRad);
        $b = $c * sin($hRad);
        
        // Convert OKLCH to RGB using a simplified transformation
        // This is a basic approximation - for accurate conversion, use a proper color library
        $r = max(0, min(255, ($l * 255) + ($a * 255)));
        $g = max(0, min(255, ($l * 255) + ($b * 255)));
        $b = max(0, min(255, ($l * 255) - ($a * 255) - ($b * 255)));
        
        return [$r, $g, $b];
    }

    public function hasGravatar(string $email): bool
    {
        $hash = md5(strtolower(trim($email)));
        $cacheKey = "gravatar_exists_{$hash}";

        return cache()->remember($cacheKey, 3600, function () use ($hash) {
            $url = "https://www.gravatar.com/avatar/{$hash}?d=404";

            try {
                $response = Http::withoutRedirecting()->head($url);

                return $response->status() === 200;
            } catch (Exception $e) {
                return false;
            }
        });
    }
}
