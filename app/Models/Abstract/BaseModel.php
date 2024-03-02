<?php

namespace App\Models\Abstract;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Watson\Validating\ValidatingTrait;

abstract class BaseModel extends Model
{
    use ValidatingTrait;

    public const CREATED_AT = 'waktu_dibuat';
    public const UPDATED_AT =  'waktu_diubah';
    protected $keyType = 'string';

    public $incrementing = false;

    protected bool $throwValidationExceptions = true;

    protected $dateFormat = 'Y-m-d\TH:i:s.uP';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->assignPrimaryKey();
    }

    protected function assignPrimaryKey(): void
    {
        $key = $this->getKeyName();
        if ($this->$key === null) {
            $this->$key = Str::ulid()->toRfc4122();
        }
    }

    abstract public function getRules(): array;
}
