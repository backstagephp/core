# Creating a field

You can create a field by extending the `FieldBase` class and implementing the `FieldInterface` interface.

In this example we will create a color field based on the `ColorPicker` component from Filament.

Each field in Filament has some default configuration options. You can override these options by creating a `getDefaultConfig` method in your field class. The `getDefaultConfig` method should return an array with the default configuration options and the extra configuration options for your field. In this example we will add a `color` and `regex` configuration option.

```php

namespace App\Backstage\Fields;

use Filament\Forms;
use Vormkracht10\Backstage\Models\Field;
use Vormkracht10\Backstage\Fields\FieldBase;
use Vormkracht10\Backstage\Fields\FieldInterface;
use Filament\Forms\Components\ColorPicker as Input;

class Color extends FieldBase implements FieldInterface
{
    public static function getDefaultConfig(): array
    {
        return [
            ...parent::getDefaultConfig(),
            'color' => ColorFormat::HEX->value,
            'regex' => null,
        ];
    }

    public static function make(string $name, Field $field): Input
    {
        // Notice that we are using the `applyDefaultSettings` method to apply the default settings to the input.
        $input = self::applyDefaultSettings(Input::make($name), $field);

        // We are setting the label and regex configuration options.
        $input = $input->label($field->name ?? self::getDefaultConfig()['label'] ?? null)
            ->regex($field->config['regex'] ?? self::getDefaultConfig()['regex'])
            ->color($field->config['color'] ?? self::getDefaultConfig()['color']);

        return $input;
    }

    public function getForm(): array
    {
        return [
            Forms\Components\Tabs::make()
                ->schema([
                    Forms\Components\Tabs\Tab::make('General')
                        ->label(__('General'))
                        ->schema([
                            ...parent::getForm(),
                        ]),
                    Forms\Components\Tabs\Tab::make('Field specific')
                        ->label(__('Field specific'))
                        ->schema([
                            // All the field specific settings are added here.
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Select::make('config.color')
                                    ->label(__('Color format'))
                                    ->options(ColorFormat::array()),
                                Forms\Components\TextInput::make('config.regex')
                                    ->label(__('Regex'))
                                    ->placeholder(__('Enter a regex pattern')),
                            ]),
                        ]),
                ])->columnSpanFull(),
        ];
    }
}
```

# Mutate form data before filling the form

You can mutate the form data before filling the form by implementing the `mutateFormDataCallback` method in your field class.

Make sure when you are mutating the form data that you are returning the `$data` array.

```php
 public static function mutateFormDataCallback(Model $record, Field $field, array $data): array
    {
        // Mutate the form data here.

        return $data;
    }
```

# Mutate the record before saving

You can mutate the record before saving by implementing the `mutateBeforeSaveCallback` method in your field class.

Make sure when you are mutating the record that you are returning the `$data` array.

```php
 public static function mutateBeforeSaveCallback(Model $record, Field $field, array $data): array
    {
        // Mutate the record before saving here.

        return $data;
    }
```
