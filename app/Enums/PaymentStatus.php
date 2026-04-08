<?php
namespace App\Enums;

enum PaymentStatus: int
{
    case UNPAID           = 0;
    case PAID             = 1;
    case PARTIALLY_PAID   = 2;

    public function label(): string
    {
        return match($this) {
            self::UNPAID         => __('Unpaid'),
            self::PAID           => __('Paid'),
            self::PARTIALLY_PAID => __('Partially paid'),
        };
    }

    // PowerGrid looks for this to render option labels
    public function labelPowergridFilter(): string
    {
        return $this->label();
    }
}
