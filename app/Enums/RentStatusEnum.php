<?php

namespace App\Enums;

enum RentStatusEnum : string {
    case REJECTED = 'rejected';
    case APPROVED = 'approved';
    case CANCELLED = 'cancelled';
    case PENDING = 'pending';
}