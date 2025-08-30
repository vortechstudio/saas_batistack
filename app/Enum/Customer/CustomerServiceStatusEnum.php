<?php

namespace App\Enum\Customer;

use App\Enum\EnumTrait;

enum CustomerServiceStatusEnum: string
{
    use EnumTrait;

    case EXPIRED = 'expired';
    case OK = 'ok';
    case PENDING = 'pending';
    case UNPAID = 'unpaid';
    case ERROR = 'error';

    public function label(): string
    {
        return match ($this) {
            self::EXPIRED => 'Expiré',
            self::OK => 'Actif',
            self::PENDING => 'En attente',
            self::UNPAID => 'Non payé',
            self::ERROR => 'Erreur',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::EXPIRED => 'danger',
            self::OK => 'success',
            self::PENDING => 'warning',
            self::UNPAID => 'danger',
            self::ERROR => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::EXPIRED => 'heroicon-o-x-circle',
            self::OK => 'heroicon-o-check-circle',
            self::PENDING => 'heroicon-o-arrow-path',
            self::UNPAID => 'heroicon-o-exclamation-circle',
            self::ERROR => 'heroicon-o-x-circle',
        };
    }

    public function badge()
    {
        return "<div class='badge badge-".$this->color()."'>".$this->label()."</div>";
    }

    public function badgeWithIcon(): string
    {
        $iconHtml = '';
        if (function_exists('svg')) {
            $svgObject = svg($this->icon());
            $iconHtml = method_exists($svgObject, 'toHtml') ? $svgObject->toHtml() : (string) $svgObject;
        }

        return sprintf(
            '<div class="badge badge-%s">%s%s</div>',
            $this->color(),
            $iconHtml,
            $this->label()
        );
    }
}
