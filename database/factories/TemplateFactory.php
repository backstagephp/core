<?php

namespace Backstage\Database\Factories;

use Backstage\Models\Site;
use Backstage\Models\Template;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TemplateFactory extends Factory
{
    protected $model = Template::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Template $template) {
            $template->sites()->attach(Site::default());
        });
    }
}
