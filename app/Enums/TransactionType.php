<?php

namespace App\Enums;

enum TransactionType: string
{
    case CREDIT = 'credit';//зачисление
    case DEBIT = 'debit'; //списание
    case TRANSFER = 'transfer'; //перевод

    const ALL = [
      self::CREDIT->value,
      self::DEBIT->value,
      self::TRANSFER->value,
    ];
}
