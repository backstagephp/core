<?php

namespace Vormkracht10\Backstage\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Vormkracht10\Backstage\Models\Site;
use Vormkracht10\Backstage\Models\Template;

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
