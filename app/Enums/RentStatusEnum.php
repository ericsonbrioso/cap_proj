<?php

namespace App\Enums;

enum RentStatusEnum : string {
    case COMPLETED = 'completed';
    case APPROVED = 'approved';
    case CANCELLED = 'cancelled';
    case PENDING = 'pending';
}