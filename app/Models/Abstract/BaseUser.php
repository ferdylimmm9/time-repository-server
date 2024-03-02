<?php

namespace App\Models\Abstract;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Str;
use Watson\Validating\ValidatingTrait;

abstract class BaseUser extends User
{
    use ValidatingTrait;

    public const CREATED_AT =  'waktu_dibuat';
    public const UPDATED_AT = 'waktu_diubah';

    protected bool $throwValidationExceptions = true;

    protected $dateFormat = 'Y-m-d\TH:i:s.uP';

    public $incrementing = false;

    protected $keyType = 'string';

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
