<?php
namespace app\models\enums;

class TransfertoError extends BaseEnum
{
    const NOERROR = 0;
    const POSTPAID = 204;

    public static $titles = [
        self::NOERROR => 'Transaction successful',
        self::POSTPAID => 'Destination Account is not prepaid or not valid',
    ];
}
