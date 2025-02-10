<?php

namespace Backstage\Models;

use Backstage\Mail\FormActionExecute;
use Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

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
            'config' => 'json',
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
     * Executes the action.
     */
    public function execute(FormSubmission $submission)
    {
        switch ($this->get('type')) {
            case 'email':
                Mail::to($submission->value($this->config['to_email'] ?? null) ?? $this->config['to_email'])
                    ->send(new FormActionExecute($this, $submission));

                break;
        }
    }
}
