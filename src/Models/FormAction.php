<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Vormkracht10\Backstage\Mail\FormActionExecute;
use Vormkracht10\Backstage\Shared\HasPackageFactory;

class FormAction extends Model
{
    use HasPackageFactory;
    use HasUlids;

    protected $primaryKey = 'ulid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'config' => 'json'
        ];
    }

    public function form()
    {
        return $this->belongsTo(Form::class, 'form_slug', 'slug');
    }

    public function getConfigAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * Replace the config field with values from the submission.
     */
    public function configValue($field)
    {
        if (!preg_match('/\{\{([^\}]+))\}\}/', $field, $matches)) {
            return $field;
        }

        return $this->form?->value($matches[1]) ?? '';
    }

    /**
     * Executes the action.
     */
    public function execute(FormSubmission $submission) {
        switch ($this->type) {
            case 'email':
                Mail::to($submission->value($this->config['to_email'] ?? null) ?? $this->config['to_email'])
                    ->send(new FormActionExecute($this, $submission));
            break;
        }
    }
}
