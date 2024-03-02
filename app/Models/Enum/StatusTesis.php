<?php

namespace App\Models\Enum;

use App\Models\Abstract\BaseEnum;

final class StatusTesis extends BaseEnum
{
    const PENDING = 'pending';
    const APPROVED = 'approved';
    const UPLOADING = 'uploading';
    const UPLOADED = 'uploaded';
    const FINISHED = 'finished';
    const REJECTED = 'rejected';
    const TAKEDOWN = 'takedown';
    const CANCELED = 'canceled';
}
