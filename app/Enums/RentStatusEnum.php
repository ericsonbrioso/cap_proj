<?php

namespace App\Enums;

enum RentStatusEnum : string {
    case COMPLETED = 'completed';
    case PROCESSING = 'processing';
    case CANCELLED = 'cancelled';
    case PENDING = 'pending';
}