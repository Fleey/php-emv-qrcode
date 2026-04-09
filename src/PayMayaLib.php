<?php

namespace Vocolboy\PromptpayGenerator;

class PayMayaLib
{
    public static function generate(
        string $payerPhone,
        string $payeeName,
        $amount = null,
        $memo = null
    ): string {
        $data = [
            EMV::calculateString('00', '01'),
            EMV::calculateString('01', $amount ? '12' : '11'),
            EMV::calculateString(
                '27',
                EMV::serialize([
                    EMV::calculateString('00', 'com.p2pqrpay'),
                    EMV::calculateString('01', 'PAPHPHM1XXX'),
                    EMV::calculateString('02', '99964403'),
                    EMV::calculateString('04', self::formatPhone($payerPhone)),
                    EMV::calculateString('05', self::formatPhone($payerPhone, true)),
                ])
            ),
            EMV::calculateString('52', '6016'),
            EMV::calculateString('53', '608'),
            EMV::when(
                $amount,
                function () use ($amount) {
                    return EMV::calculateString('54', number_format($amount, 2, '.', ''));
                }
            ),
            EMV::calculateString('58', 'PH'),
            EMV::calculateString('59', $payeeName),
            EMV::calculateString('60', 'Valenzuela'),
            EMV::when(
                $memo,
                function () use ($memo) {
                    return EMV::calculateString('62', EMV::serialize([EMV::calculateString('08', $memo)]));
                }
            ),
        ];

        $data[] = EMV::calculateString(63, EMV::crc16($data));

        return EMV::serialize($data);
    }

    public static function formatPhone(string $payerPhone, $regular = false): string
    {
        $payerPhone = preg_replace('/[^0-9]/', '', $payerPhone);

        if (substr($payerPhone, 0, 1) === '0') {
            $prefix = ($regular) ? '+63' : '63';
            $payerPhone = $prefix . substr($payerPhone, 1);
        }

        if ($regular) {
            $pattern = '/(\d{2})(\d{3})(\d{6})/';
            $replacement = '$1-$2-$3';
            return preg_replace($pattern, $replacement, $payerPhone);
        } else {
            return $payerPhone;
        }
    }
}
