<?php

namespace JobMetric\Extension\Enums;

use JobMetric\PackageCore\Enums\EnumMacros;

/**
 * @method static MODULE()
 * @method static SHIPPINGMETHOD()
 * @method static PAYMENTMETHOD()
 * @method static ONLINEPAYMENTMETHOD()
 * @method static ORDERTOTAL()
 * @method static RETURNORDERTOTAL()
 * @method static CAPTCHA()
 * @method static REPORT()
 * @method static THEME()
 * @method static ATTENDANCEDEVICE()
 * @method static SYNCINGSOFTWARE()
 */
enum ExtensionTypeEnum: string
{
    use EnumMacros;

    case MODULE = "Module";
    case SHIPPINGMETHOD = "ShippingMethod";
    case PAYMENTMETHOD = "PaymentMethod";
    case ONLINEPAYMENTMETHOD = "OnlinePaymentMethod";
    case ORDERTOTAL = "OrderTotal";
    case RETURNORDERTOTAL = "ReturnOrderTotal";
    case CAPTCHA = "Captcha";
    case REPORT = "Report";
    case THEME = "Theme";
    case ATTENDANCEDEVICE = "AttendanceDevice";
    case SYNCINGSOFTWARE = "SyncingSoftware";
}
