<?php

namespace Vocolboy\PromptpayGenerator;

class PromptPay
{
    public static function generate(string $promptpayId, $amount = 0): string
    {
        $promptpayIdLength = strlen($promptpayId);
        if ($promptpayIdLength >= 15) {
            $promptpayIdCode = '03';
        } elseif ($promptpayIdLength >= 13) {
            $promptpayIdCode = '02';
        } else {
            $promptpayIdCode = '01';
        }

        $data = [
            EMV::calculateString('00', '01'),
            EMV::calculateString('01', $amount ? '12' : '11'),
            EMV::calculateString(
                '29',
                EMV::serialize([
                    EMV::calculateString('00', 'A000000677010111'),
                    EMV::calculateString($promptpayIdCode, self::formatPromptpayId($promptpayId)),
                ])
            ),
            EMV::calculateString("58", "TH"),
            EMV::calculateString("53", "764"),
            EMV::when(
                $amount,
                function () use ($amount) {
                    return EMV::calculateString("54", number_format($amount, 2, '.', ''));
                }
            ),
        ];

        $data[] = EMV::calculateString(63, EMV::crc16($data));

        return EMV::serialize($data);
    }

    public static function formatPromptpayId(string $promptpayId): string
    {
        $promptpayId = preg_replace('/[^0-9]/', '', $promptpayId);
        $zeroPadPromptpayId = str_pad(preg_replace('/^0/', '66', $promptpayId), 13, '0', STR_PAD_LEFT);

        return strlen($promptpayId) >= 13 ? $promptpayId : $zeroPadPromptpayId;
    }
}
